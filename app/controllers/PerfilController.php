<?php

class PerfilController extends Controller {
    private $utilizador;

    public function __construct() {
        $this->utilizador = new Utilizador();
    }

    public function editar() {
        $utilizadorId = (int)($_SESSION['utilizador_id'] ?? 0);
        $utilizador = $this->utilizador->getById($utilizadorId);

        if (!$utilizador) {
            $this->flash('Utilizador não encontrado.', 'erro');
            $this->redirect('home', 'index');
        }

        $this->render('perfil/editar', compact('utilizador'));
    }

    public function atualizar() {
        $this->requirePost('perfil', 'editar');

        $utilizadorId = (int)($_SESSION['utilizador_id'] ?? 0);
        $utilizadorAtual = $this->utilizador->getById($utilizadorId);
        if (!$utilizadorAtual) {
            $this->flash('Utilizador não encontrado.', 'erro');
            $this->redirect('home', 'index');
        }

        $dados = [
            'nome' => trim((string)($_POST['nome'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'telefone' => trim((string)($_POST['telefone'] ?? '')),
        ];

        if ($dados['nome'] === '' || $dados['email'] === '') {
            $this->flash('Nome e email são obrigatórios.', 'erro');
            $this->redirect('perfil', 'editar');
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('Introduza um email válido.', 'erro');
            $this->redirect('perfil', 'editar');
        }

        if ($this->utilizador->emailExiste($dados['email'], $utilizadorId)) {
            $this->flash('Este email já está a ser usado por outro utilizador.', 'erro');
            $this->redirect('perfil', 'editar');
        }

        $senhaNova = (string)($_POST['senha_nova'] ?? '');
        $confirmarSenha = (string)($_POST['confirmar_senha'] ?? '');

        if ($senhaNova !== '' || $confirmarSenha !== '') {
            if (strlen($senhaNova) < 8) {
                $this->flash('A nova senha deve ter pelo menos 8 caracteres.', 'erro');
                $this->redirect('perfil', 'editar');
            }

            if ($senhaNova !== $confirmarSenha) {
                $this->flash('A confirmação da senha não coincide.', 'erro');
                $this->redirect('perfil', 'editar');
            }
        }

        $this->utilizador->atualizarPerfil($utilizadorId, $dados);

        if ($senhaNova !== '') {
            $this->utilizador->atualizarSenha($utilizadorId, $senhaNova);
        }

        $_SESSION['utilizador_nome'] = $dados['nome'];
        $_SESSION['utilizador_email'] = $dados['email'];

        $this->flash('Perfil atualizado com sucesso!', 'sucesso');
        $this->redirect('perfil', 'editar');
    }
}