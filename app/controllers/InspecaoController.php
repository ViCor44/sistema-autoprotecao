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
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,8,'Relatório de Inspeção',0,1,'C');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(0,6,APP_NAME,0,1,'C');
        $pdf->Cell(0,6,'Data de emissão: ' . date('d/m/Y H:i'),0,1,'C');

        $pdf->Ln(2);
        $pdf->Cell(0,6,str_repeat('-', 60),0,1);

        $ambito = !empty($relatorio['localizacao']) ? $relatorio['localizacao'] : 'Todos os equipamentos do tipo';
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,7,'Informações do Relatório',0,1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(0,6,'Equipamento: ' . $relatorio['tipo_equipamento'] . ' (' . $ambito . ')',0,1);
        $pdf->Cell(0,6,'Data: ' . date('d/m/Y', strtotime($relatorio['data_relatorio'])),0,1);
        $pdf->Cell(0,6,'Responsável: ' . ($relatorio['responsavel_nome'] ?: '-'),0,1);
        $pdf->Cell(0,6,'Condição: ' . ucfirst((string)($relatorio['condicoes_encontradas'] ?: '-')),0,1);

        $pdf->Ln(2);
        $pdf->Cell(0,6,str_repeat('-', 60),0,1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,7,'Descrição e Observações',0,1);
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(0,6,'Descrição: ' . ($relatorio['descricao'] ?: '-'));
        $pdf->Ln(1);
        $pdf->MultiCell(0,6,'Observações: ' . ($relatorio['observacoes'] ?: '-'));

        $pdf->Ln(2);
        $pdf->Cell(0,6,str_repeat('-', 60),0,1);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(0,5,'Documento gerado automaticamente por ' . APP_NAME,0,1,'C');

        $pdf->Output('I', 'relatorio_inspecao_'.$id.'.pdf');
        exit;
    }
}
