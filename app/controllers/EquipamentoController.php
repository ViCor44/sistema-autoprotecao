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
        $resultado = $db->query("SELECT id, nome FROM tipos_equipamentos WHERE ativo = TRUE ORDER BY nome ASC");
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

        $porPagina = 24;
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
     * Imprimir etiquetas dos equipamentos
     * A4 vertical: 4 colunas x 6 linhas por página
     * Extintores: 2 etiquetas por equipamento
     */
    public function etiquetas() {
        $filtros = ['ativo' => 1];

        $tipo = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;
        $estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';
        $localizacao = isset($_GET['localizacao']) ? trim((string)$_GET['localizacao']) : '';

        if ($tipo > 0) {
            $filtros['tipo_equipamento_id'] = $tipo;
        }

        if ($estado !== '') {
            $filtros['estado'] = $estado;
        }

        if ($localizacao !== '') {
            $filtros['localizacao'] = $localizacao;
        }

        $ordenacao = [
            'campo' => 'tipo_nome',
            'direcao' => 'ASC',
        ];

        $equipamentos = $this->equipamento->getAll($filtros, null, 0, $ordenacao);
        $etiquetas = [];

        foreach ($equipamentos as $equipamento) {
            $tipoNome = strtolower((string)($equipamento['tipo_nome'] ?? ''));
            $quantidadeEtiquetas = str_contains($tipoNome, 'extintor') ? 2 : 1;

            for ($i = 0; $i < $quantidadeEtiquetas; $i++) {
                $etiquetas[] = $equipamento;
            }
        }

        $etiquetasPorPagina = 24; // 4 colunas x 6 linhas
        $paginas = array_chunk($etiquetas, $etiquetasPorPagina);

        $this->render('equipamentos/etiquetas', compact('paginas', 'etiquetas'));
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
     * Salvar novo equipamento
     */
    public function salvar() {
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
            'observacoes' => $_POST['observacoes'] ?? ''
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
            'observacoes' => $_POST['observacoes'] ?? ''
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
