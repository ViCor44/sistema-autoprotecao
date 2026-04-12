<?php
/**
 * Classe Database
 * Gerencia a conexão com a base de dados
 */
class Database {
    private $conexao;
    private $host;
    private $user;
    private $password;
    private $dbname;

    public function __construct() {
        $config = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'database.php';
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->dbname = $config['dbname'];
        $this->conectar();
    }

    /**
     * Estabelece conexão com a base de dados
     */
    private function conectar() {
        try {
            $this->conexao = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->dbname
            );

            // Verificar erro de conexão
            if ($this->conexao->connect_error) {
                throw new Exception('Erro na conexão: ' . $this->conexao->connect_error);
            }

            // Configurar charset
            $this->conexao->set_charset("utf8mb4");
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
