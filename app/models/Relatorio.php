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
                  LEFT JOIN equipamentos e ON r.equipamento_id = e.id
                  LEFT JOIN tipos_equipamentos t ON t.id = COALESCE(r.tipo_equipamento_id, e.tipo_equipamento_id)
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
                  LEFT JOIN equipamentos e ON r.equipamento_id = e.id
                  LEFT JOIN tipos_equipamentos t ON t.id = COALESCE(r.tipo_equipamento_id, e.tipo_equipamento_id)
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
                  (calendario_id, tipo_equipamento_id, equipamento_id, data_relatorio, responsavel_id, tipo_relatorio,
                   descricao, observacoes, condicoes_encontradas, proxima_inspecao)
                  VALUES (NULLIF(?, 0), NULLIF(?, 0), NULLIF(?, 0), ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "iiisisssss",
            $dados['calendario_id'],
            $dados['tipo_equipamento_id'],
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
     * Obter relatório associado a uma inspeção
     */
    public function getByCalendarioId($calendarioId) {
        $query = "SELECT id FROM {$this->table} WHERE calendario_id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $calendarioId);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if (!$resultado) {
            return null;
        }

        return $this->getById((int)$resultado['id']);
    }

    /**
     * Criar relatório automaticamente a partir de uma inspeção
     */
    public function createFromInspecao($agendamento, $responsavelId) {
        if (empty($agendamento) || empty($agendamento['id'])) {
            return false;
        }

        $existente = $this->getByCalendarioId((int)$agendamento['id']);
        $dados = [
            'calendario_id' => (int)$agendamento['id'],
            'tipo_equipamento_id' => !empty($agendamento['tipo_equipamento_id']) ? (int)$agendamento['tipo_equipamento_id'] : 0,
            'equipamento_id' => !empty($agendamento['equipamento_id']) ? (int)$agendamento['equipamento_id'] : 0,
            'data_relatorio' => !empty($agendamento['data_realizacao']) ? date('Y-m-d', strtotime($agendamento['data_realizacao'])) : ($agendamento['data_inspecao'] ?? date('Y-m-d')),
            'responsavel_id' => (int)$responsavelId,
            'tipo_relatorio' => 'inspecao',
            'descricao' => $this->buildDescricaoFromInspecao($agendamento),
            'observacoes' => $this->buildObservacoesFromInspecao($agendamento),
            'condicoes_encontradas' => $agendamento['condicoes_encontradas'] ?? '',
            'proxima_inspecao' => $agendamento['proxima_inspecao'] ?? null
        ];

        if ($existente) {
            $this->atualizar((int)$existente['id'], [
                'descricao' => $dados['descricao'],
                'observacoes' => $dados['observacoes'],
                'condicoes_encontradas' => $dados['condicoes_encontradas'],
                'proxima_inspecao' => $dados['proxima_inspecao'],
            ]);
            return (int)$existente['id'];
        }

        return $this->create($dados);
    }

    /**
     * Atualizar relatório
     */
    public function atualizar($id, $dados) {
        $campos = [];
        $params = [];
        if (isset($dados['descricao'])) {
            $campos[] = "descricao = ?";
            $params[] = $dados['descricao'];
        }
        if (isset($dados['observacoes'])) {
            $campos[] = "observacoes = ?";
            $params[] = $dados['observacoes'];
        }
        if (isset($dados['condicoes_encontradas'])) {
            $campos[] = "condicoes_encontradas = ?";
            $params[] = $dados['condicoes_encontradas'];
        }
        if (isset($dados['proxima_inspecao'])) {
            $campos[] = "proxima_inspecao = ?";
            $params[] = $dados['proxima_inspecao'];
        }
        if (empty($campos)) return false;
        $params[] = $id;
        $query = "UPDATE {$this->table} SET ".implode(", ", $campos)." WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(str_repeat('s', count($params)-1).'i', ...$params);
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
                  LEFT JOIN equipamentos e ON r.equipamento_id = e.id
                  LEFT JOIN tipos_equipamentos t ON t.id = COALESCE(r.tipo_equipamento_id, e.tipo_equipamento_id)
                  JOIN utilizadores u ON r.responsavel_id = u.id
                  WHERE r.assinado = FALSE
                  ORDER BY r.data_criacao DESC";

        $resultado = $this->db->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    private function buildDescricaoFromInspecao($agendamento) {
        $partes = [];

        if (!empty($agendamento['parecer'])) {
            $partes[] = trim($agendamento['parecer']);
        }

        if (!empty($agendamento['descricao'])) {
            $partes[] = 'Plano da inspeção: ' . trim($agendamento['descricao']);
        }

        if (empty($partes)) {
            return 'Relatório gerado automaticamente a partir do registo da inspeção agendada.';
        }

        return implode("\n\n", $partes);
    }

    private function buildObservacoesFromInspecao($agendamento) {
        $partes = [];

        if (!empty($agendamento['equipamentos_avariados'])) {
            $partes[] = 'Equipamentos avariados: ' . trim($agendamento['equipamentos_avariados']);
        }

        if (!empty($agendamento['observacoes'])) {
            $partes[] = trim($agendamento['observacoes']);
        }

        return implode("\n\n", $partes);
    }
}
