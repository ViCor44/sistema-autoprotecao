<?php
/**
 * Controller para Relatórios
 */
class RelatorioController {
    private $relatorio;
    private $equipamento;

    public function __construct() {
        $this->relatorio = new Relatorio();
        $this->equipamento = new Equipamento();
    }

    /**
     * Listar todos os relatórios
     */
    public function listar() {
        $filtros = [];
        
        if (isset($_GET['data_inicio'])) {
            $filtros['data_inicio'] = $_GET['data_inicio'];
        }
        if (isset($_GET['data_fim'])) {
            $filtros['data_fim'] = $_GET['data_fim'];
        }
        if (isset($_GET['tipo'])) {
            $filtros['tipo_relatorio'] = $_GET['tipo'];
        }
        
        $relatorios = $this->relatorio->getAll($filtros);
        
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'listar.php';
    }

    /**
     * Ver detalhes de um relatório
     */
    public function ver($id) {
        $relatorio = $this->relatorio->getById($id);
        
        if (!$relatorio) {
            $_SESSION['mensagem'] = 'Relatório não encontrado.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=relatorio&acao=listar');
            exit;
        }

        $itens = $this->relatorio->getItensRelatorio($id);
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'ver.php';
    }

    /**
     * Formulário para criar relatório
     */
    public function criar($equipamento_id = null) {
        $equipamentos = $this->equipamento->getAll();
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'criar.php';
    }

    /**
     * Salvar novo relatório
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=relatorio&acao=listar');
            exit;
        }

        $dados = [
            'calendario_id' => $_POST['calendario_id'] ?? 0,
            'tipo_equipamento_id' => $_POST['tipo_equipamento_id'] ?? 0,
            'equipamento_id' => $_POST['equipamento_id'] ?? 0,
            'data_relatorio' => $_POST['data_relatorio'] ?? date('Y-m-d'),
            'responsavel_id' => $_SESSION['utilizador_id'] ?? 0,
            'tipo_relatorio' => $_POST['tipo_relatorio'] ?? 'inspecao',
            'descricao' => $_POST['descricao'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'condicoes_encontradas' => $_POST['condicoes_encontradas'] ?? '',
            'proxima_inspecao' => $_POST['proxima_inspecao'] ?? null
        ];

        $relatorio_id = $this->relatorio->create($dados);

        if ($relatorio_id) {
            // Adicionar itens do relatório se fornecidos
            if (isset($_POST['itens_descricao']) && is_array($_POST['itens_descricao'])) {
                foreach ($_POST['itens_descricao'] as $index => $descricao) {
                    $this->relatorio->adicionarItem(
                        $relatorio_id,
                        $descricao,
                        $_POST['itens_resultado'][$index] ?? 'n_aplicavel',
                        $_POST['itens_observacao'][$index] ?? ''
                    );
                }
            }

            $_SESSION['mensagem'] = 'Relatório criado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
            header('Location: index.php?controler=relatorio&acao=ver&id=' . $relatorio_id);
        } else {
            $_SESSION['mensagem'] = 'Erro ao criar relatório.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=relatorio&acao=criar');
        }
        exit;
    }

    /**
     * Formulário para editar relatório
     */
    public function editar($id) {
        $relatorio = $this->relatorio->getById($id);
        if (!$relatorio || $relatorio['assinado']) {
            $_SESSION['mensagem'] = 'Relatório não encontrado ou já assinado.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=relatorio&acao=ver&id=' . $id);
            exit;
        }
        $equipamentos = $this->equipamento->getAll();
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'editar.php';
    }

    /**
     * Atualizar relatório
     */
    public function atualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controler=relatorio&acao=ver&id=' . $id);
            exit;
        }
        $dados = [
            'descricao' => $_POST['descricao'] ?? '',
            'observacoes' => $_POST['observacoes'] ?? '',
            'condicoes_encontradas' => $_POST['condicoes_encontradas'] ?? '',
            'proxima_inspecao' => $_POST['proxima_inspecao'] ?? null
        ];
        $this->relatorio->atualizar($id, $dados);
        $_SESSION['mensagem'] = 'Relatório atualizado com sucesso!';
        $_SESSION['tipo_mensagem'] = 'sucesso';
        header('Location: index.php?controler=relatorio&acao=ver&id=' . $id);
        exit;
    }

    /**
     * Assinar relatório
     */
    public function assinar($id) {
        if ($this->relatorio->assinar($id)) {
            $_SESSION['mensagem'] = 'Relatório assinado com sucesso!';
            $_SESSION['tipo_mensagem'] = 'sucesso';
        } else {
            $_SESSION['mensagem'] = 'Erro ao assinar relatório.';
            $_SESSION['tipo_mensagem'] = 'erro';
        }
        
        header('Location: index.php?controler=relatorio&acao=ver&id=' . $id);
        exit;
    }

    /**
     * Obter relatórios pendentes
     */
    public function pendentes() {
        $relatorios = $this->relatorio->getRelatoriosPendentesAssinatura();
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'pendentes.php';
    }

    /**
     * Exportar relatório para PDF
     */
    public function pdf($id) {
        $relatorio = $this->relatorio->getById($id);
        
        if (!$relatorio) {
            $_SESSION['mensagem'] = 'Relatório não encontrado.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php?controler=relatorio&acao=listar');
            exit;
        }

        $itens = $this->relatorio->getItensRelatorio($id);
        
        // Aqui você pode implementar a geração de PDF
        // Por enquanto, apenas verificamos se o relatório existe
        require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'pdf.php';
    }
}
