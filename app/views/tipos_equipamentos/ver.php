<?php
$tipo = $tipo ?? [];
$nome = htmlspecialchars($tipo['nome'] ?? '', ENT_QUOTES, 'UTF-8');
$descricao = htmlspecialchars($tipo['descricao'] ?? '', ENT_QUOTES, 'UTF-8');
$icone = htmlspecialchars($tipo['icone'] ?? 'bi-tools', ENT_QUOTES, 'UTF-8');
$prefixo = htmlspecialchars($tipo['prefixo_numeracao'] ?? '', ENT_QUOTES, 'UTF-8');
$frequencia = (int)($tipo['frequencia_inspecao'] ?? 0);
$ativo = (bool)($tipo['ativo'] ?? true);
$totalEquipamentos = (int)($totalEquipamentos ?? 0);
?>

<section class="page-shell page-shell--narrow tipo-equipamento-ver-page">
    <header class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Tipo de Equipamento</span>
            <h1>
                <i class="bi <?php echo $icone; ?>"></i> 
                <?php echo $nome; ?>
            </h1>
            <?php if ($descricao): ?>
                <p><?php echo $descricao; ?></p>
            <?php endif; ?>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=tipo_equipamento&acao=editar&id=<?php echo (int)$tipo['id']; ?>" class="btn btn-dashboard-primary">
                <i class="bi bi-pencil"></i>
                Editar
            </a>
            <a href="index.php?controler=tipo_equipamento&acao=listar" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Voltar
            </a>
        </div>
    </header>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
        <section class="mini-stats-grid">
            <a
                href="index.php?controler=equipamento&acao=listar&tipo=<?php echo (int)$tipo['id']; ?>"
                style="text-decoration: none; color: inherit; display: block;"
                title="Ver equipamentos deste tipo"
            >
                <article class="mini-stat-card" style="cursor: pointer;">
                    <span>Total de Equipamentos</span>
                    <strong><?php echo $totalEquipamentos; ?></strong>
                </article>
            </a>
        </section>

        <section class="mini-stats-grid">
            <article class="mini-stat-card">
                <span>Status</span>
                <strong style="color: <?php echo $ativo ? '#28a745' : '#dc3545'; ?>;">
                    <?php echo $ativo ? 'Ativo' : 'Inativo'; ?>
                </strong>
            </article>
        </section>
    </div>

    <section class="panel-surface">
        <div class="panel-surface__header">
            <span class="panel-surface__eyebrow">Informações</span>
            <h2>Detalhes do Tipo</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 4px; color: #666;">Ícone</label>
                <div style="font-size: 32px; color: #0066cc;">
                    <i class="bi <?php echo $icone; ?>"></i>
                </div>
            </div>

            <?php if ($prefixo): ?>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 4px; color: #666;">Prefixo de Numeração</label>
                    <p style="margin: 0; font-size: 16px;"><?php echo $prefixo; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($frequencia > 0): ?>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 4px; color: #666;">Frequência de Inspeção</label>
                    <p style="margin: 0; font-size: 16px;"><?php echo $frequencia; ?> dias</p>
                </div>
            <?php endif; ?>

            <?php if ($tipo['data_criacao']): ?>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 4px; color: #666;">Criado em</label>
                    <p style="margin: 0; font-size: 14px; color: #666;">
                        <?php echo date('d/m/Y H:i', strtotime($tipo['data_criacao'])); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel-surface">
        <div class="panel-surface__header">
            <span class="panel-surface__eyebrow">Ações</span>
            <h2>Gerenciar Tipo</h2>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="index.php?controler=tipo_equipamento&acao=toggleAtivo&id=<?php echo (int)$tipo['id']; ?>" 
               class="btn <?php echo $ativo ? 'btn-warning' : 'btn-success'; ?>"
               onclick="return confirm('Tem a certeza que deseja <?php echo $ativo ? 'inativar' : 'ativar'; ?> este tipo?');">
                <i class="bi <?php echo $ativo ? 'bi-x-circle' : 'bi-check-circle'; ?>"></i>
                <?php echo $ativo ? 'Inativar' : 'Ativar'; ?>
            </a>

            <?php if ($totalEquipamentos === 0): ?>
                <a href="index.php?controler=tipo_equipamento&acao=deletar&id=<?php echo (int)$tipo['id']; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Tem a certeza que deseja deletar este tipo? Esta ação não pode ser desfeita.');">
                    <i class="bi bi-trash"></i>
                    Deletar
                </a>
            <?php else: ?>
                <button class="btn btn-danger" disabled title="Não pode deletar porque existem equipamentos associados">
                    <i class="bi bi-trash"></i>
                    Deletar
                </button>
            <?php endif; ?>
        </div>
    </section>
</section>
