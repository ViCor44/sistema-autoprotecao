<?php
/**
 * Configuração Global da Aplicação
 * Sistema de Autoproteção
 */

// Configuração de Base de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistema_autoprotecao');
define('DB_CHARSET', 'utf8mb4');

// Configuração da Aplicação
define('APP_NAME', 'Sistema Autoproteção');
// APP_URL dinâmico para suportar múltiplos IPs/domínios sem perder assets.
$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$requestHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = trim(dirname(dirname($scriptName)), '/\\');
$appBaseUrl = $requestScheme . '://' . $requestHost;

if ($basePath !== '' && $basePath !== '.') {
	$appBaseUrl .= '/' . $basePath;
}

define('APP_URL', $appBaseUrl);
define('APP_ENV', 'development');
define('APP_DEBUG', true);

// Fuso Horário
date_default_timezone_set('Europe/Lisbon');

// Pasta base da aplicação
define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('CONFIG_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'config');
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
