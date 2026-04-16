<?php
/**
 * Controller para Relatórios
 */
class RelatorioController extends Controller {
    private $relatorio;
    private $equipamento;

    public function __construct() {
        $this->relatorio = new Relatorio();
        $this->equipamento = new Equipamento();
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
        $this->desenharCabecalhoPdf($pdf, 'Relatório de Inspeção');

        $ambito = !empty($relatorio['localizacao']) ? $relatorio['localizacao'] : 'Todos os equipamentos do tipo';
        $numeroDocumento = str_pad((string)$relatorio['id'], 4, '0', STR_PAD_LEFT);
        $tecnico = $relatorio['responsavel_nome'] ?: '-';
        $dataDocumento = date('d/m/Y', strtotime($relatorio['data_relatorio']));

        $this->desenharLinhaMeta($pdf, $numeroDocumento, $tecnico, $dataDocumento);

        $detalhes = [];
        $detalhes[] = 'Equipamento: ' . ($relatorio['tipo_equipamento'] ?: '-') . ' (' . $ambito . ')';
        $detalhes[] = 'Tipo: ' . ucfirst((string)($relatorio['tipo_relatorio'] ?: '-'));
        $detalhes[] = 'Condição: ' . ucfirst((string)($relatorio['condicoes_encontradas'] ?: '-'));
        $detalhes[] = 'Estado: ' . ($relatorio['assinado'] ? 'Assinado' : 'Pendente de assinatura');
        $detalhes[] = 'Próxima inspeção: ' . (!empty($relatorio['proxima_inspecao']) ? date('d/m/Y', strtotime($relatorio['proxima_inspecao'])) : '-');
        $detalhes[] = '';
        $detalhes[] = 'Descrição:';
        $detalhes[] = $relatorio['descricao'] ?: '-';
        $detalhes[] = '';
        $detalhes[] = 'Observações:';
        $detalhes[] = $relatorio['observacoes'] ?: '-';

        if (!empty($itens)) {
            $detalhes[] = '';
            $detalhes[] = 'Itens de verificação:';
            foreach ($itens as $item) {
                $detalhes[] = '- ' . ($item['descricao_verificacao'] ?: '-') . ' | Resultado: ' . ucfirst((string)($item['resultado'] ?: '-'));
                if (!empty($item['observacao'])) {
                    $detalhes[] = '  Obs: ' . $item['observacao'];
                }
            }
        }

        $this->desenharBlocoDetalhes($pdf, implode("\n", $detalhes));

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, 201);
        $pdf->Cell(128, 5, 'Documento gerado automaticamente por ' . APP_NAME, 0, 1, 'C');

        $pdf->Output('I', 'relatorio_inspecao_'.$id.'.pdf');
        exit;
    }

    private function desenharCabecalhoPdf(FPDF $pdf, $titulo) {
        $pdf->SetDrawColor(70, 70, 70);
        $pdf->SetLineWidth(0.25);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 10);
        $pdf->Cell(52, 8, APP_NAME, 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetXY(70, 9);
        $pdf->Cell(68, 10, $titulo, 0, 1, 'R');

        $pdf->Line(10, 20, 138, 20);
    }

    private function desenharLinhaMeta(FPDF $pdf, $numero, $tecnico, $data) {
        $y = 26;

        $pdf->SetFont('Arial', '', 11);
        $pdf->SetXY(12, $y + 4);
        $pdf->Cell(8, 6, 'Nº:', 0, 0, 'L');
        $pdf->Rect(22, $y + 2, 18, 10);
        $pdf->SetXY(22, $y + 4);
        $pdf->Cell(18, 6, $numero, 0, 0, 'C');

        $pdf->SetXY(44, $y + 4);
        $pdf->Cell(22, 6, 'Técnico:', 0, 0, 'L');
        $pdf->Rect(66, $y + 2, 44, 10);
        $pdf->SetXY(66, $y + 4);
        $pdf->Cell(44, 6, $tecnico, 0, 0, 'C');

        $pdf->SetXY(112, $y + 4);
        $pdf->Cell(10, 6, 'Data:', 0, 0, 'L');
        $pdf->Rect(122, $y + 2, 16, 10);
        $pdf->SetXY(122, $y + 4);
        $pdf->Cell(16, 6, $data, 0, 1, 'C');

        $pdf->Line(10, 40, 138, 40);
    }

    private function desenharBlocoDetalhes(FPDF $pdf, $conteudo) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 45);
        $pdf->Cell(40, 6, 'Detalhes:', 0, 1, 'L');

        $boxX = 13;
        $boxY = 52;
        $boxW = 122;
        $boxH = 142;

        $pdf->Rect($boxX, $boxY, $boxW, $boxH);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($boxX + 2, $boxY + 3);
        $pdf->MultiCell($boxW - 4, 5, $conteudo);
    }
}
