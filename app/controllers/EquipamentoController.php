<?php
/**
 * Controller para Equipamentos
 */
class EquipamentoController {
    private $equipamento;
    private $tiposEquipamentos = [];

    public function __construct() {
        $this->equipamento = new Equipamento();
        $this->carregarTiposEquipamentos();
    }

    /**
     * Carregar tipos de equipamentos da base de dados
     */
    private function carregarTiposEquipamentos() {
        $db = new Database();
        $resultado = $db->query("SELECT id, nome FROM tipos_equipamentos WHERE ativo = TRUE ORDER BY nome ASC");
        $this->tiposEquipamentos = $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Listar todos os equipamentos
     */
    public function listar() {
        $filtros = [];
        
        if (isset($_GET['tipo'])) {
            $filtros['tipo_equipamento_id'] = (int)$_GET['tipo'];
        }
        if (isset($_GET['localizacao'])) {
            $filtros['localizacao'] = $_GET['localizacao'];
        }
        
        $equipamentos = $this->equipamento->getAll($filtros);
        
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'equipamentos' . DIRECTORY_SEPARATOR . 'listar.php';
    }

    /**
     * Ver detalhes de um equipamento
     */
    public function ver($id) {
        $equipamento = $this->equipamento->getById($id);
        
        if (!$equipamento) {
            $_SESSION['mensagem'] = 'Equipamento não encontrado.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=equipamento&acao=listar');
            exit;
        }

        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'equipamentos' . DIRECTORY_SEPARATOR . 'ver.php';
    }

    /**
     * Formulário para criar equipamento
     */
    public function criar() {
        $tipos = $this->tiposEquipamentos;
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'equipamentos' . DIRECTORY_SEPARATOR . 'criar.php';
    }

    /**
     * Salvar novo equipamento
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=equipamento&acao=listar');
            exit;
        }

        $dados = [
            'tipo_equipamento_id' => $_POST['tipo_equipamento_id'] ?? 0,
            'numero_serie' => $_POST['numero_serie'] ?? '',
            'localizacao' => $_POST['localizacao'] ?? '',
            'marca' => $_POST['marca'] ?? '',
            'modelo' => $_POST['modelo'] ?? '',
            'data_aquisicao' => $_POST['data_aquisicao'] ?? null,
            'data_instalacao' => $_POST['data_instalacao'] ?? null,
            'data_proxima_manutencao' => $_POST['data_proxima_manutencao'] ?? null,
            'estado' => $_POST['estado'] ?? 'operacional',
            'observacoes' => $_POST['observacoes'] ?? ''
        ];

        if ($this->equipamento->create($dados)) {
            $_SESSION['mensagem'] = 'Equipamento criado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
            header('Location: index.php?controler=equipamento&acao=listar');
        } else {
            $_SESSION['mensagem'] = 'Erro ao criar equipamento.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=equipamento&acao=criar');
        }
        exit;
    }

    /**
     * Formulário para editar equipamento
     */
    public function editar($id) {
        $equipamento = $this->equipamento->getById($id);
        
        if (!$equipamento) {
            $_SESSION['mensagem'] = 'Equipamento não encontrado.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=equipamento&acao=listar');
            exit;
        }

        $tipos = $this->tiposEquipamentos;
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'equipamentos' . DIRECTORY_SEPARATOR . 'editar.php';
    }

    /**
     * Atualizar equipamento
     */
    public function atualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=equipamento&acao=listar');
            exit;
        }

        $dados = [
            'tipo_equipamento_id' => $_POST['tipo_equipamento_id'] ?? 0,
            'numero_serie' => $_POST['numero_serie'] ?? '',
            'localizacao' => $_POST['localizacao'] ?? '',
            'marca' => $_POST['marca'] ?? '',
            'modelo' => $_POST['modelo'] ?? '',
            'data_aquisicao' => $_POST['data_aquisicao'] ?? null,
            'data_instalacao' => $_POST['data_instalacao'] ?? null,
            'data_proxima_manutencao' => $_POST['data_proxima_manutencao'] ?? null,
            'estado' => $_POST['estado'] ?? 'operacional',
            'observacoes' => $_POST['observacoes'] ?? ''
        ];

        if ($this->equipamento->update($id, $dados)) {
            $_SESSION['mensagem'] = 'Equipamento atualizado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
            header('Location: index.php?controler=equipamento&acao=ver&id=' . $id);
        } else {
            $_SESSION['mensagem'] = 'Erro ao atualizar equipamento.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=equipamento&acao=editar&id=' . $id);
        }
        exit;
    }

    /**
     * Deletar equipamento
     */
    public function deletar($id) {
        if ($this->equipamento->delete($id)) {
            $_SESSION['mensagem'] = 'Equipamento removido com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
        } else {
            $_SESSION['mensagem'] = 'Erro ao remover equipamento.';
            $_SESSION['tipo_mensagem'] = 'erro';
        }
        
        header('Location: index.php?controler=equipamento&acao=listar');
        exit;
    }
}
