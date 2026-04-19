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
    public $codigo_barras;
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
     * Obter equipamentos com filtros e suporte a paginação
     */
    public function getAll($filtros = [], $limite = null, $offset = 0, $ordenacao = []) {
        $query = "SELECT e.*, t.nome as tipo_nome
                  FROM {$this->table} e
                  JOIN tipos_equipamentos t ON e.tipo_equipamento_id = t.id";

        $query .= $this->buildWhereClause($filtros);
        $query .= $this->buildOrderByClause($ordenacao);

        if ($limite !== null) {
            $limite = max(1, (int)$limite);
            $offset = max(0, (int)$offset);
            $query .= " LIMIT {$offset}, {$limite}";
        }

        $resultado = $this->db->query($query);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Total de equipamentos para os filtros aplicados
     */
    public function getTotal($filtros = []) {
        $query = "SELECT COUNT(*) AS total FROM {$this->table} e";
        $query .= $this->buildWhereClause($filtros);

        $resultado = $this->db->query($query);
        $linha = $resultado ? $resultado->fetch_assoc() : null;

        return (int)($linha['total'] ?? 0);
    }

    /**
     * Resumo por estado para os filtros aplicados
     */
    public function getResumoEstados($filtros = []) {
        $query = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN e.estado = 'operacional' THEN 1 ELSE 0 END) AS operacionais,
                    SUM(CASE WHEN e.estado <> 'operacional' OR e.estado IS NULL THEN 1 ELSE 0 END) AS anomalias
                  FROM {$this->table} e";

        $query .= $this->buildWhereClause($filtros);

        $resultado = $this->db->query($query);
        $linha = $resultado ? $resultado->fetch_assoc() : [];

        return [
            'total' => (int)($linha['total'] ?? 0),
            'operacionais' => (int)($linha['operacionais'] ?? 0),
            'anomalias' => (int)($linha['anomalias'] ?? 0),
        ];
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
     * Gerar código de barras único para o equipamento
     */
    private function gerarCodigoBarras($tipoEquipamentoId) {
        // Obter prefixo do tipo de equipamento
        $query = "SELECT prefixo_numeracao FROM tipos_equipamentos WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $tipoEquipamentoId);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        
        $prefixo = $resultado['prefixo_numeracao'] ?? 'EQP';
        
        // Gerar código inicial
        $codigoBase = $prefixo . '-' . uniqid();
        
        // Garantir que o código é único
        $tentativas = 0;
        while ($tentativas < 5) {
            if (!$this->codigoBarrasExiste($codigoBase)) {
                return $codigoBase;
            }
            // Se já existe, gerar um novo com hash
            $codigoBase = $prefixo . '-' . substr(md5(uniqid()), 0, 12);
            $tentativas++;
        }
        
        // Fallback: usar timestamp + random
        return $prefixo . '-' . time() . rand(100, 999);
    }

    /**
     * Verificar se código de barras já existe
     */
    private function codigoBarrasExiste($codigoBarras) {
        $query = "SELECT id FROM {$this->table} WHERE codigo_barras = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $codigoBarras);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->num_rows > 0;
    }

    /**
     * Verificar e limpar numero_serie duplicado de equipamentos inativos
     */
    private function limparNumeroSerieInativo($numeroSerie) {
        if (empty($numeroSerie)) {
            return;
        }

        // Buscar equipamentos inativos com o mesmo numero_serie
        $query = "UPDATE {$this->table} 
                  SET numero_serie = NULL 
                  WHERE numero_serie = ? AND ativo = FALSE 
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $numeroSerie);
        return $stmt->execute();
    }

    /**
     * Verificar e limpar codigo_barras duplicado de equipamentos inativos
     */
    private function limparCodigoBarrasInativo($codigoBarras) {
        if (empty($codigoBarras)) {
            return;
        }

        // Buscar equipamentos inativos com o mesmo codigo_barras
        $query = "UPDATE {$this->table} 
                  SET codigo_barras = NULL 
                  WHERE codigo_barras = ? AND ativo = FALSE 
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $codigoBarras);
        return $stmt->execute();
    }

    /**
     * Inserir novo equipamento
     */
    public function create($dados) {
        // Se numero_serie foi preenchido, limpar duplicatas inativas
        if (!empty($dados['numero_serie'])) {
            $this->limparNumeroSerieInativo($dados['numero_serie']);
        }

        // Gerar código de barras
        $codigoBarras = $this->gerarCodigoBarras($dados['tipo_equipamento_id']);
        
        $query = "INSERT INTO {$this->table} 
                  (tipo_equipamento_id, numero_serie, codigo_barras, localizacao, marca, modelo, 
                   data_aquisicao, data_instalacao, data_proxima_manutencao, estado, observacoes)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "issssssssss",
            $dados['tipo_equipamento_id'],
            $dados['numero_serie'],
            $codigoBarras,
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
        // Se numero_serie foi preenchido, limpar duplicatas inativas
        if (!empty($dados['numero_serie'])) {
            // Mas não limpar se for o mesmo equipamento
            $equipamentoAtual = $this->getById($id);
            if ($equipamentoAtual && $equipamentoAtual['numero_serie'] !== $dados['numero_serie']) {
                $this->limparNumeroSerieInativo($dados['numero_serie']);
            }
        }

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

    /**
     * Construir cláusula WHERE dos filtros
     */
    private function buildWhereClause($filtros = []) {
        $where = " WHERE 1=1";

        if (isset($filtros['ativo'])) {
            $where .= " AND e.ativo = " . (int)$filtros['ativo'];
        }

        if (!empty($filtros['tipo_equipamento_id'])) {
            $where .= " AND e.tipo_equipamento_id = " . (int)$filtros['tipo_equipamento_id'];
        }

        if (!empty($filtros['estado'])) {
            $estado = $this->db->escape($filtros['estado']);
            $where .= " AND e.estado = '{$estado}'";
        }

        if (!empty($filtros['localizacao'])) {
            $texto = $this->db->escape($filtros['localizacao']);
            $where .= " AND (
                e.localizacao LIKE '%{$texto}%'
                OR e.numero_serie LIKE '%{$texto}%'
                OR e.marca LIKE '%{$texto}%'
                OR e.modelo LIKE '%{$texto}%'
            )";
        }

        return $where;
    }

    /**
     * Construir cláusula ORDER BY segura
     */
    private function buildOrderByClause($ordenacao = []) {
        $campo = $ordenacao['campo'] ?? 'tipo_nome';
        $direcao = strtoupper($ordenacao['direcao'] ?? 'ASC');
        $direcao = $direcao === 'DESC' ? 'DESC' : 'ASC';

        $mapaCampos = [
            'tipo_nome' => 't.nome',
            'localizacao' => 'e.localizacao',
            'estado' => 'e.estado',
            'proxima_manutencao' => 'e.data_proxima_manutencao',
        ];

        $coluna = $mapaCampos[$campo] ?? $mapaCampos['tipo_nome'];

        if ($campo === 'proxima_manutencao') {
            return " ORDER BY
                CASE
                    WHEN e.data_proxima_manutencao IS NULL OR e.data_proxima_manutencao = '0000-00-00' THEN 1
                    ELSE 0
                END ASC,
                {$coluna} {$direcao},
                t.nome ASC,
                e.localizacao ASC";
        }

        return " ORDER BY {$coluna} {$direcao}, t.nome ASC, e.localizacao ASC, e.numero_serie ASC";
    }
}
