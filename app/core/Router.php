<?php

class Router {
    public function resolve(array $query) {
        $controllerName = $query['controler'] ?? $query['controller'] ?? 'home';
        $action = $query['acao'] ?? $query['action'] ?? 'index';
        $id = $query['id'] ?? null;

        $controller = ucfirst(strtolower((string)$controllerName)) . 'Controller';
        $params = [];

        if ($id !== null && $id !== '') {
            $params[] = (int)$id;
        }

        return [
            'controller' => $controller,
            'action' => (string)$action,
            'params' => $params,
        ];
    }
}