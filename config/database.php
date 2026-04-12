<?php
/**
 * Configuração da Base de Dados
 */

return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'password' => '',
    'dbname' => 'sistema_autoprotecao',
    'charset' => 'utf8mb4',
];
            echo 'Connection error: ' . $exception->getMessage();
        }
        return $this->conn;
    }
}
