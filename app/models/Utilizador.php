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
    public function getAll($filtros = []) {
        $query = "SELECT u.id, u.nome, u.email, u.telefone, u.funcao, u.ativo, u.aprovado,
                         u.data_aprovacao, u.data_criacao, aprovador.nome as aprovado_por_nome
                  FROM {$this->table} u
                  LEFT JOIN {$this->table} aprovador ON aprovador.id = u.aprovado_por
                  WHERE 1=1";

        if (empty($filtros['incluir_todos'])) {
            $query .= " AND u.ativo = TRUE AND u.aprovado = TRUE";
        }

        if (array_key_exists('ativo', $filtros)) {
            $query .= " AND u.ativo = " . ((int)!empty($filtros['ativo']));
        }

        if (array_key_exists('aprovado', $filtros)) {
            $query .= " AND u.aprovado = " . ((int)!empty($filtros['aprovado']));
        }

        $query .= " ORDER BY u.nome ASC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function getPendentesAprovacao() {
        return $this->getAll([
            'incluir_todos' => true,
            'ativo' => false,
            'aprovado' => false,
        ]);
    }

    /**
     * Obter utilizador por ID
     */
    public function getById($id) {
        $query = "SELECT u.id, u.nome, u.email, u.telefone, u.funcao, u.ativo, u.aprovado,
                         u.aprovado_por, u.data_aprovacao, u.data_criacao, aprovador.nome as aprovado_por_nome
                  FROM {$this->table} u
                  LEFT JOIN {$this->table} aprovador ON aprovador.id = u.aprovado_por
                  WHERE u.id = ?";
        
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
                  (nome, email, telefone, senha, funcao, ativo, aprovado, aprovado_por, data_aprovacao)
                  VALUES (?, ?, ?, ?, ?, ?, ?, NULLIF(?, 0), NULLIF(?, ''))";

        $stmt = $this->db->prepare($query);
        
        // Hash da senha
        $senhaHash = password_hash($dados['senha'], PASSWORD_BCRYPT);
        $funcao = $dados['funcao'] ?? 'tecnico';
        $ativo = !empty($dados['ativo']) ? 1 : 0;
        $aprovado = !empty($dados['aprovado']) ? 1 : 0;
        $aprovadoPor = (int)($dados['aprovado_por'] ?? 0);
        $dataAprovacao = !empty($dados['data_aprovacao']) ? $dados['data_aprovacao'] : '';
        
        $stmt->bind_param(
            "sssssiiis",
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $senhaHash,
            $funcao,
            $ativo,
            $aprovado,
            $aprovadoPor,
            $dataAprovacao
        );

        if ($stmt->execute()) {
            return $this->db->getLastId();
        }
        return false;
    }

    public function criarRegistoPendente($dados) {
        $dados['funcao'] = 'tecnico';
        $dados['ativo'] = false;
        $dados['aprovado'] = false;
        $dados['aprovado_por'] = null;
        $dados['data_aprovacao'] = null;
        return $this->create($dados);
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
     * Verificar se um email já existe
     */
    public function emailExiste($email, $excluirId = null) {
        $query = "SELECT id FROM {$this->table} WHERE email = ?";
        if ($excluirId !== null) {
            $query .= " AND id != ?";
        }

        $stmt = $this->db->prepare($query);
        if ($excluirId !== null) {
            $stmt->bind_param("si", $email, $excluirId);
        } else {
            $stmt->bind_param("s", $email);
        }
        $stmt->execute();

        return (bool)$stmt->get_result()->fetch_assoc();
    }

    /**
     * Validar credenciais de autenticação
     */
    public function autenticar($email, $senha) {
        $usuario = $this->getByEmail($email);
        
        if (!$usuario) {
            return ['ok' => false, 'motivo' => 'credenciais'];
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return ['ok' => false, 'motivo' => 'credenciais'];
        }

        if (empty($usuario['aprovado'])) {
            return ['ok' => false, 'motivo' => 'pendente'];
        }

        if (empty($usuario['ativo'])) {
            return ['ok' => false, 'motivo' => 'inativo'];
        }

        return ['ok' => true, 'utilizador' => $usuario];
    }

    /**
     * Verificar senha
     */
    public function verificarSenha($email, $senha) {
        $resultado = $this->autenticar($email, $senha);
        return !empty($resultado['ok']);
    }

    public function aprovar($id, $adminId, $funcao = 'tecnico') {
        $query = "UPDATE {$this->table}
                  SET ativo = TRUE,
                      aprovado = TRUE,
                      funcao = ?,
                      aprovado_por = ?,
                      data_aprovacao = NOW()
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sii", $funcao, $adminId, $id);
        return $stmt->execute();
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
