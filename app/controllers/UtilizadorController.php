<?php

class UtilizadorController extends Controller {
    private $utilizador;
    private $rolesPermitidas = ['tecnico', 'administrador'];

    public function __construct() {
        $this->requireAdmin();
        $this->utilizador = new Utilizador();
    }

    public function listar() {
        $pendentes = $this->utilizador->getPendentesAprovacao();
        $utilizadores = $this->utilizador->getAll(['incluir_todos' => true]);
        $this->render('utilizadores/listar', compact('pendentes', 'utilizadores'));
    }

    public function aprovar($id) {
        $this->requirePost('utilizador', 'listar');

        $funcao = strtolower(trim((string)($_POST['funcao'] ?? 'tecnico')));
        if (!in_array($funcao, $this->rolesPermitidas, true)) {
            $this->flash('Selecione uma função válida para aprovar o utilizador.', 'erro');
            $this->redirect('utilizador', 'listar');
        }

        if ($this->utilizador->aprovar($id, (int)($_SESSION['utilizador_id'] ?? 0), $funcao)) {
            $this->flash('Utilizador aprovado com sucesso.', 'sucesso');
        } else {
            $this->flash('Não foi possível aprovar o utilizador.', 'erro');
        }

        $this->redirect('utilizador', 'listar');
    }

    public function inativar($id) {
        $this->requirePost('utilizador', 'listar');

        if ((int)$id === (int)($_SESSION['utilizador_id'] ?? 0)) {
            $this->flash('Não pode inativar a sua própria conta.', 'erro');
            $this->redirect('utilizador', 'listar');
        }

        if ($this->utilizador->inativar($id)) {
            $this->flash('Utilizador inativado com sucesso.', 'sucesso');
        } else {
            $this->flash('Não foi possível inativar o utilizador.', 'erro');
        }

        $this->redirect('utilizador', 'listar');
    }

    public function ativar($id) {
        $this->requirePost('utilizador', 'listar');

        if ($this->utilizador->ativar($id)) {
            $this->flash('Utilizador reativado com sucesso.', 'sucesso');
        } else {
            $this->flash('Não foi possível reativar o utilizador.', 'erro');
        }

        $this->redirect('utilizador', 'listar');
    }
}