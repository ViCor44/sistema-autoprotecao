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
        if (empty($inspecao['responsavel_nome']) && !empty($_SESSION['utilizador_nome'])) {
            $inspecao['responsavel_nome'] = $_SESSION['utilizador_nome'];
        }
        // Pré-preencher próxima inspeção com a data já agendada para este tipo, se existir
        if (empty($inspecao['proxima_inspecao'])) {
            $proximaAgendada = $this->calendario->getProximaAgendadaPorTipo(
                $inspecao['tipo_equipamento_id'],
                $id
            );
            if ($proximaAgendada) {
                $inspecao['proxima_inspecao'] = $proximaAgendada;
            }
        }
        $this->render('inspecoes/preencher', compact('inspecao'));
    }

    public function guardar($id) {
        $this->requirePost('inspecao', 'listar');
        $responsavelId = (int)($_SESSION['utilizador_id'] ?? 0);
        $proximaInspecao = !empty($_POST['proxima_inspecao']) ? $_POST['proxima_inspecao'] : null;

        // Se não foi preenchida manualmente, tentar obter do próximo agendamento existente
        if (empty($proximaInspecao)) {
            $inspecaoAtual = $this->calendario->getById($id);
            if ($inspecaoAtual) {
                $proximaInspecao = $this->calendario->getProximaAgendadaPorTipo(
                    $inspecaoAtual['tipo_equipamento_id'],
                    $id
                );
            }
        }

        $dados = [
            'parecer' => $_POST['parecer'] ?? '',
            'equipamentos_avariados' => $_POST['equipamentos_avariados'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'condicoes_encontradas' => $_POST['condicoes_encontradas'] ?? '',
            'responsavel_id' => $responsavelId,
            'proxima_inspecao' => $proximaInspecao,
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
        // Se proxima_inspecao não foi guardada, tentar obter do calendário
        if (empty($inspecao['proxima_inspecao']) || $inspecao['proxima_inspecao'] === '0000-00-00') {
            $proxima = $this->calendario->getProximaAgendadaPorTipo(
                $inspecao['tipo_equipamento_id'],
                $id
            );
            if ($proxima) {
                $inspecao['proxima_inspecao'] = $proxima;
            }
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


        $numero  = 'INS-' . str_pad((string)$relatorio['id'], 4, '0', STR_PAD_LEFT);
        $tecnico = $relatorio['responsavel_nome'] ?: '-';
        $data    = date('d/m/Y', strtotime($relatorio['data_relatorio']));
        $ambito  = !empty($relatorio['localizacao'])
            ? $relatorio['localizacao']
            : (!empty($relatorio['equipamento_id']) ? '-' : 'Todos os equipamentos do tipo');

        // ── Cabeçalho ─────────────────────────────────────────────────────────
        $pdf->SetFillColor(26, 58, 92);
        $pdf->SetDrawColor(26, 58, 92);
        $pdf->SetLineWidth(0.2);
        $pdf->Rect(0, 0, 148, 22, 'F');

        $pdf->SetFillColor(220, 100, 20);
        $pdf->Rect(0, 22, 148, 1.5, 'F');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(8, 4.5);
        $pdf->Cell(70, 8, APP_NAME, 0, 0, 'L');

        $pdf->SetFont('Arial', '', 6.4);
        $pdf->SetXY(8, 11.2);
        $pdf->Cell(70, 4, 'Gestão técnica de inspeções e medidas de autoproteção.', 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY(70, 5);
        $pdf->Cell(70, 8, 'Relatório de Inspeção', 0, 0, 'R');

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(8, 14);
        $pdf->Cell(132, 5, 'Documento nº ' . $numero . '   |   Gerado em ' . date('d/m/Y H:i'), 0, 0, 'L');

        // ── Meta: Técnico / Data ──────────────────────────────────────────────
        $metaY = 28;

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

        $pdf->SetFillColor(235, 239, 245);
        $pdf->SetDrawColor(180, 190, 205);
        $pdf->Rect(100, $metaY, 40, 14, 'FD');
        $pdf->SetFillColor(43, 87, 151);
        $pdf->Rect(100, $metaY, 40, 5.5, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(101, $metaY + 0.5);
        $pdf->Cell(38, 5, 'DATA', 0, 0, 'L');
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(101, $metaY + 6.5);
        $pdf->Cell(38, 6, $data, 0, 0, 'L');

        // ── Secção: Informação ─────────────────────────────────────────────────
        $y = 48;

        // Secção
        $pdf->SetFillColor(43, 87, 151);
        $pdf->SetDrawColor(43, 87, 151);
        $pdf->Rect(8, $y, 132, 6.5, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetXY(10, $y + 0.8);
        $pdf->Cell(128, 5.5, 'INFORMAÇÃO GERAL', 0, 0, 'L');
        $y += 7;

        $campos = [
            ['Equipamento', $relatorio['tipo_equipamento'] ?: '-'],
            ['Âmbito', $ambito],
            ['Condições Encontradas', ucfirst((string)($relatorio['condicoes_encontradas'] ?: '-'))],
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

        // ── Secção: Descrição ─────────────────────────────────────────────────
        if (!empty($relatorio['descricao'])) {
            $y += 3;
            $pdf->SetFillColor(43, 87, 151);
            $pdf->SetDrawColor(43, 87, 151);
            $pdf->Rect(8, $y, 132, 6.5, 'F');
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetXY(10, $y + 0.8);
            $pdf->Cell(128, 5.5, 'DESCRIÇÃO', 0, 0, 'L');
            $y += 7;

            $pdf->SetTextColor(30, 30, 30);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY(10, $y + 1);
            $pdf->MultiCell(128, 5, $relatorio['descricao']);
            $y = $pdf->GetY() + 2;
        }

        // ── Observações ───────────────────────────────────────────────────────
        if (!empty($relatorio['observacoes'])) {
            $pdf->SetFillColor(43, 87, 151);
            $pdf->SetDrawColor(43, 87, 151);
            $pdf->Rect(8, $y, 132, 6.5, 'F');
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetXY(10, $y + 0.8);
            $pdf->Cell(128, 5.5, 'OBSERVAÇÕES', 0, 0, 'L');
            $y += 7;

            $pdf->SetTextColor(30, 30, 30);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY(10, $y + 1);
            $pdf->MultiCell(128, 5, $relatorio['observacoes']);
            $y = $pdf->GetY() + 2;
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

        $pdf->Output('I', 'inspecao_' . $id . '.pdf');
        exit;
    }
}
