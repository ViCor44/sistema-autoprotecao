<?php
class InspecaoController extends Controller {
    private $calendario;
    private $relatorio;
    private $equipamento;

    public function __construct() {
        $this->calendario = new CalendarioManutencao();
        $this->relatorio = new Relatorio();
        $this->equipamento = new Equipamento();
    }

    public function listar() {
        $inspecoes = $this->calendario->getAll(['tipo_inspecao' => 'inspecao']);
        $this->render('inspecoes/listar', compact('inspecoes'));
    }

    public function preencher($id) {
        $inspecao = $this->calendario->getById($id);
        if (!$inspecao) {
            $this->flash('Inspeção agendada não encontrada.', 'erro');
            $this->redirect('inspecao', 'listar');
        }
        $this->render('inspecoes/preencher', compact('inspecao'));
    }

    public function guardar($id) {
        $this->requirePost('inspecao', 'listar');
        $dados = [
            'parecer' => $_POST['parecer'] ?? '',
            'equipamentos_avariados' => $_POST['equipamentos_avariados'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'condicoes_encontradas' => $_POST['condicoes_encontradas'] ?? '',
            'proxima_inspecao' => $_POST['proxima_inspecao'] ?? null,
        ];
        $this->calendario->atualizarInspecao($id, $dados);

        if (!empty($dados['proxima_inspecao'])) {
            $this->equipamento->atualizarProximaManutencaoPorAgendamento($id, $dados['proxima_inspecao']);
        }

        $this->calendario->updateStatus($id, 'concluido');
        $inspecao = $this->calendario->getById($id);
        $this->relatorio->createFromInspecao($inspecao, $_SESSION['utilizador_id'] ?? 0);
        $this->flash('Inspeção preenchida e relatório gerado!', 'sucesso');
        $this->redirect('inspecao', 'ver', ['id' => $id]);
    }

    public function ver($id) {
        $inspecao = $this->calendario->getById($id);
        if (!$inspecao) {
            $this->flash('Inspeção agendada não encontrada.', 'erro');
            $this->redirect('inspecao', 'listar');
        }
        $relatorio = $this->relatorio->getByCalendarioId($id);
        $this->render('inspecoes/ver', compact('inspecao', 'relatorio'));
    }

    public function exportar_pdf($id) {
        $relatorio = $this->relatorio->getByCalendarioId($id);
        if (!$relatorio) {
            $this->flash('Relatório não encontrado.', 'erro');
            $this->redirect('inspecao', 'listar');
        }
        require_once APP_PATH . '/libs/fpdf/fpdf.php';
        $pdf = new FPDF('P', 'mm', 'A5');
        $pdf->AddPage();
        $pdf->SetDrawColor(70, 70, 70);
        $pdf->SetLineWidth(0.25);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 10);
        $pdf->Cell(52, 8, APP_NAME, 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetXY(70, 9);
        $pdf->Cell(68, 10, 'Relatório de Inspeção', 0, 1, 'R');
        $pdf->Line(10, 20, 138, 20);

        $numero = str_pad((string)$relatorio['id'], 4, '0', STR_PAD_LEFT);
        $tecnico = $relatorio['responsavel_nome'] ?: '-';
        $data = date('d/m/Y', strtotime($relatorio['data_relatorio']));

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

        $ambito = !empty($relatorio['localizacao']) ? $relatorio['localizacao'] : 'Todos os equipamentos do tipo';
        $conteudo = [];
        $conteudo[] = 'Equipamento: ' . ($relatorio['tipo_equipamento'] ?: '-') . ' (' . $ambito . ')';
        $conteudo[] = 'Condição: ' . ucfirst((string)($relatorio['condicoes_encontradas'] ?: '-'));
        $conteudo[] = '';
        $conteudo[] = 'Descrição:';
        $conteudo[] = $relatorio['descricao'] ?: '-';
        $conteudo[] = '';
        $conteudo[] = 'Observações:';
        $conteudo[] = $relatorio['observacoes'] ?: '-';

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
        $pdf->MultiCell($boxW - 4, 5, implode("\n", $conteudo));

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, 201);
        $pdf->Cell(128, 5, 'Documento gerado automaticamente por ' . APP_NAME, 0, 1, 'C');

        $pdf->Output('I', 'relatorio_inspecao_'.$id.'.pdf');
        exit;
    }
}
