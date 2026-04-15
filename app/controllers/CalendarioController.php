<?php
/**
 * Controller para Calendário de Inspeções
 */
class CalendarioController extends Controller {
    private $calendario;
    private $equipamento;
    private $relatorio;
    private $tiposEquipamentos = [];

    public function __construct() {
        $this->calendario = new CalendarioManutencao();
        $this->equipamento = new Equipamento();
        $this->relatorio = new Relatorio();
        $this->carregarTiposEquipamentos();
    }

    /**
     * Carregar tipos de equipamentos
     */
    private function carregarTiposEquipamentos() {
        $db = new Database();
        $resultado = $db->query("SELECT id, nome FROM tipos_equipamentos WHERE ativo = TRUE ORDER BY nome ASC");
        $this->tiposEquipamentos = $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Exibir calendário
     */
    public function calendario() {
        $mes = $_GET['mes'] ?? date('m');
        $ano = $_GET['ano'] ?? date('Y');
        $tipoEquipamentoId = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;
        $status = $_GET['status'] ?? '';

        // Validar mês e ano
        $mes = min(max((int)$mes, 1), 12);
        $ano = max((int)$ano, 2000);

        $filtros = [
            'data_inicio' => "$ano-$mes-01",
            'data_fim' => date('Y-m-t', mktime(0, 0, 0, $mes, 1, $ano))
        ];

        if ($tipoEquipamentoId > 0) {
            $filtros['tipo_equipamento_id'] = $tipoEquipamentoId;
        }

        if ($status !== '') {
            $filtros['status'] = $status;
        }

        $agendamentos = $this->calendario->getAll($filtros);
        $tiposEquipamentos = $this->tiposEquipamentos;
        $statusFiltro = $status;
        $tipoFiltro = $tipoEquipamentoId;

        $this->render('calendario/calendario', compact(
            'mes',
            'ano',
            'agendamentos',
            'tiposEquipamentos',
            'statusFiltro',
            'tipoFiltro'
        ));
    }

    /**
     * Listar agendamentos
     */
    public function listar() {
        $filtros = [];
        
        if (isset($_GET['status'])) {
            $filtros['status'] = $_GET['status'];
        }
        
        $agendamentos = $this->calendario->getAll($filtros);

        $this->render('calendario/listar', compact('agendamentos'));
    }

    /**
     * Ver detalhes de um agendamento
     */
    public function ver($id) {
        $agendamento = $this->calendario->getById($id);
        
        if (!$agendamento) {
            $this->flash('Agendamento não encontrado.', 'erro');
            $this->redirect('calendario', 'listar');
        }

        $relatorioInspecao = $this->relatorio->getByCalendarioId((int)$id);

        $returnMes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m', strtotime($agendamento['data_inspecao']));
        $returnAno = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y', strtotime($agendamento['data_inspecao']));

        $this->render('calendario/ver', compact('agendamento', 'relatorioInspecao', 'returnMes', 'returnAno'));
    }

    /**
     * Formulário para editar agendamento
     */
    public function editar($id) {
        $agendamento = $this->calendario->getById($id);

        if (!$agendamento) {
            $this->flash('Agendamento não encontrado.', 'erro');
            $this->redirect('calendario', 'listar');
        }

        $equipamentos = $this->equipamento->getAll();
        $tiposEquipamentos = $this->tiposEquipamentos;
        $returnMes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m', strtotime($agendamento['data_inspecao']));
        $returnAno = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y', strtotime($agendamento['data_inspecao']));

        $this->render('calendario/editar', compact(
            'agendamento',
            'equipamentos',
            'tiposEquipamentos',
            'returnMes',
            'returnAno'
        ));
    }

    /**
     * Atualizar agendamento
     */
    public function atualizar($id) {
        $this->requirePost('calendario', 'listar');

        $dados = [
            'tipo_equipamento_id' => (int)($_POST['tipo_equipamento_id'] ?? 0),
            'equipamento_id' => $_POST['equipamento_id'] ?? 0,
            'tipo_inspecao' => $_POST['tipo_inspecao'] ?? 'inspecao',
            'descricao' => $_POST['descricao'] ?? '',
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'status' => $_POST['status'] ?? 'agendado',
            'prioridade' => $_POST['prioridade'] ?? 'normal'
        ];

        if ($dados['tipo_equipamento_id'] <= 0) {
            $this->flash('Selecione o tipo de equipamento para o agendamento.', 'erro');
            $this->redirect('calendario', 'editar', ['id' => $id]);
        }

        if ($this->calendario->update($id, $dados)) {
            $this->flash('Agendamento atualizado com sucesso!', 'sucesso');
            $this->redirect('calendario', 'ver', [
                'id' => $id,
                'mes' => (int)($_POST['return_mes'] ?? date('m')),
                'ano' => (int)($_POST['return_ano'] ?? date('Y')),
            ]);
        }

        $this->flash('Erro ao atualizar agendamento.', 'erro');
        $this->redirect('calendario', 'editar', ['id' => $id]);
    }

    /**
     * Formulário para agendar
     */
    public function agendar($equipamento_id = null) {
        $equipamentos = $this->equipamento->getAll();
        $tiposEquipamentos = $this->tiposEquipamentos;
        $dataSelecionada = $_GET['data'] ?? date('Y-m-d');
        $returnMes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m', strtotime($dataSelecionada));
        $returnAno = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y', strtotime($dataSelecionada));

        $this->render('calendario/agendar', compact(
            'equipamento_id',
            'equipamentos',
            'tiposEquipamentos',
            'dataSelecionada',
            'returnMes',
            'returnAno'
        ));
    }

    /**
     * Salvar agendamento
     */
    public function salvar() {
        $this->requirePost('calendario', 'listar');

        $dados = [
            'tipo_equipamento_id' => (int)($_POST['tipo_equipamento_id'] ?? 0),
            'equipamento_id' => $_POST['equipamento_id'] ?? 0,
            'data_inspecao' => $_POST['data_inspecao'] ?? date('Y-m-d'),
            'tipo_inspecao' => $_POST['tipo_inspecao'] ?? 'inspecao',
            'descricao' => $_POST['descricao'] ?? '',
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'status' => 'agendado',
            'prioridade' => $_POST['prioridade'] ?? 'normal'
        ];

        if ($dados['tipo_equipamento_id'] <= 0) {
            $this->flash('Selecione o tipo de equipamento para a inspeção.', 'erro');
            $this->redirect('calendario', 'agendar', [
                'data' => $dados['data_inspecao'],
                'mes' => (int)($_POST['return_mes'] ?? date('m', strtotime($dados['data_inspecao']))),
                'ano' => (int)($_POST['return_ano'] ?? date('Y', strtotime($dados['data_inspecao']))),
            ]);
        }

        $agendamentoId = $this->calendario->create($dados);

        if ($agendamentoId) {
            $agendamento = $this->calendario->getById((int)$agendamentoId);
            $responsavelRelatorio = $_SESSION['utilizador_id'] ?? 0;
            $relatorioId = $this->relatorio->createFromInspecao($agendamento, $responsavelRelatorio);

            if ($relatorioId) {
                $this->flash('Agendamento criado e relatório gerado com sucesso!', 'sucesso');
            } else {
                $this->flash('Agendamento criado, mas não foi possível gerar o relatório automático.', 'erro');
            }
            $this->redirect('calendario', 'calendario', [
                'mes' => (int)($_POST['return_mes'] ?? date('m', strtotime($dados['data_inspecao']))),
                'ano' => (int)($_POST['return_ano'] ?? date('Y', strtotime($dados['data_inspecao']))),
            ]);
        } else {
            $this->flash('Erro ao criar agendamento.', 'erro');
            $this->redirect('calendario', 'agendar', [
                'data' => $dados['data_inspecao'],
                'mes' => (int)($_POST['return_mes'] ?? date('m', strtotime($dados['data_inspecao']))),
                'ano' => (int)($_POST['return_ano'] ?? date('Y', strtotime($dados['data_inspecao']))),
            ]);
        }
    }

    /**
     * Dashboard com próximas inspeções
     */
    public function dashboard() {
        $proximasInspecoes = $this->calendario->getProximos(30);
        $inspecoesVencidas = $this->calendario->getVencidos();
        $equipamentosPendentes = $this->equipamento->getEquipamentosComVistoriaPendente();

        $this->render('calendario/dashboard', compact('proximasInspecoes', 'inspecoesVencidas', 'equipamentosPendentes'));
    }

    /**
     * Atualizar status de agendamento
     */
    public function atualizarStatus($id) {
        $this->requirePost('calendario', 'listar');

        $status = $_POST['status'] ?? 'agendado';
        
        if ($this->calendario->updateStatus($id, $status)) {
            $this->flash('Status atualizado com sucesso!', 'sucesso');
        } else {
            $this->flash('Erro ao atualizar status.', 'erro');
        }

        $this->redirect('calendario', 'ver', ['id' => $id]);
    }
}
