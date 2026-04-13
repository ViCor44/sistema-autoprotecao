<?php
/**
 * Classe CalendarioManutencao
 * Modelo para gerenciar calendário de inspeções
 */
class CalendarioManutencao {
    private $db;
    private $table = 'calendarios_manutencao';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obter todos os agendamentos
     */
    public function getAll($filtros = []) {
        $query = "SELECT c.*, e.localizacao, e.numero_serie,
                        t.nome as tipo_equipamento, u.nome as responsavel_nome
                  FROM {$this->table} c
                  LEFT JOIN equipamentos e ON c.equipamento_id = e.id
                  JOIN tipos_equipamentos t ON t.id = c.tipo_equipamento_id
                  LEFT JOIN utilizadores u ON c.responsavel_id = u.id
                  WHERE 1=1";

        if (isset($filtros['tipo_equipamento_id'])) {
            $query .= " AND c.tipo_equipamento_id = " . (int)$filtros['tipo_equipamento_id'];
        }

        if (isset($filtros['status'])) {
            $query .= " AND c.status = '" . $this->db->escape($filtros['status']) . "'";
        }

        if (isset($filtros['data_inicio']) && isset($filtros['data_fim'])) {
            $query .= " AND DATE(c.data_inspecao) BETWEEN '" . $this->db->escape($filtros['data_inicio']) . "' AND '" . $this->db->escape($filtros['data_fim']) . "'";
        }

        if (isset($filtros['prioridade'])) {
            $query .= " AND c.prioridade = '" . $this->db->escape($filtros['prioridade']) . "'";
        }

        $query .= " ORDER BY c.data_inspecao ASC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obter agendamento por ID
     */
    public function getById($id) {
          $query = "SELECT c.*, e.localizacao, e.numero_serie, e.marca, e.modelo,
                    t.nome as tipo_equipamento, u.nome as responsavel_nome
                  FROM {$this->table} c
                LEFT JOIN equipamentos e ON c.equipamento_id = e.id
                JOIN tipos_equipamentos t ON t.id = c.tipo_equipamento_id
                  LEFT JOIN utilizadores u ON c.responsavel_id = u.id
                  WHERE c.id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Criar novo agendamento
     */
    public function create($dados) {
        $query = "INSERT INTO {$this->table}
                  (tipo_equipamento_id, equipamento_id, data_inspecao, tipo_inspecao, descricao, responsavel_id, status, prioridade)
                  VALUES (?, NULLIF(?, 0), ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "iisssiss",
            $dados['tipo_equipamento_id'],
            $dados['equipamento_id'],
            $dados['data_inspecao'],
            $dados['tipo_inspecao'],
            $dados['descricao'],
            $dados['responsavel_id'],
            $dados['status'],
            $dados['prioridade']
        );

        if ($stmt->execute()) {
            return $this->db->getLastId();
        }
        return false;
    }

    /**
     * Atualizar agendamento
     */
    public function update($id, $dados) {
        $query = "UPDATE {$this->table} SET
                  tipo_equipamento_id = ?,
                  equipamento_id = NULLIF(?, 0),
                  tipo_inspecao = ?,
                  descricao = ?,
                  responsavel_id = ?,
                  status = ?,
                  prioridade = ?
                  WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "iississi",
            $dados['tipo_equipamento_id'],
            $dados['equipamento_id'],
            $dados['tipo_inspecao'],
            $dados['descricao'],
            $dados['responsavel_id'],
            $dados['status'],
            $dados['prioridade'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Atualizar status do agendamento
     */
    public function updateStatus($id, $status) {
        $query = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Obter agendamentos próximos
     */
    public function getProximos($dias = 7) {
        $query = "SELECT c.*, e.localizacao, t.nome as tipo_equipamento, u.nome as responsavel_nome
                  FROM {$this->table} c
                  LEFT JOIN equipamentos e ON c.equipamento_id = e.id
                  JOIN tipos_equipamentos t ON t.id = c.tipo_equipamento_id
                  LEFT JOIN utilizadores u ON c.responsavel_id = u.id
                  WHERE c.data_inspecao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  AND c.status != 'cancelado'
                  ORDER BY c.data_inspecao ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $dias);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obter agendamentos vencidos
     */
    public function getVencidos() {
        $query = "SELECT c.*, e.localizacao, t.nome as tipo_equipamento, u.nome as responsavel_nome
                  FROM {$this->table} c
                  LEFT JOIN equipamentos e ON c.equipamento_id = e.id
                  JOIN tipos_equipamentos t ON t.id = c.tipo_equipamento_id
                  LEFT JOIN utilizadores u ON c.responsavel_id = u.id
                  WHERE c.data_inspecao < CURDATE()
                  AND c.status != 'concluido'
                  AND c.status != 'cancelado'
                  ORDER BY c.data_inspecao ASC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Deletar agendamento
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Atualizar dados da inspeção
     */
    public function atualizarInspecao($id, $dados) {
        $campos = [];
        $params = [];
        if (isset($dados['parecer'])) {
            $campos[] = "parecer = ?";
            $params[] = $dados['parecer'];
        }
        if (isset($dados['equipamentos_avariados'])) {
            $campos[] = "equipamentos_avariados = ?";
            $params[] = $dados['equipamentos_avariados'];
        }
        if (isset($dados['observacoes'])) {
            $campos[] = "observacoes = ?";
            $params[] = $dados['observacoes'];
        }
        if (empty($campos)) return false;
        $params[] = $id;
        $query = "UPDATE calendario_manutencao SET ".implode(", ", $campos)." WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(str_repeat('s', count($params)-1).'i', ...$params);
        return $stmt->execute();
    }
}
