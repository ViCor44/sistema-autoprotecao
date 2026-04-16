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
        // Geração simples de PDF (exemplo com FPDF)
        require_once APP_PATH . '/libs/fpdf/fpdf.php';
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Relatório de Inspeção',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Ln(5);
        $ambito = !empty($relatorio['localizacao']) ? $relatorio['localizacao'] : 'Todos os equipamentos do tipo';
        $pdf->Cell(0,10,utf8_decode('Equipamento: ' . $relatorio['tipo_equipamento'] . ' (' . $ambito . ')'),0,1);
        $pdf->Cell(0,10,'Data: ' . date('d/m/Y', strtotime($relatorio['data_relatorio'])),0,1);
        $pdf->Cell(0,10,utf8_decode('Responsável: ' . $relatorio['responsavel_nome']),0,1);
        $pdf->Cell(0,10,utf8_decode('Condição: ' . ucfirst($relatorio['condicoes_encontradas'] ?: '-')),0,1);
        $pdf->Ln(5);
        $pdf->MultiCell(0,8,utf8_decode('Descrição: ' . ($relatorio['descricao'] ?: '-')));
        $pdf->Ln(5);
        $pdf->MultiCell(0,8,utf8_decode('Observações: ' . ($relatorio['observacoes'] ?: '-')));

        if (!empty($itens)) {
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode('Itens de Verificação'), 0, 1);
            $pdf->SetFont('Arial', '', 11);
            foreach ($itens as $item) {
                $linha = sprintf(
                    '%s | %s | %s',
                    $item['descricao_verificacao'],
                    strtoupper((string)$item['resultado']),
                    $item['observacao'] ?: '-'
                );
                $pdf->MultiCell(0, 7, utf8_decode($linha));
            }
        }

        $pdf->Output('I', 'relatorio_inspecao_'.$id.'.pdf');
        exit;
    }
}
