<?php
/**
 * Controller para Equipamentos
 */
class EquipamentoController extends Controller {
    private $equipamento;
    private $tipoEquipamento;
    private $tiposEquipamentos = [];
    private $camposDinamicosPorTipo = [];

    public function __construct() {
        $this->equipamento = new Equipamento();
        $this->tipoEquipamento = new TipoEquipamento();
        $this->carregarTiposEquipamentos();
    }

    private function renderStandalone($view, array $data = []) {
        $viewPath = APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception('View não encontrada: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    /**
     * Carregar tipos de equipamentos da base de dados
     */
    private function carregarTiposEquipamentos() {
        $db = new Database();
        $resultado = $db->query("SELECT id, nome, prefixo_numeracao FROM tipos_equipamentos WHERE ativo = TRUE ORDER BY nome ASC");
        $this->tiposEquipamentos = $resultado->fetch_all(MYSQLI_ASSOC);

        foreach ($this->tiposEquipamentos as $tipo) {
            $this->camposDinamicosPorTipo[(int)$tipo['id']] = $this->equipamento->getCamposDinamicosPorTipo((int)$tipo['id']);
        }
    }

    /**
     * Listar todos os equipamentos
     */
    public function listar() {
        $filtros = ['ativo' => 1];
        $autoAbrirEquipamentoId = null;

        $tipo = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;
        $estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';
        $localizacao = isset($_GET['localizacao']) ? trim((string)$_GET['localizacao']) : '';
        $ordenar = isset($_GET['ordenar']) ? trim((string)$_GET['ordenar']) : 'tipo_nome';
        $direcao = isset($_GET['direcao']) ? strtolower(trim((string)$_GET['direcao'])) : 'asc';

        $camposOrdenacaoPermitidos = ['tipo_nome', 'localizacao', 'estado', 'proxima_manutencao'];
        if (!in_array($ordenar, $camposOrdenacaoPermitidos, true)) {
            $ordenar = 'tipo_nome';
        }

        if (!in_array($direcao, ['asc', 'desc'], true)) {
            $direcao = 'asc';
        }

        if ($tipo > 0) {
            $filtros['tipo_equipamento_id'] = $tipo;
        }

        if ($estado !== '') {
            $filtros['estado'] = $estado;
        }

        $qrNumero = null;
        $qrLocalizacao = null;

        if ($localizacao !== '' && preg_match('/^NR=(.+);LOC=(.+)$/i', $localizacao, $matches)) {
            $qrNumero = trim((string)$matches[1]);
            $qrLocalizacao = trim((string)$matches[2]);
        }

        if ($qrNumero !== null && $qrLocalizacao !== null && $qrNumero !== '' && $qrLocalizacao !== '') {
            $filtros['qr_numero'] = $qrNumero;
            $filtros['qr_localizacao'] = $qrLocalizacao;
        } elseif ($localizacao !== '') {
            $filtros['localizacao'] = $localizacao;
        }

        $porPagina = 25;
        $paginaAtual = max(1, (int)($_GET['pagina'] ?? 1));

        $totalResultados = $this->equipamento->getTotal($filtros);
        $totalPaginas = max(1, (int)ceil($totalResultados / $porPagina));

        if ($paginaAtual > $totalPaginas) {
            $paginaAtual = $totalPaginas;
        }

        $offset = ($paginaAtual - 1) * $porPagina;
        $ordenacao = [
            'campo' => $ordenar,
            'direcao' => strtoupper($direcao),
        ];

        $equipamentos = $this->equipamento->getAll($filtros, $porPagina, $offset, $ordenacao);

        if ($qrNumero !== null && $qrLocalizacao !== null) {
            foreach ($equipamentos as $equip) {
                $numeroSerie = trim((string)($equip['numero_serie'] ?? ''));
                $localizacaoEquip = trim((string)($equip['localizacao'] ?? ''));

                if (strcasecmp($numeroSerie, $qrNumero) === 0 && strcasecmp($localizacaoEquip, $qrLocalizacao) === 0) {
                    $autoAbrirEquipamentoId = (int)$equip['id'];
                    break;
                }
            }
        }

        $resumo = $this->equipamento->getResumoEstados($filtros);
        $tipos = $this->tiposEquipamentos;

        $this->render('equipamentos/listar', compact(
            'equipamentos',
            'tipos',
            'filtros',
            'resumo',
            'paginaAtual',
            'porPagina',
            'totalPaginas',
            'totalResultados',
            'offset',
            'ordenar',
            'direcao',
            'autoAbrirEquipamentoId'
        ));
    }

    /**
     * Imprimir etiquetas dos equipamentos.
     *
     * Modos:
     *   ?id=X             → Seleção de posição na folha para etiqueta individual
     *   ?id=X&posicao=N   → Render da etiqueta individual na posição N
     *   ?tipo=T&…         → Batch: extintores→QR+simples; outros→só simples
     */
    public function etiquetas() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $posicao = isset($_GET['posicao']) && $_GET['posicao'] !== '' ? (int)$_GET['posicao'] : -1;

        if ($id > 0) {
            $equipamento = $this->equipamento->getById($id);
            if (!$equipamento) {
                $this->flash('Equipamento não encontrado.', 'erro');
                $this->redirect('equipamento', 'listar');
                return;
            }

            $isExtintor = stripos((string)($equipamento['tipo_nome'] ?? ''), 'extintor') !== false;
            // O seletor usa a grelha QR (24 células) para extintores, simples (48) para outros.
            // A posição escolhida é válida nas duas folhas (pos < 24 ≤ 48).
            $totalCelulas = $isExtintor ? 24 : 48;

            if ($posicao < 0) {
                $this->renderStandalone('equipamentos/etiquetas', [
                    'modoEscolhaPosicao' => true,
                    'equipamento'        => $equipamento,
                    'isExtintor'         => $isExtintor,
                    'totalCelulas'       => $totalCelulas,
                ]);
                return;
            }

            $posicao = max(0, min($posicao, $totalCelulas - 1));

            $this->renderStandalone('equipamentos/etiquetas', [
                'modoUnico'    => true,
                'etiqueta'     => $equipamento,
                'isExtintor'   => $isExtintor,
                'posicao'      => $posicao,
                'totalCelulas' => $totalCelulas,
            ]);
            return;
        }

        // Modo batch: filtros
        $filtros = ['ativo' => 1, 'is_reserva' => 0];

        $tipo        = isset($_GET['tipo'])         ? (int)$_GET['tipo']              : 0;
        $estado      = isset($_GET['estado'])       ? trim((string)$_GET['estado'])   : '';
        $localizacao = isset($_GET['localizacao'])  ? trim((string)$_GET['localizacao']) : '';

        if ($tipo > 0) {
            $filtros['tipo_equipamento_id'] = $tipo;
        }
        if ($estado !== '') {
            $filtros['estado'] = $estado;
        }
        if ($localizacao !== '') {
            $filtros['localizacao'] = $localizacao;
        }

        $ordenacao = ['campo' => 'tipo_nome', 'direcao' => 'ASC'];
        $equipamentos = $this->equipamento->getAll($filtros, null, 0, $ordenacao);

        // Extintores recebem os dois tipos de etiqueta; outros só a simples.
        $etiquetasQr     = array_values(array_filter($equipamentos, function ($e) {
            return stripos((string)($e['tipo_nome'] ?? ''), 'extintor') !== false;
        }));
        $etiquetasSimples = array_values($equipamentos); // todos

        $paginasQr      = !empty($etiquetasQr)      ? array_chunk($etiquetasQr,      24) : [];
        $paginasSimples = !empty($etiquetasSimples)  ? array_chunk($etiquetasSimples, 48) : [];
        $totalEtiquetas = count($equipamentos);

        $this->renderStandalone('equipamentos/etiquetas', compact(
            'etiquetasQr', 'etiquetasSimples',
            'paginasQr', 'paginasSimples',
            'totalEtiquetas'
        ));
    }

    /**
     * Lista imprimível de equipamentos por tipo
     */
    public function lista_imprimivel() {
        $tipoId = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;
        if ($tipoId <= 0) {
            $this->flash('Tipo de equipamento inválido.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        $tipo = $this->tipoEquipamento->getById($tipoId);
        if (!$tipo) {
            $this->flash('Tipo de equipamento não encontrado.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        $equipamentos = $this->equipamento->getAll(
            ['ativo' => 1, 'tipo_equipamento_id' => $tipoId],
            null,
            0,
            ['campo' => 'localizacao', 'direcao' => 'ASC']
        );

        $this->renderStandalone('equipamentos/lista_imprimivel', compact('tipo', 'equipamentos'));
    }

    /**
     * Exportar lista de equipamentos com os filtros actuais em PDF
     */
    public function exportar_pdf() {
        $filtros = ['ativo' => 1];

        $tipoId  = isset($_GET['tipo'])   ? (int)$_GET['tipo']              : 0;
        $estado  = isset($_GET['estado']) ? trim((string)$_GET['estado'])   : '';
        $loc     = isset($_GET['localizacao']) ? trim((string)$_GET['localizacao']) : '';
        $ordenar = isset($_GET['ordenar'])  ? trim((string)$_GET['ordenar'])  : 'localizacao';
        $direcao = isset($_GET['direcao'])  ? strtoupper(trim((string)$_GET['direcao'])) : 'ASC';
        $seletorCamposPresente = isset($_GET['campos_pdf_presentes']) && (string)$_GET['campos_pdf_presentes'] === '1';
        $camposPdfSelecionados = [];
        if (isset($_GET['campos_pdf']) && is_array($_GET['campos_pdf'])) {
            foreach ($_GET['campos_pdf'] as $slugCampo) {
                $slugCampo = preg_replace('/[^a-z0-9_\-]/i', '', (string)$slugCampo);
                if ($slugCampo !== '') {
                    $camposPdfSelecionados[$slugCampo] = true;
                }
            }
        }

        $camposOrdenacaoPermitidos = ['tipo_nome', 'localizacao', 'estado', 'proxima_manutencao'];
        if (!in_array($ordenar, $camposOrdenacaoPermitidos, true)) {
            $ordenar = 'localizacao';
        }
        if (!in_array($direcao, ['ASC', 'DESC'], true)) {
            $direcao = 'ASC';
        }

        $nomeTipo = 'Todos';
        if ($tipoId > 0) {
            $filtros['tipo_equipamento_id'] = $tipoId;
            $tipo = $this->tipoEquipamento->getById($tipoId);
            if ($tipo) {
                $nomeTipo = $tipo['nome'];
            }
        }
        if ($estado !== '') {
            $filtros['estado'] = $estado;
        }
        if ($loc !== '') {
            $filtros['localizacao'] = $loc;
        }

        $equipamentos = $this->equipamento->getAll(
            $filtros,
            null,
            0,
            ['campo' => 'numero_registo', 'direcao' => 'ASC']
        );

        // Buscar características dinâmicas dos equipamentos em bloco
        $caracteristicasPorEquip = [];
        $mostrarColunaCaracteristicas = true;
        if ($seletorCamposPresente && empty($camposPdfSelecionados)) {
            $mostrarColunaCaracteristicas = false;
        }

        if (!empty($equipamentos)) {
            $db = new Database();
            $idsSeguros = implode(',', array_map('intval', array_column($equipamentos, 'id')));
            $resultado = $db->query(
                "SELECT ecv.equipamento_id, tec.slug, tec.nome_campo, tec.unidade, ecv.valor
                 FROM equipamentos_campos_valores ecv
                 JOIN tipos_equipamentos_campos tec ON tec.id = ecv.campo_id
                 WHERE ecv.equipamento_id IN ({$idsSeguros})
                   AND tec.ativo = TRUE
                 ORDER BY tec.ordem ASC, tec.nome_campo ASC"
            );
            if ($resultado) {
                foreach ($resultado->fetch_all(MYSQLI_ASSOC) as $row) {
                    $slugCampo = trim((string)($row['slug'] ?? ''));
                    if ($seletorCamposPresente && !empty($camposPdfSelecionados)) {
                        if ($slugCampo === '' || !isset($camposPdfSelecionados[$slugCampo])) {
                            continue;
                        }
                    }

                    $equipamentoId = (int)$row['equipamento_id'];
                    $valor = trim((string)($row['valor'] ?? ''));
                    if ($valor === '') {
                        continue;
                    }

                    $unidade = trim((string)($row['unidade'] ?? ''));
                    $valorFormatado = $valor . ($unidade !== '' ? ' ' . $unidade : '');
                    $caracteristicasPorEquip[$equipamentoId][] = $valorFormatado;
                }
            }
        }

        require_once APP_PATH . '/libs/fpdf/fpdf.php';

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        $larguraNumero = 24;
        $larguraLocalizacao = $mostrarColunaCaracteristicas ? 48 : 76;
        $larguraCaracteristicas = 42;
        $larguraObservacoes = $mostrarColunaCaracteristicas ? 36 : 50;
        $larguraEstado = 20;
        $larguraProx = 20;

        // Cabeçalho
        $pdf->SetFillColor(242, 245, 249);
        $pdf->SetDrawColor(205, 212, 223);
        $pdf->Rect(0, 0, 210, 22, 'FD');
        $pdf->SetTextColor(26, 38, 56);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetXY(10, 5);
        $pdf->Cell(95, 8, $this->pdfTexto(APP_NAME), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(95, 8, $this->pdfTexto('Lista de Equipamentos' . ($nomeTipo !== 'Todos' ? ' - ' . $nomeTipo : '')), 0, 1, 'R');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, 14);
        $filtroDesc = 'Total: ' . count($equipamentos);
        if ($estado !== '') {
            $filtroDesc .= '   |   Estado: ' . $this->pdfTexto(ucfirst($estado));
        }
        if ($loc !== '') {
            $filtroDesc .= '   |   Localiz.: ' . $this->pdfTexto($loc);
        }
        $filtroDesc .= '   |   ' . date('d/m/Y H:i');
        $pdf->Cell(190, 5, $filtroDesc, 0, 1, 'L');

        // Cabeçalho da tabela
        $headers = [
            ['Nº Registo', $larguraNumero],
            ['Localização', $larguraLocalizacao],
        ];
        if ($mostrarColunaCaracteristicas) {
            $headers[] = ['Características', $larguraCaracteristicas];
        }
        $headers[] = ['Observações', $larguraObservacoes];
        $headers[] = ['Estado', $larguraEstado];
        $headers[] = ['Próx. Vist.', $larguraProx];

        $pdf->SetFillColor(226, 232, 240);
        $pdf->SetDrawColor(180, 190, 205);
        $pdf->SetTextColor(26, 38, 56);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(10, 26);
        foreach ($headers as [$texto, $larg]) {
            $pdf->Cell($larg, 8, $this->pdfTexto($texto), 1, 0, 'L', true);
        }
        $pdf->Ln();

        // Linhas
        $pdf->SetFont('Arial', '', 8.5);
        $alt = false;
        foreach ($equipamentos as $eq) {
            $caracteristicas = $caracteristicasPorEquip[(int)$eq['id']] ?? [];
            $caracteristicasTexto = empty($caracteristicas) ? '-' : implode(' | ', $caracteristicas);
            $observacoes = trim((string)($eq['observacoes'] ?? ''));

            $celulas = [
                [$larguraNumero, $this->pdfTexto($eq['numero_registo'] ?? '-')],
                [$larguraLocalizacao, $this->pdfTexto($eq['localizacao'] ?? '-')],
            ];
            if ($mostrarColunaCaracteristicas) {
                $celulas[] = [$larguraCaracteristicas, $this->pdfTexto($caracteristicasTexto)];
            }
            $celulas[] = [$larguraObservacoes, $this->pdfTexto($observacoes === '' ? '-' : $observacoes)];
            $celulas[] = [$larguraEstado, $this->pdfTexto(ucfirst((string)($eq['estado'] ?? '-')))];
            $celulas[] = [$larguraProx, $this->pdfTexto($this->formatarDataPdf($eq['data_proxima_manutencao'] ?? null))];

            $linhasPorCelula = [];
            $maxLinhas = 1;
            foreach ($celulas as [$largura, $texto]) {
                $linhas = $pdf->SplitTextToWidth($largura, $texto);
                $linhasPorCelula[] = $linhas;
                $maxLinhas = max($maxLinhas, count($linhas));
            }
            $alturaLinha = max(7, 2 + ($maxLinhas * 4));

            if ($pdf->GetY() + $alturaLinha > 287) {
                $pdf->AddPage();
                $pdf->SetFillColor(226, 232, 240);
                $pdf->SetDrawColor(180, 190, 205);
                $pdf->SetTextColor(26, 38, 56);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY(10, 10);
                foreach ($headers as [$texto, $larg]) {
                    $pdf->Cell($larg, 8, $this->pdfTexto($texto), 1, 0, 'L', true);
                }
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 8.5);
            }

            $fill = $alt;
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
            $inicioX = 10;
            $inicioY = $pdf->GetY();
            $x = $inicioX;
            foreach ($celulas as $indice => [$largura, $texto]) {
                $pdf->Rect($x, $inicioY, $largura, $alturaLinha, 'FD');
                foreach ($linhasPorCelula[$indice] as $numeroLinha => $linha) {
                    $pdf->SetXY($x, $inicioY + 1 + ($numeroLinha * 4));
                    $pdf->Cell($largura, 4, $linha, 0, 0, 'L');
                }
                $x += $largura;
            }
            $pdf->SetXY($inicioX, $inicioY + $alturaLinha);
            $alt = !$alt;
        }

        $pdf->Output('I', 'equipamentos_' . date('Ymd_Hi') . '.pdf');
        exit;
    }

    /**
     * Exportar lista de equipamentos por tipo em PDF
     */
    public function lista_pdf() {
        $tipoId = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;
        if ($tipoId <= 0) {
            $this->flash('Tipo de equipamento inválido.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        $tipo = $this->tipoEquipamento->getById($tipoId);
        if (!$tipo) {
            $this->flash('Tipo de equipamento não encontrado.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        $equipamentos = $this->equipamento->getAll(
            ['ativo' => 1, 'tipo_equipamento_id' => $tipoId],
            null,
            0,
            ['campo' => 'localizacao', 'direcao' => 'ASC']
        );

        require_once APP_PATH . '/libs/fpdf/fpdf.php';

        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();

        $pdf->SetFillColor(242, 245, 249);
        $pdf->SetDrawColor(205, 212, 223);
        $pdf->Rect(0, 0, 297, 20, 'FD');
        $pdf->SetTextColor(26, 38, 56);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY(10, 5);
        $pdf->Cell(140, 8, APP_NAME, 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(137, 8, 'Lista de Equipamentos por Tipo', 0, 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY(10, 13);
        $pdf->Cell(160, 5, 'Tipo: ' . ($tipo['nome'] ?? '-') . '   |   Total: ' . count($equipamentos) . '   |   Gerado em ' . date('d/m/Y H:i'), 0, 0, 'L');

        $y = 28;
        $headers = [
            ['Nº', 35],
            ['Localização', 78],
            ['Marca', 40],
            ['Modelo', 48],
            ['Estado', 36],
            ['Próxima Vistoria', 40],
        ];

        $pdf->SetFillColor(226, 232, 240);
        $pdf->SetDrawColor(180, 190, 205);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(10, $y);
        foreach ($headers as [$texto, $largura]) {
            $pdf->Cell($largura, 8, $texto, 1, 0, 'L', true);
        }
        $pdf->Ln();
        $y += 8;

        $pdf->SetFont('Arial', '', 8.5);
        $alt = false;
        foreach ($equipamentos as $equipamento) {
            if ($y > 195) {
                $pdf->AddPage();
                $y = 20;
                $pdf->SetFillColor(226, 232, 240);
                $pdf->SetDrawColor(180, 190, 205);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY(10, $y);
                foreach ($headers as [$texto, $largura]) {
                    $pdf->Cell($largura, 8, $texto, 1, 0, 'L', true);
                }
                $pdf->Ln();
                $y += 8;
                $pdf->SetFont('Arial', '', 8.5);
            }

            $fill = $alt;
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
            $pdf->SetXY(10, $y);
            $pdf->Cell(35, 7, $this->pdfTexto(($equipamento['numero_serie'] ?? '-')), 1, 0, 'L', true);
            $pdf->Cell(78, 7, $this->pdfTexto(($equipamento['localizacao'] ?? '-')), 1, 0, 'L', true);
            $pdf->Cell(40, 7, $this->pdfTexto(($equipamento['marca'] ?? '-')), 1, 0, 'L', true);
            $pdf->Cell(48, 7, $this->pdfTexto(($equipamento['modelo'] ?? '-')), 1, 0, 'L', true);
            $pdf->Cell(36, 7, $this->pdfTexto(ucfirst((string)($equipamento['estado'] ?? '-'))), 1, 0, 'L', true);
            $pdf->Cell(40, 7, $this->pdfTexto($this->formatarDataPdf($equipamento['data_proxima_manutencao'] ?? null)), 1, 1, 'L', true);
            $y += 7;
            $alt = !$alt;
        }

        $nomeFicheiro = 'equipamentos_tipo_' . $tipoId . '.pdf';
        $pdf->Output('I', $nomeFicheiro);
        exit;
    }

    private function formatarDataPdf($data) {
        $data = trim((string)$data);
        if ($data === '' || $data === '0000-00-00') {
            return '-';
        }

        $timestamp = strtotime($data);
        if ($timestamp === false) {
            return '-';
        }

        return date('d/m/Y', $timestamp);
    }

    private function pdfTexto($texto) {
        $texto = (string)$texto;
        if ($texto === '') {
            return '-';
        }

        return iconv('UTF-8', 'windows-1252//TRANSLIT', $texto) ?: $texto;
    }

    private function limitarTextoPdf($texto, $limite = 36) {
        $texto = trim((string)$texto);
        if ($texto === '') {
            return '-';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($texto, 'UTF-8') <= $limite) {
                return $texto;
            }
            return mb_substr($texto, 0, $limite - 3, 'UTF-8') . '...';
        }

        if (strlen($texto) <= $limite) {
            return $texto;
        }

        return substr($texto, 0, $limite - 3) . '...';
    }

    private function contarLinhasPdf($pdf, $largura, $texto) {
        $largura = (float)$largura;
        if ($largura <= 0) {
            return 1;
        }

        if (!isset($pdf->CurrentFont['cw'])) {
            return 1;
        }

        $cw = $pdf->CurrentFont['cw'];
        $wmax = ($largura - 2 * $pdf->cMargin) * 1000 / $pdf->FontSize;
        $s = str_replace("\r", '', (string)$texto);
        $nb = strlen($s);

        if ($nb > 0 && $s[$nb - 1] === "\n") {
            $nb--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }

            if ($c === ' ') {
                $sep = $i;
            }

            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                if ($sep === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }

    /**
     * Ver detalhes de um equipamento
     */
    public function ver($id) {
        $equipamento = $this->equipamento->getById($id);
        
        if (!$equipamento) {
            $this->flash('Equipamento não encontrado.', 'erro');
            $this->redirect('equipamento', 'listar');
        }

        $camposDinamicos = $this->equipamento->getCamposDinamicosPorTipo((int)$equipamento['tipo_equipamento_id']);
        $valoresCamposDinamicos = $this->equipamento->getValoresCamposDinamicos((int)$equipamento['id']);

        $this->render('equipamentos/ver', compact('equipamento', 'camposDinamicos', 'valoresCamposDinamicos'));
    }

    /**
     * Formulário para criar equipamento
     */
    public function criar() {
        $tipos = $this->tiposEquipamentos;
        $camposDinamicosPorTipo = $this->camposDinamicosPorTipo;
        $this->render('equipamentos/criar', compact('tipos', 'camposDinamicosPorTipo'));
    }

    /**
     * AJAX: devolver preview do próximo número de registo para um tipo
     */
    public function previewNumero() {
        header('Content-Type: application/json; charset=utf-8');

        $tipoId = (int)($_GET['tipo_id'] ?? 0);
        if ($tipoId <= 0) {
            echo json_encode(['numero' => '']);
            exit;
        }

        $db = new Database();
        $stmt = $db->prepare("SELECT prefixo_numeracao, proximo_numero FROM tipos_equipamentos WHERE id = ? AND ativo = TRUE");
        $stmt->bind_param("i", $tipoId);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if (!$resultado) {
            echo json_encode(['numero' => '']);
            exit;
        }

        $prefixo = !empty($resultado['prefixo_numeracao']) ? strtoupper(trim($resultado['prefixo_numeracao'])) : 'EQP';
        $numero = (int)($resultado['proximo_numero'] ?? 1);
        $preview = $prefixo . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);

        echo json_encode(['numero' => $preview]);
        exit;
    }

    /**
     * Salvar novo equipamento
     */
    public function salvar() {
        $this->requirePost('equipamento', 'listar');

        $isReserva = !empty($_POST['is_reserva']) ? 1 : 0;

        $intercalarPosicao = null;
        if (!$isReserva && !empty($_POST['usar_intercalacao']) && !empty($_POST['intercalar_posicao'])) {
            $intercalarPosicao = max(1, (int)$_POST['intercalar_posicao']);
        }

        $dados = [
            'tipo_equipamento_id' => $_POST['tipo_equipamento_id'] ?? 0,
            'is_reserva' => $isReserva,
            'numero_serie' => $_POST['numero_serie'] ?? '',
            'localizacao' => $_POST['localizacao'] ?? '',
            'marca' => $_POST['marca'] ?? '',
            'modelo' => $_POST['modelo'] ?? '',
            'data_aquisicao' => $_POST['data_aquisicao'] ?? null,
            'data_instalacao' => $_POST['data_instalacao'] ?? null,
            'data_proxima_manutencao' => $_POST['data_proxima_manutencao'] ?? null,
            'estado' => $_POST['estado'] ?? 'operacional',
            'observacoes' => $_POST['observacoes'] ?? '',
            'intercalar_posicao' => $intercalarPosicao,
        ];

        $equipamentoId = $this->equipamento->create($dados);

        if ($equipamentoId) {
            $camposDinamicos = $_POST['campos_dinamicos'] ?? [];
            $this->equipamento->salvarCamposDinamicos((int)$equipamentoId, $camposDinamicos);

            $this->flash('Equipamento criado com sucesso!', 'sucesso');
            $this->redirect('equipamento', 'listar');
        } else {
            $this->flash('Erro ao criar equipamento.', 'erro');
            $this->redirect('equipamento', 'criar');
        }
    }

    /**
     * Formulário para editar equipamento
     */
    public function editar($id) {
        $equipamento = $this->equipamento->getById($id);
        
        if (!$equipamento) {
            $this->flash('Equipamento não encontrado.', 'erro');
            $this->redirect('equipamento', 'listar');
        }

        $tipos = $this->tiposEquipamentos;
        $camposDinamicosPorTipo = $this->camposDinamicosPorTipo;
        $valoresCamposDinamicos = $this->equipamento->getValoresCamposDinamicos((int)$id);
        $this->render('equipamentos/editar', compact('equipamento', 'tipos', 'camposDinamicosPorTipo', 'valoresCamposDinamicos'));
    }

    /**
     * Atualizar equipamento
     */
    public function atualizar($id) {
        $this->requirePost('equipamento', 'listar');

        $dados = [
            'tipo_equipamento_id' => $_POST['tipo_equipamento_id'] ?? 0,
            'numero_serie' => $_POST['numero_serie'] ?? '',
            'localizacao' => $_POST['localizacao'] ?? '',
            'marca' => $_POST['marca'] ?? '',
            'modelo' => $_POST['modelo'] ?? '',
            'data_aquisicao' => $_POST['data_aquisicao'] ?? null,
            'data_instalacao' => $_POST['data_instalacao'] ?? null,
            'data_proxima_manutencao' => $_POST['data_proxima_manutencao'] ?? null,
            'estado' => $_POST['estado'] ?? 'operacional',
            'observacoes' => $_POST['observacoes'] ?? '',
            'atribuir_numero' => !empty($_POST['atribuir_numero']) ? 1 : 0,
        ];

        if ($this->equipamento->update($id, $dados)) {
            $camposDinamicos = $_POST['campos_dinamicos'] ?? [];
            $this->equipamento->salvarCamposDinamicos((int)$id, $camposDinamicos);

            $this->flash('Equipamento atualizado com sucesso!', 'sucesso');
            $this->redirect('equipamento', 'ver', ['id' => $id]);
        } else {
            $this->flash('Erro ao atualizar equipamento.', 'erro');
            $this->redirect('equipamento', 'editar', ['id' => $id]);
        }
    }

    /**
     * Deletar equipamento
     */
    public function deletar($id) {
        if ($this->equipamento->delete($id)) {
            $this->flash('Equipamento removido com sucesso!', 'sucesso');
        } else {
            $this->flash('Erro ao remover equipamento.', 'erro');
        }

        $this->redirect('equipamento', 'listar');
    }
}
