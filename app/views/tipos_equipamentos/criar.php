<?php
$icones = [
    'bi-tools' => 'Ferramentas',
    'bi-shield-check' => 'Escudo',
    'bi-lightning-charge' => 'Eletricidade',
    'bi-droplet' => 'Água/Hidráulica',
    'bi-fire' => 'Fogo',
    'bi-door-closed' => 'Portas',
    'bi-sliders' => 'Controles',
    'bi-gear' => 'Engrenagens',
    'bi-box-seam' => 'Caixa',
    'bi-lamp' => 'Iluminação',
    'bi-badge-ad' => 'Sinalização',
    'bi-alarm' => 'Alarme'
];
?>

<section class="page-shell page-shell--narrow tipo-equipamento-criar-page">
    <header class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Novo Tipo de Equipamento</span>
            <h1><i class="bi bi-plus-circle"></i> Criar Tipo</h1>
            <p>Defina um novo tipo de equipamento para o seu sistema de autoproteção.</p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=tipo_equipamento&acao=listar" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Cancelar
            </a>
        </div>
    </header>

    <section class="panel-surface">
        <div class="panel-surface__header">
            <span class="panel-surface__eyebrow">Formulário</span>
            <h2>Informações do Tipo</h2>
        </div>

        <form method="POST" action="index.php?controler=tipo_equipamento&acao=salvar" class="modern-form">
            <div class="form-group">
                <label for="nome" class="form-label">Nome do Tipo <span class="required">*</span></label>
                <input type="text" id="nome" name="nome" class="form-control" 
                    placeholder="Ex.: Extintores, Sprinklers, Portas corta-fogo" required>
                <small class="form-text">Nome único que identifique este tipo de equipamento.</small>
            </div>

            <div class="form-group">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="4"
                    placeholder="Descrição detalhada do tipo de equipamento..."></textarea>
                <small class="form-text">Opcional. Descrição para ajudar a identificar o tipo.</small>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="icone" class="form-label">Ícone</label>
                    <select id="icone" name="icone" class="form-select">
                        <?php foreach ($icones as $valor => $label): ?>
                            <option value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Ícone para representar este tipo na interface.</small>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="prefixo_numeracao" class="form-label">Prefixo de Numeração</label>
                    <input type="text" id="prefixo_numeracao" name="prefixo_numeracao" class="form-control" 
                        placeholder="Ex.: EXT, SPRK, POR" maxlength="20">
                    <small class="form-text">Prefixo para numeração dos equipamentos (ex: EXT-001).</small>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="frequencia_inspecao" class="form-label">Frequência de Inspeção (dias)</label>
                    <input type="number" id="frequencia_inspecao" name="frequencia_inspecao" class="form-control" 
                        placeholder="Ex.: 365" min="1">
                    <small class="form-text">Intervalo em dias para inspeção periódica.</small>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Criar Tipo
                </button>
                <a href="index.php?controler=tipo_equipamento&acao=listar" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </section>
</section>

<style>
.required {
    color: #dc3545;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}
</style>
