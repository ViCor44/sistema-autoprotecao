<?php
/**
 * Classe Utilizador
 * Modelo para gerenciar utilizadores
 */
class Utilizador {
    private $db;
    private $table = 'utilizadores';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obter todos os utilizadores
     */
    public function getAll() {
        $query = "SELECT id, nome, email, telefone, funcao, ativo, data_criacao 
                  FROM {$this->table} WHERE ativo = TRUE ORDER BY nome ASC";
        
        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obter utilizador por ID
     */
    public function getById($id) {
        $query = "SELECT id, nome, email, telefone, funcao, ativo, data_criacao 
                  FROM {$this->table} WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obter utilizador por email
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Criar novo utilizador
     */
    public function create($dados) {
        $query = "INSERT INTO {$this->table} 
                  (nome, email, telefone, senha, funcao)
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        
        // Hash da senha
        $senhaHash = password_hash($dados['senha'], PASSWORD_BCRYPT);
        
        $stmt->bind_param(
            "sssss",
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $senhaHash,
            $dados['funcao']
        );

        if ($stmt->execute()) {
            return $this->db->getLastId();
        }
        return false;
    }

    /**
     * Atualizar utilizador
     */
    public function update($id, $dados) {
        $query = "UPDATE {$this->table} SET
                  nome = ?,
                  email = ?,
                  telefone = ?,
                  funcao = ?
                  WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "ssssi",
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['funcao'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Atualizar senha
     */
    public function atualizarSenha($id, $novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
        $query = "UPDATE {$this->table} SET senha = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $senhaHash, $id);
        
        return $stmt->execute();
    }

    /**
     * Verificar senha
     */
    public function verificarSenha($email, $senha) {
        $usuario = $this->getByEmail($email);
        
        if (!$usuario) {
            return false;
        }
        
        return password_verify($senha, $usuario['senha']);
    }

    /**
     * Inativar utilizador
     */
    public function inativar($id) {
        $query = "UPDATE {$this->table} SET ativo = FALSE WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Ativar utilizador
     */
    public function ativar($id) {
        $query = "UPDATE {$this->table} SET ativo = TRUE WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
