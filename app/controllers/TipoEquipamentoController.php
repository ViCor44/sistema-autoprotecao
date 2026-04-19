<?php
/**
 * Controller para Tipos de Equipamentos
 */
class TipoEquipamentoController extends Controller {
    private $tipoEquipamento;

    public function __construct() {
        $this->tipoEquipamento = new TipoEquipamento();
    }

    /**
     * Listar todos os tipos de equipamentos
     */
    public function listar() {
        $filtros = [];
        
        $busca = isset($_GET['busca']) ? trim((string)$_GET['busca']) : '';
        $mostrar = isset($_GET['mostrar']) ? trim((string)$_GET['mostrar']) : 'todos';

        if ($busca !== '') {
            $filtros['nome'] = $busca;
        }

        if ($mostrar === 'ativos') {
            $filtros['ativo'] = 1;
        } elseif ($mostrar === 'inativos') {
            $filtros['ativo'] = 0;
        }

        $porPagina = 20;
        $paginaAtual = max(1, (int)($_GET['pagina'] ?? 1));

        $totalResultados = $this->tipoEquipamento->getTotal($filtros);
        $totalPaginas = max(1, (int)ceil($totalResultados / $porPagina));

        if ($paginaAtual > $totalPaginas) {
            $paginaAtual = $totalPaginas;
        }

        $offset = ($paginaAtual - 1) * $porPagina;
        $tipos = $this->tipoEquipamento->getAll($filtros, $porPagina, $offset);

        // Adicionar contagem de equipamentos para cada tipo
        foreach ($tipos as &$tipo) {
            $tipo['total_equipamentos'] = $this->tipoEquipamento->getContagemEquipamentos((int)$tipo['id'], true);
        }

        $this->render('tipos_equipamentos/listar', compact(
            'tipos',
            'paginaAtual',
            'porPagina',
            'totalPaginas',
            'totalResultados',
            'busca',
            'mostrar'
        ));
    }

    /**
     * Ver detalhes de um tipo de equipamento
     */
    public function ver($id) {
        $tipo = $this->tipoEquipamento->getById($id);
        
        if (!$tipo) {
            $this->flash('Tipo de equipamento não encontrado.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        $totalEquipamentos = $this->tipoEquipamento->getContagemEquipamentos($id, true);
        $totalEquipamentosAssociados = $this->tipoEquipamento->getContagemEquipamentos($id, false);
        
        $this->render('tipos_equipamentos/ver', compact('tipo', 'totalEquipamentos', 'totalEquipamentosAssociados'));
    }

    /**
     * Formulário para criar tipo de equipamento
     */
    public function criar() {
        $this->render('tipos_equipamentos/criar');
    }

    /**
     * Salvar novo tipo de equipamento
     */
    public function salvar() {
        $this->requirePost('tipo_equipamento', 'listar');

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'icone' => $_POST['icone'] ?? 'bi-tools',
            'prefixo_numeracao' => $_POST['prefixo_numeracao'] ?? '',
            'frequencia_inspecao' => $_POST['frequencia_inspecao'] ?? null
        ];

        if (empty($dados['nome'])) {
            $this->flash('O nome do tipo é obrigatório.', 'erro');
            $this->redirect('tipo_equipamento', 'criar');
        }

        if ($this->tipoEquipamento->existeNome($dados['nome'])) {
            $this->flash('Já existe um tipo de equipamento com esse nome.', 'erro');
            $this->redirect('tipo_equipamento', 'criar');
        }

        $id = $this->tipoEquipamento->create($dados);

        if ($id) {
            $this->flash('Tipo de equipamento criado com sucesso!', 'sucesso');
            $this->redirect('tipo_equipamento', 'ver', ['id' => $id]);
        } else {
            $this->flash('Erro ao criar tipo de equipamento.', 'erro');
            $this->redirect('tipo_equipamento', 'criar');
        }
    }

    /**
     * Formulário para editar tipo de equipamento
     */
    public function editar($id) {
        $tipo = $this->tipoEquipamento->getById($id);
        
        if (!$tipo) {
            $this->flash('Tipo de equipamento não encontrado.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        $this->render('tipos_equipamentos/editar', compact('tipo'));
    }

    /**
     * Atualizar tipo de equipamento
     */
    public function atualizar($id) {
        $this->requirePost('tipo_equipamento', 'listar');

        $tipo = $this->tipoEquipamento->getById($id);
        
        if (!$tipo) {
            $this->flash('Tipo de equipamento não encontrado.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'icone' => $_POST['icone'] ?? 'bi-tools',
            'prefixo_numeracao' => $_POST['prefixo_numeracao'] ?? '',
            'frequencia_inspecao' => $_POST['frequencia_inspecao'] ?? null
        ];

        if (empty($dados['nome'])) {
            $this->flash('O nome do tipo é obrigatório.', 'erro');
            $this->redirect('tipo_equipamento', 'editar', ['id' => $id]);
        }

        if ($this->tipoEquipamento->existeNome($dados['nome'], $id)) {
            $this->flash('Já existe um tipo de equipamento com esse nome.', 'erro');
            $this->redirect('tipo_equipamento', 'editar', ['id' => $id]);
        }

        if ($this->tipoEquipamento->update($id, $dados)) {
            $this->flash('Tipo de equipamento atualizado com sucesso!', 'sucesso');
            $this->redirect('tipo_equipamento', 'ver', ['id' => $id]);
        } else {
            $this->flash('Erro ao atualizar tipo de equipamento.', 'erro');
            $this->redirect('tipo_equipamento', 'editar', ['id' => $id]);
        }
    }

    /**
     * Deletar tipo de equipamento
     */
    public function deletar($id) {
        $tipo = $this->tipoEquipamento->getById($id);
        
        if (!$tipo) {
            $this->flash('Tipo de equipamento não encontrado.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        if ($this->tipoEquipamento->delete($id)) {
            $this->flash('Tipo de equipamento deletado com sucesso!', 'sucesso');
            $this->redirect('tipo_equipamento', 'listar');
        } else {
            $this->flash('Este tipo de equipamento possui equipamentos associados e não pode ser deletado. Inative-o ou delete os equipamentos primeiro.', 'erro');
            $this->redirect('tipo_equipamento', 'ver', ['id' => $id]);
        }
    }

    /**
     * Alternar status ativo/inativo
     */
    public function toggleAtivo($id) {
        $tipo = $this->tipoEquipamento->getById($id);
        
        if (!$tipo) {
            $this->flash('Tipo de equipamento não encontrado.', 'erro');
            $this->redirect('tipo_equipamento', 'listar');
        }

        if ($this->tipoEquipamento->toggleAtivo($id)) {
            $status = $tipo['ativo'] ? 'inativado' : 'ativado';
            $this->flash("Tipo de equipamento {$status} com sucesso!", 'sucesso');
        } else {
            $this->flash('Erro ao alterar status.', 'erro');
        }

        $this->redirect('tipo_equipamento', 'ver', ['id' => $id]);
    }
}
