<?php
/**
 * Classe Database
 * Gerencia a conexão com a base de dados
 */
class Database {
    private $conexao;
    private $host;
    private $port;
    private $user;
    private $password;
    private $dbname;
    private static $sharedConnection = null;

    public function __construct() {
        $config = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'database.php';
        $this->host = $config['host'];
        $this->port = $config['port'] ?? 3306;
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->dbname = $config['dbname'];
        $this->conectar();
    }

    /**
     * Estabelece conexão com a base de dados
     */
    private function conectar() {
        if (self::$sharedConnection instanceof mysqli && !self::$sharedConnection->connect_errno) {
            $this->conexao = self::$sharedConnection;
            return;
        }

        try {
            $connection = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->dbname,
                (int)$this->port
            );

            // Verificar erro de conexão
            if ($connection->connect_error) {
                throw new Exception('Erro na conexão: ' . $connection->connect_error);
            }

            // Configurar charset
            $connection->set_charset("utf8mb4");
            self::$sharedConnection = $connection;
            $this->conexao = $connection;
        } catch (Exception $e) {
            die('Erro de conexão com a base de dados: ' . $e->getMessage());
        }
    }

    /**
     * Retorna a conexão ativa
     */
    public function getConexao() {
        return $this->conexao;
    }

    /**
     * Executar query preparada
     */
    public function prepare($query) {
        return $this->conexao->prepare($query);
    }

    /**
     * Executar query simples
     */
    public function query($query) {
        return $this->conexao->query($query);
    }

    /**
     * Obter último ID inserido
     */
    public function getLastId() {
        return $this->conexao->insert_id;
    }

    /**
     * Obter número de linhas afetadas
     */
    public function getAffectedRows() {
        return $this->conexao->affected_rows;
    }

    /**
     * Escapar string para segurança
     */
    public function escape($string) {
        return $this->conexao->real_escape_string($string);
    }

    /**
     * Fechar conexão
     */
    public function close() {
        if ($this->conexao) {
            $this->conexao->close();
        }
    }

    /**
     * Iniciar transação
     */
    public function beginTransaction() {
        $this->conexao->begin_transaction();
    }

    /**
     * Confirmar transação
     */
    public function commit() {
        $this->conexao->commit();
    }

    /**
     * Desfazer transação
     */
    public function rollback() {
        $this->conexao->rollback();
    }
}
