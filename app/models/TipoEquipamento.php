<?php
/**
 * Classe TipoEquipamento
 * Modelo para gerenciar tipos de equipamentos
 */
class TipoEquipamento {
    private $db;
    private $table = 'tipos_equipamentos';

    public $id;
    public $nome;
    public $descricao;
    public $icone;
    public $prefixo_numeracao;
    public $proximo_numero;
    public $frequencia_inspecao;
    public $ativo;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obter todos os tipos com filtros
     */
    public function getAll($filtros = [], $limite = null, $offset = 0) {
        $query = "SELECT * FROM {$this->table}";
        
        $query .= $this->buildWhereClause($filtros);
        $query .= " ORDER BY nome ASC";

        if ($limite !== null) {
            $limite = max(1, (int)$limite);
            $offset = max(0, (int)$offset);
            $query .= " LIMIT {$offset}, {$limite}";
        }

        $resultado = $this->db->query($query);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obter um tipo por ID
     */
    public function getById($id) {
        $id = (int)$id;
        $query = "SELECT * FROM {$this->table} WHERE id = {$id}";
        $resultado = $this->db->query($query);
        
        if ($resultado && $resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        return null;
    }

    /**
     * Contar total de registros com filtros
     */
    public function getTotal($filtros = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $query .= $this->buildWhereClause($filtros);
        
        $resultado = $this->db->query($query);
        $row = $resultado->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Criar novo tipo de equipamento
     */
    public function create($dados) {
        $nomeOriginal = trim((string)($dados['nome'] ?? ''));
        $nome = $this->db->escape($nomeOriginal);
        $descricao = $this->db->escape($dados['descricao'] ?? '');
        $icone = $this->db->escape($dados['icone'] ?? 'bi-tools');
        $prefixo = $this->db->escape($dados['prefixo_numeracao'] ?? '');
        $frequencia = isset($dados['frequencia_inspecao']) ? (int)$dados['frequencia_inspecao'] : null;

        if ($nomeOriginal === '') {
            return false;
        }

        if ($this->existeNome($nomeOriginal)) {
            return false;
        }

        $frequenciaVal = $frequencia !== null ? $frequencia : 'NULL';

        $query = "INSERT INTO {$this->table} (nome, descricao, icone, prefixo_numeracao, frequencia_inspecao, ativo)
                  VALUES ('{$nome}', '{$descricao}', '{$icone}', '{$prefixo}', {$frequenciaVal}, TRUE)";

        if ($this->db->query($query)) {
            return $this->db->getLastId();
        }
        return false;
    }

    /**
     * Atualizar tipo de equipamento
     */
    public function update($id, $dados) {
        $id = (int)$id;
        $nomeOriginal = trim((string)($dados['nome'] ?? ''));
        $nome = $this->db->escape($nomeOriginal);
        $descricao = $this->db->escape($dados['descricao'] ?? '');
        $icone = $this->db->escape($dados['icone'] ?? 'bi-tools');
        $prefixo = $this->db->escape($dados['prefixo_numeracao'] ?? '');
        $frequencia = isset($dados['frequencia_inspecao']) ? (int)$dados['frequencia_inspecao'] : null;

        if ($nomeOriginal === '') {
            return false;
        }

        if ($this->existeNome($nomeOriginal, $id)) {
            return false;
        }

        $frequenciaVal = $frequencia !== null ? $frequencia : 'NULL';

        $query = "UPDATE {$this->table} 
                  SET nome = '{$nome}', 
                      descricao = '{$descricao}', 
                      icone = '{$icone}', 
                      prefixo_numeracao = '{$prefixo}',
                      frequencia_inspecao = {$frequenciaVal}
                  WHERE id = {$id}";

        return $this->db->query($query);
    }

    /**
     * Deletar tipo de equipamento
     */
    public function delete($id) {
        $id = (int)$id;
        
        // Verificar se há equipamentos associados
        $query = "SELECT COUNT(*) as total FROM equipamentos WHERE tipo_equipamento_id = {$id}";
        $resultado = $this->db->query($query);
        $row = $resultado->fetch_assoc();
        
        if ((int)$row['total'] > 0) {
            return false; // Não permite deletar se houver equipamentos
        }

        $query = "DELETE FROM {$this->table} WHERE id = {$id}";
        return $this->db->query($query);
    }

    /**
     * Alternar status ativo/inativo
     */
    public function toggleAtivo($id) {
        $id = (int)$id;
        $query = "UPDATE {$this->table} SET ativo = NOT ativo WHERE id = {$id}";
        return $this->db->query($query);
    }

    /**
     * Obter quantidade de equipamentos por tipo
     */
    public function getContagemEquipamentos($id, $apenasAtivos = true) {
        $id = (int)$id;
        $query = "SELECT COUNT(*) as total FROM equipamentos WHERE tipo_equipamento_id = {$id}";
        if ($apenasAtivos) {
            $query .= " AND ativo = TRUE";
        }
        $resultado = $this->db->query($query);
        $row = $resultado->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Verificar se já existe tipo com o mesmo nome
     */
    public function existeNome($nome, $ignorarId = null) {
        $nomeEscapado = $this->db->escape(trim((string)$nome));
        $ignorarId = $ignorarId !== null ? (int)$ignorarId : null;

        $query = "SELECT id FROM {$this->table} WHERE LOWER(nome) = LOWER('{$nomeEscapado}')";
        if ($ignorarId !== null) {
            $query .= " AND id <> {$ignorarId}";
        }
        $query .= " LIMIT 1";

        $resultado = $this->db->query($query);
        return $resultado && $resultado->num_rows > 0;
    }

    /**
     * Construir cláusula WHERE a partir de filtros
     */
    private function buildWhereClause($filtros) {
        $condicoes = [];

        if (isset($filtros['ativo'])) {
            $ativo = (int)$filtros['ativo'];
            $condicoes[] = "ativo = {$ativo}";
        }

        if (isset($filtros['nome'])) {
            $nome = $this->db->escape($filtros['nome']);
            $condicoes[] = "nome LIKE '%{$nome}%'";
        }

        if (empty($condicoes)) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $condicoes);
    }
}
