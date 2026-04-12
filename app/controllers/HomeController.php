<?php
/**
 * Controller para a Página Principal
 */
class HomeController {
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
        // Obter dados para o dashboard
        $proximasManutencoes = $this->calendarioManutencao->getProximos(7);
        $manutencoeVencidas = $this->calendarioManutencao->getVencidos();
        $relatoriosRecentes = $this->relatorio->getAll(['data_inicio' => date('Y-m-d', strtotime('-30 days'))]);
        $totalEquipamentos = count($this->equipamento->getAll(['ativo' => 1]));

        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'index.php';
    }

    /**
     * Página de login
     */
    public function login() {
        if (isset($_SESSION['utilizador_id'])) {
            header('Location: index.php');
            exit;
        }

        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'login.php';
    }

    /**
     * Processar login
     */
    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=home&acao=login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $utilizador = new Utilizador();
        
        if ($utilizador->verificarSenha($email, $senha)) {
            $user = $utilizador->getByEmail($email);
            $_SESSION['utilizador_id'] = $user['id'];
            $_SESSION['utilizador_nome'] = $user['nome'];
            $_SESSION['utilizador_email'] = $user['email'];
            $_SESSION['utilizador_funcao'] = $user['funcao'];

            $_SESSION['mensagem'] = 'Bem-vindo, ' . $user['nome'] . '!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
            
            header('Location: index.php');
        } else {
            $_SESSION['mensagem'] = 'Email ou senha incorretos.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=home&acao=login');
        }
        exit;
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        header('Location: index.php?controler=home&acao=login');
        exit;
    }

    /**
     * Página para exibir mensagens de erro
     */
    public function erro($mensagem = 'Página não encontrada') {
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'erro.php';
    }
}
