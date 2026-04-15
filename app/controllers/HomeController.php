<?php
/**
 * Controller para a Página Principal
 */
class HomeController extends Controller {
    private $calendarioManutencao;
    private $relatorio;
    private $equipamento;

    public function __construct() {
        $this->calendarioManutencao = new CalendarioManutencao();
        $this->relatorio = new Relatorio();
        $this->equipamento = new Equipamento();
    }

    /**
     * Página inicial - Dashboard
     */
    public function index() {
        $proximasManutencoes = $this->calendarioManutencao->getProximos(7);
        $manutencoeVencidas = $this->calendarioManutencao->getVencidos();
        $relatoriosRecentes = $this->relatorio->getAll(['data_inicio' => date('Y-m-d', strtotime('-30 days'))]);
        $totalEquipamentos = count($this->equipamento->getAll(['ativo' => 1]));
        $totalInspecoesAgendadas = count($this->calendarioManutencao->getAll(['status' => 'agendado']));
        $this->render('home/index', compact(
            'proximasManutencoes',
            'manutencoeVencidas',
            'relatoriosRecentes',
            'totalEquipamentos',
            'totalInspecoesAgendadas'
        ));
    }

    /**
     * Página de login
     */
    public function login() {
        if (isset($_SESSION['utilizador_id'])) {
            $this->redirect('home');
        }

        $this->render('home/login');
    }

    /**
     * Processar login
     */
    public function autenticar() {
        $this->requirePost('home', 'login');

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $utilizador = new Utilizador();
        
        if ($utilizador->verificarSenha($email, $senha)) {
            $user = $utilizador->getByEmail($email);
            $_SESSION['utilizador_id'] = $user['id'];
            $_SESSION['utilizador_nome'] = $user['nome'];
            $_SESSION['utilizador_email'] = $user['email'];
            $_SESSION['utilizador_funcao'] = $user['funcao'];

            $this->flash('Bem-vindo, ' . $user['nome'] . '!', 'sucesso');
            $this->redirect('home');
        } else {
            $this->flash('Email ou senha incorretos.', 'erro');
            $this->redirect('home', 'login');
        }
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        $this->redirect('home', 'login');
    }

    /**
     * Página para exibir mensagens de erro
     */
    public function erro($mensagem = 'Página não encontrada') {
        $this->render('home/erro', compact('mensagem'));
    }
}
