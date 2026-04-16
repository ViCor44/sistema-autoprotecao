<?php
/**
 * Controller para Relatórios
 */
class RelatorioController extends Controller {
    private $relatorio;
    private $equipamento;
    private $calendario;

    public function __construct() {
        $this->relatorio = new Relatorio();
        $this->equipamento = new Equipamento();
        $this->calendario = new CalendarioManutencao();
    }

    /**
     * Listar todos os relatórios
     */
    public function listar() {
        $filtros = [];
        
        if (isset($_GET['data_inicio'])) {
            $filtros['data_inicio'] = $_GET['data_inicio'];
        }
        if (isset($_GET['data_fim'])) {
            $filtros['data_fim'] = $_GET['data_fim'];
        }
        if (isset($_GET['tipo'])) {
            $filtros['tipo_relatorio'] = $_GET['tipo'];
        }
        
        $relatorios = $this->relatorio->getAll($filtros);

        $this->render('relatorios/listar', compact('relatorios'));
    }

    /**
     * Ver detalhes de um relatório
     */
    public function ver($id) {
        $relatorio = $this->relatorio->getById($id);
        
        if (!$relatorio) {
            $this->flash('Relatório não encontrado.', 'erro');
            $this->redirect('relatorio', 'listar');
        }

        $itens = $this->relatorio->getItensRelatorio($id);
        // Se proxima_inspecao não foi guardada, tentar obter do calendário
        if ((empty($relatorio['proxima_inspecao']) || $relatorio['proxima_inspecao'] === '0000-00-00') && !empty($relatorio['calendario_id'])) {
            $proxima = $this->calendario->getProximaAgendadaPorTipo(
                $relatorio['tipo_equipamento_id'],
                $relatorio['calendario_id']
            );
            if ($proxima) {
                $relatorio['proxima_inspecao'] = $proxima;
            }
        }
        $this->render('relatorios/ver', compact('relatorio', 'itens'));
    }

    /**
     * Formulário para criar relatório
     */
    public function criar($equipamento_id = null) {
        $equipamentos = $this->equipamento->getAll();
        $this->render('relatorios/criar', compact('equipamento_id', 'equipamentos'));
    }

