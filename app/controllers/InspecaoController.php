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
        $pdf->Output('I', 'relatorio_inspecao_'.$id.'.pdf');
        exit;
    }
}
