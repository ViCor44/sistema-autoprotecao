<?php

class Router {
    public function resolve(array $query) {
        $controllerName = $query['controler'] ?? $query['controller'] ?? 'home';
        $action = $query['acao'] ?? $query['action'] ?? 'index';
        $id = $query['id'] ?? null;

        $controller = $this->formatControllerName($controllerName) . 'Controller';
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

    /**
     * Converter snake_case para PascalCase
     * tipo_equipamento → TipoEquipamento
     * equipamento → Equipamento
     */
    private function formatControllerName($name) {
        $name = strtolower((string)$name);
        $parts = explode('_', $name);
        $parts = array_map('ucfirst', $parts);
        return implode('', $parts);
    }
}