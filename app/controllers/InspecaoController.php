<?php
class InspecaoController {
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
        require APP_PATH . '/views/inspecoes/listar.php';
    }

    public function preencher($id) {
        $inspecao = $this->calendario->getById($id);
        require APP_PATH . '/views/inspecoes/preencher.php';
    }

    public function guardar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=inspecao&acao=listar');
            exit;
        }
        $dados = [
            'parecer' => $_POST['parecer'] ?? '',
            'equipamentos_avariados' => $_POST['equipamentos_avariados'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? ''
        ];
        $this->calendario->atualizarInspecao($id, $dados);
        $this->calendario->updateStatus($id, 'concluida');
        // Gera relatório automaticamente
        $inspecao = $this->calendario->getById($id);
        $this->relatorio->createFromInspecao($inspecao, $_SESSION['utilizador_id'] ?? 0);
        $_SESSION['mensagem'] = 'Inspeção preenchida e relatório gerado!';
        $_SESSION['tipo_mensagem'] = 'sucesso';
        header('Location: index.php?controler=inspecao&acao=ver&id=' . $id);
        exit;
    }

    public function ver($id) {
        $inspecao = $this->calendario->getById($id);
        $relatorio = $this->relatorio->getByCalendarioId($id);
        require APP_PATH . '/views/inspecoes/ver.php';
    }

    public function exportar_pdf($id) {
        $relatorio = $this->relatorio->getByCalendarioId($id);
        if (!$relatorio) {
            $_SESSION['mensagem'] = 'Relatório não encontrado.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=inspecao&acao=listar');
            exit;
        }
        require_once APP_PATH . '/libs/fpdf/fpdf.php';
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Relatório de Inspeção',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Ln(5);
        $pdf->Cell(0,10,'Equipamento: ' . $relatorio['tipo_equipamento'] . ' (' . $relatorio['localizacao'] . ')',0,1);
        $pdf->Cell(0,10,'Data: ' . date('d/m/Y', strtotime($relatorio['data_relatorio'])),0,1);
        $pdf->Cell(0,10,'Responsável: ' . $relatorio['responsavel_nome'],0,1);
        $pdf->Cell(0,10,'Condição: ' . ucfirst($relatorio['condicoes_encontradas']),0,1);
        $pdf->Ln(5);
        $pdf->MultiCell(0,8,'Descrição: ' . $relatorio['descricao']);
        $pdf->Ln(5);
        $pdf->MultiCell(0,8,'Observações: ' . $relatorio['observacoes']);
        $pdf->Output('I', 'relatorio_inspecao_'.$id.'.pdf');
        exit;
    }
}
