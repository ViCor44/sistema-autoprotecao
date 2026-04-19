<?php

class Application {
    public function run() {
        $this->startSession();
        $this->loadConfiguration();
        $this->registerAutoloader();

        $router = new Router();
        $route = $router->resolve($_GET);

        $this->enforceAuthentication($route);
        $this->dispatch($route);
    }

    private function startSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function loadConfiguration() {
        require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Config.php';
    }

    private function registerAutoloader() {
        spl_autoload_register(function ($class) {
            $directories = [
                APP_PATH . DIRECTORY_SEPARATOR . 'core',
                APP_PATH . DIRECTORY_SEPARATOR . 'models',
                APP_PATH . DIRECTORY_SEPARATOR . 'controllers',
            ];

            foreach ($directories as $directory) {
                $file = $directory . DIRECTORY_SEPARATOR . $class . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }

    private function enforceAuthentication($route) {
        $publicRoutes = [
            'HomeController@login',
            'HomeController@autenticar',
            'HomeController@registo',
            'HomeController@registar',
            'HomeController@erro',
            'QrController@visualizar',  // Permitir visualização de QR sem autenticação
        ];

        $signature = $route['controller'] . '@' . $route['action'];

        if (!isset($_SESSION['utilizador_id']) && !in_array($signature, $publicRoutes, true)) {
            $_SESSION['mensagem'] = 'Por favor, faça login para continuar.';
            $_SESSION['tipo_mensagem'] = 'aviso';
            header('Location: index.php?controler=home&acao=login');
            exit;
        }

        if (isset($_SESSION['utilizador_id']) && in_array($signature, ['HomeController@login', 'HomeController@registo'], true)) {
            header('Location: index.php');
            exit;
        }
    }

    private function dispatch($route) {
        try {
            if (!class_exists($route['controller'])) {
                throw new Exception('Controller não encontrado: ' . $route['controller']);
            }

            $controller = new $route['controller']();

            if (!method_exists($controller, $route['action'])) {
                throw new Exception('Ação não encontrada: ' . $route['action']);
            }

            call_user_func_array([$controller, $route['action']], $route['params']);
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    private function handleException($exception) {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            http_response_code(500);
            echo "<div class='alert alert-danger'><strong>Erro:</strong> " . $exception->getMessage() . '</div>';
            echo '<pre>' . $exception->getTraceAsString() . '</pre>';
            return;
        }

        if (class_exists('HomeController')) {
            $controller = new HomeController();
            $controller->erro($exception->getMessage());
            return;
        }

        http_response_code(500);
        echo 'Ocorreu um erro interno.';
    }
}