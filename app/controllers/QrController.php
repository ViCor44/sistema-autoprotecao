<?php
/**
 * Controlador para visualização rápida de equipamentos via QR
 */
class QrController extends Controller {
    private $equipamento;

    public function __construct() {
        $this->equipamento = new Equipamento();
    }

    /**
     * Visualizar equipamento via QR (página pública)
     */
    public function visualizar($id = null) {
        // Obter ID da URL se não fornecido como parâmetro
        if ($id === null) {
            $id = $_GET['id'] ?? null;
        }

        if (empty($id)) {
            $this->flash('ID do equipamento não fornecido.', 'erro');
            $this->redirect('home', 'index');
        }

        $equipamento = $this->equipamento->getById((int)$id);

        if (!$equipamento) {
            $this->flash('Equipamento não encontrado.', 'erro');
            $this->redirect('home', 'index');
        }

        $camposDinamicos = $this->equipamento->getCamposDinamicosPorTipo((int)$equipamento['tipo_equipamento_id']);
        $valoresCamposDinamicos = $this->equipamento->getValoresCamposDinamicos((int)$equipamento['id']);

        // Render uma página simplificada para mobile
        $this->render('qr/visualizar', compact('equipamento', 'camposDinamicos', 'valoresCamposDinamicos'));
    }
}
