<?php

class View {
    public static function render($view, array $data = []) {
        $viewPath = APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception('View não encontrada: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main.php';
    }
}