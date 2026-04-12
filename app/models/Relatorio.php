<?php
/**
 * Classe Relatorio
 * Modelo para gerenciar relatórios de inspeção
 */
class Relatorio {
    private $db;
    private $table = 'relatorios';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obter todos os relatórios com filtros
     */
    public function getAll($filtros = []) {
        $query = "SELECT r.*, e.localizacao, e.numero_serie, 
                        t.nome as tipo_equipamento, u.nome as responsavel_nome
                  FROM {$this->table} r
                  JOIN equipamentos e ON r.equipamento_id = e.id
                  JOIN tipos_equipamentos t ON e.tipo_equipamento_id = t.id
                  JOIN utilizadores u ON r.responsavel_id = u.id
                  WHERE 1=1";

        if (isset($filtros['data_inicio']) && isset($filtros['data_fim'])) {
            $query .= " AND DATE(r.data_relatorio) BETWEEN '" . $this->db->escape($filtros['data_inicio']) . "' AND '" . $this->db->escape($filtros['data_fim']) . "'";
        }

        if (isset($filtros['tipo_relatorio'])) {
            $query .= " AND r.tipo_relatorio = '" . $this->db->escape($filtros['tipo_relatorio']) . "'";
        }

        if (isset($filtros['equipamento_id'])) {
            $query .= " AND r.equipamento_id = " . (int)$filtros['equipamento_id'];
        }

        $query .= " ORDER BY r.data_relatorio DESC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obter relatório por ID
     */
    public function getById($id) {
        $query = "SELECT r.*, e.localizacao, e.numero_serie, e.marca, e.modelo,
                        t.nome as tipo_equipamento, u.nome as responsavel_nome
                  FROM {$this->table} r
                  JOIN equipamentos e ON r.equipamento_id = e.id
                  JOIN tipos_equipamentos t ON e.tipo_equipamento_id = t.id
                  JOIN utilizadores u ON r.responsavel_id = u.id
                  WHERE r.id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Criar novo relatório
     */
    public function create($dados) {
        $query = "INSERT INTO {$this->table}
                  (equipamento_id, data_relatorio, responsavel_id, tipo_relatorio, 
                   descricao, observacoes, condicoes_encontradas, proxima_inspecao)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "isssssss",
            $dados['equipamento_id'],
            $dados['data_relatorio'],
            $dados['responsavel_id'],
            $dados['tipo_relatorio'],
            $dados['descricao'],
            $dados['observacoes'],
            $dados['condicoes_encontradas'],
            $dados['proxima_inspecao']
        );

        if ($stmt->execute()) {
            return $this->db->getLastId();
        }
        return false;
    }

    /**
     * Atualizar relatório
     */
    public function update($id, $dados) {
        $query = "UPDATE {$this->table} SET
                  tipo_relatorio = ?,
                  descricao = ?,
                  observacoes = ?,
                  condicoes_encontradas = ?,
                  proxima_inspecao = ?
                  WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "ssssi",
            $dados['tipo_relatorio'],
            $dados['descricao'],
            $dados['observacoes'],
            $dados['condicoes_encontradas'],
            $dados['proxima_inspecao'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Assinar relatório
     */
    public function assinar($id) {
        $query = "UPDATE {$this->table} SET assinado = TRUE, data_assinatura = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Obter itens do relatório
     */
    public function getItensRelatorio($relatorio_id) {
        $query = "SELECT * FROM itens_relatorio WHERE relatorio_id = ? ORDER BY id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $relatorio_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Inserir item do relatório
     */
    public function adicionarItem($relatorio_id, $descricao, $resultado, $observacao = '') {
        $query = "INSERT INTO itens_relatorio (relatorio_id, descricao_verificacao, resultado, observacao)
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("isss", $relatorio_id, $descricao, $resultado, $observacao);
        return $stmt->execute();
    }

    /**
     * Obter relatórios pendentes de assinatura
     */
    public function getRelatoriosPendentesAssinatura() {
        $query = "SELECT r.*, e.localizacao, t.nome as tipo_equipamento, u.nome as responsavel_nome
                  FROM {$this->table} r
                  JOIN equipamentos e ON r.equipamento_id = e.id
                  JOIN tipos_equipamentos t ON e.tipo_equipamento_id = t.id
                  JOIN utilizadores u ON r.responsavel_id = u.id
                  WHERE r.assinado = FALSE
                  ORDER BY r.data_criacao DESC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
