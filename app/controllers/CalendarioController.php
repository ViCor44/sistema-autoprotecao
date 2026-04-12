<?php
/**
 * Controller para Calendário de Manutenção
 */
class CalendarioController {
    private $calendario;
    private $equipamento;

    public function __construct() {
        $this->calendario = new CalendarioManutencao();
        $this->equipamento = new Equipamento();
    }

    /**
     * Exibir calendário
     */
    public function calendario() {
        $mes = $_GET['mes'] ?? date('m');
        $ano = $_GET['ano'] ?? date('Y');

        // Validar mês e ano
        $mes = min(max((int)$mes, 1), 12);
        $ano = max((int)$ano, 2000);

        $agendamentos = $this->calendario->getAll([
            'data_inicio' => "$ano-$mes-01",
            'data_fim' => date('Y-m-t', mktime(0, 0, 0, $mes, 1, $ano))
        ]);

        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'calendario' . DIRECTORY_SEPARATOR . 'calendario.php';
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
        
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'calendario' . DIRECTORY_SEPARATOR . 'listar.php';
    }

    /**
     * Ver detalhes de um agendamento
     */
    public function ver($id) {
        $agendamento = $this->calendario->getById($id);
        
        if (!$agendamento) {
            $_SESSION['mensagem'] = 'Agendamento não encontrado.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=calendario&acao=listar');
            exit;
        }

        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'calendario' . DIRECTORY_SEPARATOR . 'ver.php';
    }

    /**
     * Formulário para agendar
     */
    public function agendar($equipamento_id = null) {
        $equipamentos = $this->equipamento->getAll();
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'calendario' . DIRECTORY_SEPARATOR . 'agendar.php';
    }

    /**
     * Salvar agendamento
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=calendario&acao=listar');
            exit;
        }

        $dados = [
            'equipamento_id' => $_POST['equipamento_id'] ?? 0,
            'data_inspecao' => $_POST['data_inspecao'] ?? date('Y-m-d'),
            'tipo_inspecao' => $_POST['tipo_inspecao'] ?? 'inspeção',
            'descricao' => $_POST['descricao'] ?? '',
            'responsavel_id' => $_POST['responsavel_id'] ?? null,
            'status' => 'agendado',
            'prioridade' => $_POST['prioridade'] ?? 'normal'
        ];

        if ($this->calendario->create($dados)) {
            $_SESSION['mensagem'] = 'Agendamento criado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
            header('Location: index.php?controler=calendario&acao=listar');
        } else {
            $_SESSION['mensagem'] = 'Erro ao criar agendamento.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=calendario&acao=agendar');
        }
        exit;
    }

    /**
     * Dashboard com próximas manutenções
     */
    public function dashboard() {
        $proximasManutencoes = $this->calendario->getProximos(30);
        $manutencoeVencidas = $this->calendario->getVencidos();
        $equipamentosPendentes = $this->equipamento->getEquipamentosComManutencaoPendente();

        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'calendario' . DIRECTORY_SEPARATOR . 'dashboard.php';
    }

    /**
     * Atualizar status de agendamento
     */
    public function atualizarStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=calendario&acao=listar');
            exit;
        }

        $status = $_POST['status'] ?? 'agendado';
        
        if ($this->calendario->updateStatus($id, $status)) {
            $_SESSION['mensagem'] = 'Status atualizado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
        } else {
            $_SESSION['mensagem'] = 'Erro ao atualizar status.';
            $_SESSION['tipo_mensagem'] = 'erro';
        }
        
        header('Location: index.php?controler=calendario&acao=ver&id=' . $id);
        exit;
    }
}