    /**
     * Salvar novo relatório
     */
    public function salvar() {
        $this->requirePost('relatorio', 'listar');

        $dados = [
            'calendario_id' => $_POST['calendario_id'] ?? 0,
            'tipo_equipamento_id' => $_POST['tipo_equipamento_id'] ?? 0,
            'equipamento_id' => $_POST['equipamento_id'] ?? 0,
            'data_relatorio' => $_POST['data_relatorio'] ?? date('Y-m-d'),
            'responsavel_id' => $_SESSION['utilizador_id'] ?? 0,
            'tipo_relatorio' => $_POST['tipo_relatorio'] ?? 'inspecao',
            'descricao' => $_POST['descricao'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'condicoes_encontradas' => $_POST['condicoes_encontradas'] ?? '',
            'proxima_inspecao' => $_POST['proxima_inspecao'] ?? null
        ];

        $relatorio_id = $this->relatorio->create($dados);

        if ($relatorio_id) {
            // Adicionar itens do relatório se fornecidos
            if (isset($_POST['itens_descricao']) && is_array($_POST['itens_descricao'])) {
                foreach ($_POST['itens_descricao'] as $index => $descricao) {
                    $this->relatorio->adicionarItem(
                        $relatorio_id,
                        $descricao,
                        $_POST['itens_resultado'][$index] ?? 'n_aplicavel',
                        $_POST['itens_observacao'][$index] ?? ''
                    );
                }
            }

            $this->flash('Relatório criado com sucesso!', 'sucesso');
            $this->redirect('relatorio', 'ver', ['id' => $relatorio_id]);
        } else {
            $this->flash('Erro ao criar relatório.', 'erro');
            $this->redirect('relatorio', 'criar');
        }
    }

    /**
     * Formulário para editar relatório
     */
    public function editar($id) {
        $relatorio = $this->relatorio->getById($id);
        if (!$relatorio || $relatorio['assinado']) {
            $this->flash('Relatório não encontrado ou já assinado.', 'erro');
            $this->redirect('relatorio', 'ver', ['id' => $id]);
        }
        $equipamentos = $this->equipamento->getAll();
        $this->render('relatorios/editar', compact('relatorio', 'equipamentos'));
    }

    /**
     * Atualizar relatório
     */
    public function atualizar($id) {
        $this->requirePost('relatorio', 'ver', ['id' => $id]);
        $dados = [
            'descricao' => $_POST['descricao'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'condicoes_encontradas' => $_POST['condicoes_encontradas'] ?? '',
            'proxima_inspecao' => $_POST['proxima_inspecao'] ?? null
        ];
        $this->relatorio->atualizar($id, $dados);
        $this->flash('Relatório atualizado com sucesso!', 'sucesso');
        $this->redirect('relatorio', 'ver', ['id' => $id]);
    }

    /**
     * Assinar relatório
     */
    public function assinar($id) {
        if ($this->relatorio->assinar($id)) {
            $this->flash('Relatório assinado com sucesso!', 'sucesso');
        } else {
            $this->flash('Erro ao assinar relatório.', 'erro');
        }

        $this->redirect('relatorio', 'ver', ['id' => $id]);
    }

    /**
     * Obter relatórios pendentes
     */
    public function pendentes() {
        $relatorios = $this->relatorio->getRelatoriosPendentesAssinatura();
        $this->render('relatorios/pendentes', compact('relatorios'));
    }

    /**
     * Exportar relatório para PDF
     */
    public function pdf($id) {
        $this->exportar_pdf($id);
    }

    /**
     * Exportar relatório em PDF
     */
    public function exportar_pdf($id) {
        $relatorio = $this->relatorio->getById($id);
        if (!$relatorio) {
            $this->flash('Relatório não encontrado.', 'erro');
            $this->redirect('relatorio', 'listar');
        }
        $itens = $this->relatorio->getItensRelatorio($id);

        require_once APP_PATH . '/libs/fpdf/fpdf.php';
        $pdf = new FPDF('P', 'mm', 'A5');
        $pdf->AddPage();

        $numeroDocumento = 'REL-' . str_pad((string)$relatorio['id'], 4, '0', STR_PAD_LEFT);
        $tecnico         = $relatorio['responsavel_nome'] ?: '-';
        $dataDocumento   = date('d/m/Y', strtotime($relatorio['data_relatorio']));
        $ambito          = !empty($relatorio['localizacao']) ? $relatorio['localizacao'] : '-';
        $proximaInsp     = !empty($relatorio['proxima_inspecao']) ? date('d/m/Y', strtotime($relatorio['proxima_inspecao'])) : '-';

        // ── Cabeçalho ────────────────────────────────────────────────────────
        // Faixa azul-escura
        $pdf->SetFillColor(26, 58, 92);
        $pdf->SetDrawColor(26, 58, 92);
        $pdf->SetLineWidth(0.2);
        $pdf->Rect(0, 0, 148, 22, 'F');

        // Listra laranja de destaque
        $pdf->SetFillColor(220, 100, 20);
        $pdf->Rect(0, 22, 148, 1.5, 'F');

        // Texto do cabeçalho (branco)
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetXY(8, 5);
        $pdf->Cell(70, 8, APP_NAME, 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY(70, 5);
        $pdf->Cell(70, 8, 'Relatório de Inspeção', 0, 0, 'R');

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(8, 14);
        $pdf->Cell(132, 5, 'Documento nº ' . $numeroDocumento . '   |   Gerado em ' . date('d/m/Y H:i'), 0, 0, 'L');

        // ── Meta: Técnico / Data ──────────────────────────────────────────────
        $metaY = 28;

        // Box Técnico
        $pdf->SetFillColor(235, 239, 245);
        $pdf->SetDrawColor(180, 190, 205);
        $pdf->SetLineWidth(0.3);
        $pdf->Rect(8, $metaY, 88, 14, 'FD');
        $pdf->SetFillColor(43, 87, 151);
        $pdf->Rect(8, $metaY, 88, 5.5, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(9, $metaY + 0.5);
        $pdf->Cell(86, 5, 'TÉCNICO RESPONSÁVEL', 0, 0, 'L');
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(9, $metaY + 6.5);
        $pdf->Cell(86, 6, $tecnico, 0, 0, 'L');

        // Box Data
        $pdf->SetFillColor(235, 239, 245);
        $pdf->SetDrawColor(180, 190, 205);
        $pdf->Rect(100, $metaY, 40, 14, 'FD');
        $pdf->SetFillColor(43, 87, 151);
        $pdf->Rect(100, $metaY, 40, 5.5, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(101, $metaY + 0.5);
        $pdf->Cell(38, 5, 'DATA DO RELATÓRIO', 0, 0, 'L');
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(101, $metaY + 6.5);
        $pdf->Cell(38, 6, $dataDocumento, 0, 0, 'L');

        // ── Secção: Informação Geral ─────────────────────────────────────────
        $pdf->SetDrawColor(200, 205, 215);
        $pdf->SetLineWidth(0.2);
        $y = 48;

        $this->pdfSeccao($pdf, $y, 'INFORMAÇÃO GERAL');
        $y += 7;

        $campos = [
            ['Equipamento',    $relatorio['tipo_equipamento'] ?: '-'],
            ['Âmbito',         $ambito],
            ['Tipo de Relatório', ucfirst((string)($relatorio['tipo_relatorio'] ?: '-'))],
            ['Condições Encontradas', ucfirst((string)($relatorio['condicoes_encontradas'] ?: '-'))],
            ['Próxima Inspeção', $proximaInsp],
            ['Estado', $relatorio['assinado'] ? 'Assinado' : 'Pendente de assinatura'],
        ];

        $altRow = false;
        foreach ($campos as $campo) {
            $pdf->SetFillColor($altRow ? 245 : 255, $altRow ? 247 : 255, $altRow ? 250 : 255);
            $pdf->SetDrawColor(220, 225, 235);
            $pdf->Rect(8, $y, 132, 7, 'FD');
            $pdf->SetTextColor(90, 100, 120);
            $pdf->SetFont('Arial', 'B', 7.5);
            $pdf->SetXY(10, $y + 1.2);
            $pdf->Cell(40, 5, $campo[0] . ':', 0, 0, 'L');
            $pdf->SetTextColor(20, 20, 20);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY(52, $y + 1.2);
            $pdf->Cell(86, 5, $campo[1], 0, 0, 'L');
            $y += 7;
            $altRow = !$altRow;
        }

        // ── Secção: Descrição ────────────────────────────────────────────────
        if (!empty($relatorio['descricao'])) {
            $y += 3;
            $this->pdfSeccao($pdf, $y, 'DESCRIÇÃO');
            $y += 7;
            $pdf->SetFillColor(250, 251, 253);
            $pdf->SetDrawColor(210, 215, 225);
            $pdf->Rect(8, $y, 132, 0.3, 'F');
            $pdf->SetTextColor(30, 30, 30);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY(10, $y + 1);
            $pdf->MultiCell(128, 5, $relatorio['descricao']);
            $y = $pdf->GetY() + 2;
        }

        // ── Secção: Observações ───────────────────────────────────────────────
        if (!empty($relatorio['observacoes'])) {
            $this->pdfSeccao($pdf, $y, 'OBSERVAÇÕES');
            $y += 7;
            $pdf->SetTextColor(30, 30, 30);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY(10, $y + 1);
            $pdf->MultiCell(128, 5, $relatorio['observacoes']);
            $y = $pdf->GetY() + 2;
        }

        // ── Secção: Itens de Verificação ──────────────────────────────────────
        if (!empty($itens)) {
            $this->pdfSeccao($pdf, $y, 'ITENS DE VERIFICAÇÃO');
            $y += 7;

            // Cabeçalho da tabela
            $pdf->SetFillColor(210, 218, 232);
            $pdf->SetDrawColor(180, 190, 205);
            $pdf->SetLineWidth(0.2);
            $pdf->Rect(8, $y, 100, 6, 'FD');
            $pdf->Rect(108, $y, 32, 6, 'FD');
            $pdf->SetTextColor(30, 50, 80);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(10, $y + 1);
            $pdf->Cell(98, 5, 'Verificação', 0, 0, 'L');
            $pdf->SetXY(109, $y + 1);
            $pdf->Cell(30, 5, 'Resultado', 0, 0, 'L');
            $y += 6;

            $altRow = false;
            foreach ($itens as $item) {
                $rowH = empty($item['observacao']) ? 7 : 11;
                $pdf->SetFillColor($altRow ? 244 : 255, $altRow ? 247 : 255, $altRow ? 252 : 255);
                $pdf->SetDrawColor(220, 225, 235);
                $pdf->Rect(8, $y, 100, $rowH, 'FD');
                $pdf->Rect(108, $y, 32, $rowH, 'FD');

                $pdf->SetTextColor(20, 20, 20);
                $pdf->SetFont('Arial', '', 8.5);
                $pdf->SetXY(10, $y + 1.5);
                $pdf->Cell(96, 5, $item['descricao_verificacao'] ?: '-', 0, 0, 'L');
                if (!empty($item['observacao'])) {
                    $pdf->SetTextColor(90, 100, 115);
                    $pdf->SetFont('Arial', '', 7.5);
                    $pdf->SetXY(10, $y + 6);
                    $pdf->Cell(96, 4, 'Obs: ' . $item['observacao'], 0, 0, 'L');
                }

                $resultado = strtolower((string)($item['resultado'] ?: ''));
                if ($resultado === 'conforme') {
                    $pdf->SetTextColor(0, 130, 60);
                } elseif ($resultado === 'não conforme' || $resultado === 'nao conforme') {
                    $pdf->SetTextColor(180, 30, 30);
                } else {
                    $pdf->SetTextColor(100, 100, 100);
                }
                $pdf->SetFont('Arial', 'B', 8.5);
                $pdf->SetXY(109, $y + 1.5);
                $pdf->Cell(30, 5, ucfirst($item['resultado'] ?: '-'), 0, 0, 'L');

                $y += $rowH;
                $altRow = !$altRow;
            }
        }

        // ── Assinatura ────────────────────────────────────────────────────────
        $y += 8;
        $pdf->SetDrawColor(160, 170, 185);
        $pdf->SetLineWidth(0.4);
        $pdf->Line(8, $y, 80, $y);
        $pdf->Line(90, $y, 140, $y);
        $pdf->SetTextColor(120, 120, 130);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY(8, $y + 1.5);
        $pdf->Cell(72, 4, 'Assinatura do Responsável', 0, 0, 'C');
        $pdf->SetXY(90, $y + 1.5);
        $pdf->Cell(50, 4, 'Data', 0, 0, 'C');

        // ── Rodapé ────────────────────────────────────────────────────────────
        $footerY = 202;
        $pdf->SetFillColor(26, 58, 92);
        $pdf->Rect(0, $footerY, 148, 8, 'F');
        $pdf->SetTextColor(200, 210, 225);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(0, $footerY + 1.5);
        $pdf->Cell(148, 5, APP_NAME . ' — Documento gerado automaticamente em ' . date('d/m/Y H:i'), 0, 0, 'C');

        $pdf->Output('I', 'relatorio_' . $id . '.pdf');
        exit;
    }

    /**
     * Desenha uma faixa de secção com título
     */
    private function pdfSeccao(FPDF $pdf, $y, $titulo) {
        $pdf->SetFillColor(43, 87, 151);
        $pdf->SetDrawColor(43, 87, 151);
        $pdf->Rect(8, $y, 132, 6.5, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetXY(10, $y + 0.8);
        $pdf->Cell(128, 5.5, $titulo, 0, 0, 'L');
    }
}
