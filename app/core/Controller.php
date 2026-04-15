<?php

abstract class Controller {
    protected function render($view, array $data = []) {
        View::render($view, $data);
    }

    protected function redirect($controller, $action = 'index', array $params = []) {
        $query = array_merge([
            'controler' => $controller,
            'acao' => $action,
        ], $params);

        header('Location: index.php?' . http_build_query($query));
        exit;
    }

    protected function flash($message, $type = 'sucesso') {
        $_SESSION['mensagem'] = $message;
        $_SESSION['tipo_mensagem'] = $type;
    }

    protected function requirePost($controller, $action = 'index', array $params = []) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($controller, $action, $params);
        }
    }
}