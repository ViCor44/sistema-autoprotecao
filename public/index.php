<?php
/**
 * Ficheiro Principal da Aplicação - Sistema de Autoproteção
 * Ponto de entrada único (Front Controller)
 */

session_start();

// Carregar configurações
require_once __DIR__ . '/config/Config.php';

// Auto-loader de classes
function autoload($class) {
    $file = APP_PATH . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class . '.php';
    
    if (!file_exists($file)) {
        $file = APP_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $class . '.php';
    }
    
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('autoload');

// Capturar parâmetros da rota
$controler = isset($_GET['controler']) ? ucfirst($_GET['controler']) . 'Controller' : 'HomeController';
$acao = isset($_GET['acao']) ? $_GET['acao'] : 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Verificar se o utilizador está autenticado (exceto nas páginas de login)
if (!isset($_SESSION['utilizador_id']) && $controler !== 'HomeController') {
    $_SESSION['mensagem'] = 'Por favor, faça login para continuar.';
    $_SESSION['tipo_mensagem'] = 'aviso';
    header('Location: index.php?controler=home&acao=login');
    exit;
}

// Verificar se o utilizador está autenticado na página de login
if (isset($_SESSION['utilizador_id']) && $controler === 'HomeController' && in_array($acao, ['login'])) {
    header('Location: index.php');
    exit;
}

try {
    // Verificar se o controller existe
    if (!class_exists($controler)) {
        throw new Exception("Controller não encontrado: $controler");
    }
    
    // Instanciar o controller
    $controller = new $controler();
    
    // Verificar se o método existe
    if (!method_exists($controller, $acao)) {
        throw new Exception("Ação não encontrada: $acao");
    }
    
    // Determinar o caminho da view
    $view_file = APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    
    if ($controler === 'HomeController') {
        $view_file .= strtolower(str_replace('Controller', '', $controler)) . DIRECTORY_SEPARATOR . $acao . '.php';
    } else {
        $view_file .= strtolower(str_replace('Controller', '', $controler)) . DIRECTORY_SEPARATOR . $acao . '.php';
    }
    
    // Chamar a ação do controller
    if ($id !== null) {
        $controller->$acao($id);
    } else {
        $controller->$acao();
    }
    
    // Se existir ficheiro de view, renderizar
    if (isset($view_file) && file_exists($view_file)) {
        $view_path = $view_file;
        include APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main.php';
    }
    
} catch (Exception $e) {
    // Tratar erros
    if (APP_DEBUG) {
        echo "<div class='alert alert-danger'><strong>Erro:</strong> " . $e->getMessage() . "</div>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        $controlador = new HomeController();
        $controlador->erro($e->getMessage());
    }
}
