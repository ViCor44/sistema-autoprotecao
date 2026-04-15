<?php
/**
 * Controller para Equipamentos
 */
class EquipamentoController extends Controller {
    private $equipamento;
    private $tiposEquipamentos = [];
    private $camposDinamicosPorTipo = [];

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

        foreach ($this->tiposEquipamentos as $tipo) {
            $this->camposDinamicosPorTipo[(int)$tipo['id']] = $this->equipamento->getCamposDinamicosPorTipo((int)$tipo['id']);
        }
    }

    /**
     * Listar todos os equipamentos
     */
    public function listar() {
        $filtros = ['ativo' => 1];

        $tipo = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;
        $estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';
        $localizacao = isset($_GET['localizacao']) ? trim((string)$_GET['localizacao']) : '';

        if ($tipo > 0) {
            $filtros['tipo_equipamento_id'] = $tipo;
        }

        if ($estado !== '') {
            $filtros['estado'] = $estado;
        }

        if ($localizacao !== '') {
            $filtros['localizacao'] = $localizacao;
        }

        $porPagina = 24;
        $paginaAtual = max(1, (int)($_GET['pagina'] ?? 1));

        $totalResultados = $this->equipamento->getTotal($filtros);
        $totalPaginas = max(1, (int)ceil($totalResultados / $porPagina));

        if ($paginaAtual > $totalPaginas) {
            $paginaAtual = $totalPaginas;
        }

        $offset = ($paginaAtual - 1) * $porPagina;

        $equipamentos = $this->equipamento->getAll($filtros, $porPagina, $offset);
        $resumo = $this->equipamento->getResumoEstados($filtros);
        $tipos = $this->tiposEquipamentos;

        $this->render('equipamentos/listar', compact(
            'equipamentos',
            'tipos',
            'filtros',
            'resumo',
            'paginaAtual',
            'porPagina',
            'totalPaginas',
            'totalResultados',
            'offset'
        ));
    }

    /**
     * Ver detalhes de um equipamento
     */
    public function ver($id) {
        $equipamento = $this->equipamento->getById($id);
        
        if (!$equipamento) {
            $this->flash('Equipamento não encontrado.', 'erro');
            $this->redirect('equipamento', 'listar');
        }

        $camposDinamicos = $this->equipamento->getCamposDinamicosPorTipo((int)$equipamento['tipo_equipamento_id']);
        $valoresCamposDinamicos = $this->equipamento->getValoresCamposDinamicos((int)$equipamento['id']);

        $this->render('equipamentos/ver', compact('equipamento', 'camposDinamicos', 'valoresCamposDinamicos'));
    }

    /**
     * Formulário para criar equipamento
     */
    public function criar() {
        $tipos = $this->tiposEquipamentos;
        $camposDinamicosPorTipo = $this->camposDinamicosPorTipo;
        $this->render('equipamentos/criar', compact('tipos', 'camposDinamicosPorTipo'));
    }

    /**
     * Salvar novo equipamento
     */
    public function salvar() {
        $this->requirePost('equipamento', 'listar');

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

        $equipamentoId = $this->equipamento->create($dados);

        if ($equipamentoId) {
            $camposDinamicos = $_POST['campos_dinamicos'] ?? [];
            $this->equipamento->salvarCamposDinamicos((int)$equipamentoId, $camposDinamicos);

            $this->flash('Equipamento criado com sucesso!', 'sucesso');
            $this->redirect('equipamento', 'listar');
        } else {
            $this->flash('Erro ao criar equipamento.', 'erro');
            $this->redirect('equipamento', 'criar');
        }
    }

    /**
     * Formulário para editar equipamento
     */
    public function editar($id) {
        $equipamento = $this->equipamento->getById($id);
        
        if (!$equipamento) {
            $this->flash('Equipamento não encontrado.', 'erro');
            $this->redirect('equipamento', 'listar');
        }

        $tipos = $this->tiposEquipamentos;
        $camposDinamicosPorTipo = $this->camposDinamicosPorTipo;
        $valoresCamposDinamicos = $this->equipamento->getValoresCamposDinamicos((int)$id);
        $this->render('equipamentos/editar', compact('equipamento', 'tipos', 'camposDinamicosPorTipo', 'valoresCamposDinamicos'));
    }

    /**
     * Atualizar equipamento
     */
    public function atualizar($id) {
        $this->requirePost('equipamento', 'listar');

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
            $camposDinamicos = $_POST['campos_dinamicos'] ?? [];
            $this->equipamento->salvarCamposDinamicos((int)$id, $camposDinamicos);

            $this->flash('Equipamento atualizado com sucesso!', 'sucesso');
            $this->redirect('equipamento', 'ver', ['id' => $id]);
        } else {
            $this->flash('Erro ao atualizar equipamento.', 'erro');
            $this->redirect('equipamento', 'editar', ['id' => $id]);
        }
    }

    /**
     * Deletar equipamento
     */
    public function deletar($id) {
        if ($this->equipamento->delete($id)) {
            $this->flash('Equipamento removido com sucesso!', 'sucesso');
        } else {
            $this->flash('Erro ao remover equipamento.', 'erro');
        }

        $this->redirect('equipamento', 'listar');
    }
}
