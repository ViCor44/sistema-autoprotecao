<?php
/**
 * Classe Equipamento
 * Modelo para gerenciar equipamentos
 */
class Equipamento {
    private $db;
    private $table = 'equipamentos';

    public $id;
    public $tipo_equipamento_id;
    public $numero_serie;
    public $localizacao;
    public $marca;
    public $modelo;
    public $data_aquisicao;
    public $data_instalacao;
    public $data_proxima_manutencao;
    public $estado;
    public $observacoes;
    public $ativo;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obter todos os equipamentos
     */
    public function getAll($filtros = []) {
        $query = "SELECT e.*, t.nome as tipo_nome 
                  FROM {$this->table} e
                  JOIN tipos_equipamentos t ON e.tipo_equipamento_id = t.id
                  WHERE 1=1";

        if (isset($filtros['ativo'])) {
            $query .= " AND e.ativo = " . (int)$filtros['ativo'];
        }

        if (isset($filtros['tipo_equipamento_id'])) {
            $query .= " AND e.tipo_equipamento_id = " . (int)$filtros['tipo_equipamento_id'];
        }

        if (isset($filtros['localizacao'])) {
            $query .= " AND e.localizacao LIKE '%" . $this->db->escape($filtros['localizacao']) . "%'";
        }

        $query .= " ORDER BY e.localizacao ASC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obter equipamento por ID
     */
    public function getById($id) {
        $query = "SELECT e.*, t.nome as tipo_nome 
                  FROM {$this->table} e
                  JOIN tipos_equipamentos t ON e.tipo_equipamento_id = t.id
                  WHERE e.id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Inserir novo equipamento
     */
    public function create($dados) {
        $query = "INSERT INTO {$this->table} 
                  (tipo_equipamento_id, numero_serie, localizacao, marca, modelo, 
                   data_aquisicao, data_instalacao, data_proxima_manutencao, estado, observacoes)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "isssssssss",
            $dados['tipo_equipamento_id'],
            $dados['numero_serie'],
            $dados['localizacao'],
            $dados['marca'],
            $dados['modelo'],
            $dados['data_aquisicao'],
            $dados['data_instalacao'],
            $dados['data_proxima_manutencao'],
            $dados['estado'],
            $dados['observacoes']
        );

        if ($stmt->execute()) {
            return $this->db->getLastId();
        }
        return false;
    }

    /**
     * Atualizar equipamento
     */
    public function update($id, $dados) {
        $query = "UPDATE {$this->table} SET
                  tipo_equipamento_id = ?,
                  numero_serie = ?,
                  localizacao = ?,
                  marca = ?,
                  modelo = ?,
                  data_aquisicao = ?,
                  data_instalacao = ?,
                  data_proxima_manutencao = ?,
                  estado = ?,
                  observacoes = ?
                  WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "isssssssssi",
            $dados['tipo_equipamento_id'],
            $dados['numero_serie'],
            $dados['localizacao'],
            $dados['marca'],
            $dados['modelo'],
            $dados['data_aquisicao'],
            $dados['data_instalacao'],
            $dados['data_proxima_manutencao'],
            $dados['estado'],
            $dados['observacoes'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Deletar (inativar) equipamento
     */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET ativo = FALSE WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Contar equipamentos que precisam de manutenção
     */
    public function getEquipamentosComManutencaoPendente() {
        $query = "SELECT e.*, t.nome as tipo_nome 
                  FROM {$this->table} e
                  JOIN tipos_equipamentos t ON e.tipo_equipamento_id = t.id
                  WHERE e.data_proxima_manutencao <= CURDATE()
                  AND e.ativo = TRUE
                  ORDER BY e.data_proxima_manutencao ASC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
