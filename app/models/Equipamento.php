<?php
/**
 * Classe Equipamento
 * Modelo para gerenciar equipamentos
 */
class Equipamento {
    private $db;
    private $table = 'equipamentos';
    private $tableCampos = 'tipos_equipamentos_campos';
    private $tableValoresCampos = 'equipamentos_campos_valores';
    private $camposDinamicosDisponiveis = null;

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

    public function atualizarProximaManutencaoPorAgendamento($agendamentoId, $proximaInspecao) {
        $query = "UPDATE equipamentos e
                  JOIN calendarios_manutencao c ON c.equipamento_id = e.id
                  SET e.data_proxima_manutencao = ?
                  WHERE c.id = ? AND c.equipamento_id IS NOT NULL";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('si', $proximaInspecao, $agendamentoId);
        return $stmt->execute();
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
     * Obter campos dinâmicos ativos por tipo de equipamento
     */
    public function getCamposDinamicosPorTipo($tipoEquipamentoId) {
        if (!$this->isCamposDinamicosDisponiveis()) {
            return [];
        }

        $query = "SELECT id, tipo_equipamento_id, nome_campo, slug, tipo_dado, unidade, obrigatorio, ordem
                  FROM {$this->tableCampos}
                  WHERE tipo_equipamento_id = ? AND ativo = TRUE
                  ORDER BY ordem ASC, nome_campo ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $tipoEquipamentoId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obter valores dos campos dinâmicos por equipamento
     */
    public function getValoresCamposDinamicos($equipamentoId) {
        if (!$this->isCamposDinamicosDisponiveis()) {
            return [];
        }

        $query = "SELECT v.campo_id, v.valor
                  FROM {$this->tableValoresCampos} v
                  WHERE v.equipamento_id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $equipamentoId);
        $stmt->execute();

        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $mapa = [];

        foreach ($resultado as $linha) {
            $mapa[(int)$linha['campo_id']] = $linha['valor'];
        }

        return $mapa;
    }

    /**
     * Guardar valores dos campos dinâmicos do equipamento
     */
    public function salvarCamposDinamicos($equipamentoId, $valoresCampos) {
        if (!$this->isCamposDinamicosDisponiveis()) {
            return true;
        }

        if (empty($valoresCampos) || !is_array($valoresCampos)) {
            return true;
        }

        $query = "INSERT INTO {$this->tableValoresCampos} (equipamento_id, campo_id, valor)
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE valor = VALUES(valor), data_atualizacao = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($query);

        foreach ($valoresCampos as $campoId => $valor) {
            $campoId = (int)$campoId;
            $valorLimpo = is_string($valor) ? trim($valor) : $valor;

            if ($campoId <= 0) {
                continue;
            }

            if ($valorLimpo === '' || $valorLimpo === null) {
                $queryDelete = "DELETE FROM {$this->tableValoresCampos} WHERE equipamento_id = ? AND campo_id = ?";
                $stmtDelete = $this->db->prepare($queryDelete);
                $stmtDelete->bind_param("ii", $equipamentoId, $campoId);
                $stmtDelete->execute();
                continue;
            }

            $stmt->bind_param("iis", $equipamentoId, $campoId, $valorLimpo);
            if (!$stmt->execute()) {
                return false;
            }
        }

        return true;
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

    /**
     * Alias semântico para vistoria (mantém compatibilidade com código existente)
     */
    public function getEquipamentosComVistoriaPendente() {
        return $this->getEquipamentosComManutencaoPendente();
    }

    /**
     * Verifica se as tabelas de campos dinâmicos estão disponíveis
     */
    private function isCamposDinamicosDisponiveis() {
        if ($this->camposDinamicosDisponiveis !== null) {
            return $this->camposDinamicosDisponiveis;
        }

        $queryCampos = "SHOW TABLES LIKE '{$this->tableCampos}'";
        $queryValores = "SHOW TABLES LIKE '{$this->tableValoresCampos}'";

        $resCampos = $this->db->query($queryCampos);
        $resValores = $this->db->query($queryValores);

        $this->camposDinamicosDisponiveis = ($resCampos && $resCampos->num_rows > 0) && ($resValores && $resValores->num_rows > 0);
        return $this->camposDinamicosDisponiveis;
    }
}
