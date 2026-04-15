<?php
$totalEquipamentos = count($equipamentos);
$operacionais = 0;
$emFalha = 0;

foreach ($equipamentos as $equipamento) {
    if (($equipamento['estado'] ?? '') === 'operacional') {
        $operacionais++;
    } else {
        $emFalha++;
    }
}
?>

<section class="page-shell page-shell--narrow equipamentos-page">
    <header class="page-hero compact equipamentos-hero">
        <div>
            <span class="page-hero__eyebrow">Inventario Tecnico</span>
            <h1><i class="bi bi-tools"></i> Equipamentos</h1>
            <p>
                Consulte o estado operacional de cada equipamento, filtre por localizacao e mantenha o parque de autoprotecao sempre atualizado.
            </p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=equipamento&acao=criar" class="btn btn-dashboard-primary">
                <i class="bi bi-plus-circle"></i>
                Novo Equipamento
            </a>
        </div>
    </header>

    <section class="mini-stats-grid equipamentos-stats">
        <article class="mini-stat-card">
            <span>Total Registados</span>
            <strong><?php echo $totalEquipamentos; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Operacionais</span>
            <strong><?php echo $operacionais; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Com anomalias</span>
            <strong><?php echo $emFalha; ?></strong>
        </article>
    </section>

    <section class="panel-surface equipamentos-filter-panel">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Pesquisa</span>
                <h2>Filtrar equipamentos</h2>
            </div>
        </div>
        <form method="GET" action="index.php" class="modern-form equipamentos-filter-form">
            <input type="hidden" name="controler" value="equipamento">
            <input type="hidden" name="acao" value="listar">

            <div class="equipamentos-filter-grid">
                <input
                    type="text"
                    name="localizacao"
                    class="form-control"
                    placeholder="Ex.: Cozinha, Sala tecnica, Armazem..."
                    value="<?php echo htmlspecialchars($_GET['localizacao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="index.php?controler=equipamento&acao=listar" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </section>

    <?php if (empty($equipamentos)): ?>
        <section class="panel-surface">
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon">
                    <i class="bi bi-inboxes"></i>
                </div>
                <div>
                    <strong>Nenhum equipamento encontrado</strong>
                    <p>Registe o primeiro equipamento ou ajuste os filtros para visualizar resultados.</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="equipamentos-grid">
            <?php foreach ($equipamentos as $equip): ?>
                <?php
                $estado = $equip['estado'] ?? '';
                $estadoClass = $estado === 'operacional' ? 'status-pill--success' : 'status-pill--warning';
                ?>
                <article class="equipamento-card">
                    <div class="equipamento-card__head">
                        <h3><?php echo htmlspecialchars($equip['tipo_nome'] ?? 'Equipamento', ENT_QUOTES, 'UTF-8'); ?></h3>
                        <span class="status-pill <?php echo $estadoClass; ?>">
                            <?php echo htmlspecialchars(ucfirst($estado ?: 'indefinido'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <div class="equipamento-card__body">
                        <div class="detail-row">
                            <span>Localizacao</span>
                            <strong><?php echo htmlspecialchars($equip['localizacao'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Numero de registo</span>
                            <strong><?php echo htmlspecialchars($equip['numero_serie'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Marca e modelo</span>
                            <strong>
                                <?php echo htmlspecialchars($equip['marca'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> /
                                <?php echo htmlspecialchars($equip['modelo'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                            </strong>
                        </div>
                    </div>

                    <footer class="equipamento-card__actions">
                        <a href="index.php?controler=equipamento&acao=ver&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                        <a href="index.php?controler=equipamento&acao=editar&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="index.php?controler=equipamento&acao=deletar&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem a certeza?');">Deletar</a>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</section>
