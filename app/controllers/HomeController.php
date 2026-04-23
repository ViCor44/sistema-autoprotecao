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
        $planeamentoManutencoes = array_merge($manutencoeVencidas, $proximasManutencoes);
        usort($planeamentoManutencoes, function ($a, $b) {
            return strtotime((string)($a['data_inspecao'] ?? '')) <=> strtotime((string)($b['data_inspecao'] ?? ''));
        });
        $relatoriosRecentes = $this->relatorio->getAll(['data_inicio' => date('Y-m-d', strtotime('-30 days'))]);
        $totalEquipamentos = count($this->equipamento->getAll(['ativo' => 1]));
        $totalInspecoesAgendadas = count($this->calendarioManutencao->getAll(['status' => 'agendado']));
        $this->render('home/index', compact(
            'proximasManutencoes',
            'manutencoeVencidas',
            'planeamentoManutencoes',
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

    public function registo() {
        if (isset($_SESSION['utilizador_id'])) {
            $this->redirect('home');
        }

        $this->render('home/registo');
    }

    public function registar() {
        $this->requirePost('home', 'registo');

        $dados = [
            'nome' => trim((string)($_POST['nome'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'telefone' => trim((string)($_POST['telefone'] ?? '')),
            'senha' => (string)($_POST['senha'] ?? ''),
        ];
        $confirmacao = (string)($_POST['confirmar_senha'] ?? '');

        if ($dados['nome'] === '' || $dados['email'] === '' || $dados['senha'] === '') {
            $this->flash('Preencha nome, email e senha para concluir o registo.', 'erro');
            $this->redirect('home', 'registo');
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('Introduza um email válido.', 'erro');
            $this->redirect('home', 'registo');
        }

        if (strlen($dados['senha']) < 8) {
            $this->flash('A senha deve ter pelo menos 8 caracteres.', 'erro');
            $this->redirect('home', 'registo');
        }

        if ($dados['senha'] !== $confirmacao) {
            $this->flash('A confirmação da senha não coincide.', 'erro');
            $this->redirect('home', 'registo');
        }

        $utilizador = new Utilizador();
        if ($utilizador->emailExiste($dados['email'])) {
            $this->flash('Já existe uma conta registada com esse email.', 'erro');
            $this->redirect('home', 'registo');
        }

        if ($utilizador->criarRegistoPendente($dados)) {
            $this->flash('Registo submetido com sucesso. Aguarda aprovação de um administrador.', 'sucesso');
            $this->redirect('home', 'login');
        }

        $this->flash('Não foi possível concluir o registo.', 'erro');
        $this->redirect('home', 'registo');
    }

    /**
     * Processar login
     */
    public function autenticar() {
        $this->requirePost('home', 'login');

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $utilizador = new Utilizador();
        $resultado = $utilizador->autenticar($email, $senha);

        if (!empty($resultado['ok'])) {
            $user = $resultado['utilizador'];
            $_SESSION['utilizador_id'] = $user['id'];
            $_SESSION['utilizador_nome'] = $user['nome'];
            $_SESSION['utilizador_email'] = $user['email'];
            $_SESSION['utilizador_funcao'] = $user['funcao'];

            $this->flash('Bem-vindo, ' . $user['nome'] . '!', 'sucesso');
            $this->redirect('home');
        }

        $mensagens = [
            'credenciais' => 'Email ou senha incorretos.',
            'pendente' => 'A conta existe, mas ainda aguarda aprovação de um administrador.',
            'inativo' => 'A conta está inativa. Contacte um administrador.',
        ];

        $this->flash($mensagens[$resultado['motivo'] ?? 'credenciais'] ?? 'Não foi possível autenticar.', 'erro');
        $this->redirect('home', 'login');
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
