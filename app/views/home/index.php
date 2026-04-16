<?php
$totalProximas = count($proximasManutencoes);
$totalVencidas = count($manutencoeVencidas);
$totalRelatorios = count($relatoriosRecentes);
$totalAgendadas = $totalInspecoesAgendadas ?? 0;
$proximaInspecao = null;
foreach ($proximasManutencoes as $item) {
    $statusItem = strtolower((string)($item['status'] ?? ''));
    if (!in_array($statusItem, ['concluido', 'concluida', 'cancelado'], true)) {
        $proximaInspecao = $item;
        break;
    }
}
$estadoOperacional = $totalVencidas > 0 ? 'Atenção imediata' : ($totalProximas > 0 ? 'Sob controlo' : 'Sem pressão imediata');
$estadoClasse = $totalVencidas > 0 ? 'is-critical' : ($totalProximas > 0 ? 'is-warning' : 'is-stable');
?>

<section class="dashboard-page">
    <div class="dashboard-hero">
        <div class="dashboard-hero__content">
            <span class="dashboard-eyebrow">Centro de controlo</span>
            <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
            <p>
                Monitorize o plano de inspeções, identifique atrasos e avance rapidamente para o registo das medidas de autoproteção.
            </p>

            <div class="dashboard-hero__actions">
                <a href="index.php?controler=inspecao&acao=listar" class="btn btn-dashboard-primary">
                    <i class="bi bi-clipboard-check"></i> Registar inspeção
                </a>
                <a href="index.php?controler=calendario&acao=dashboard" class="btn btn-dashboard-secondary">
                    <i class="bi bi-calendar3"></i> Ver agenda operacional
                </a>
            </div>
        </div>

        <aside class="dashboard-hero__panel <?php echo $estadoClasse; ?>">
            <div class="dashboard-hero__panel-label">Estado operacional</div>
            <div class="dashboard-hero__panel-value"><?php echo $estadoOperacional; ?></div>
            <p>
                <?php if ($proximaInspecao): ?>
                    Próxima ação a <?php echo date('d/m/Y', strtotime($proximaInspecao['data_inspecao'])); ?> em <?php echo htmlspecialchars($proximaInspecao['localizacao'] ?: $proximaInspecao['tipo_equipamento']); ?>.
                <?php else: ?>
                    Não existem inspeções agendadas para os próximos dias.
                <?php endif; ?>
            </p>
        </aside>
    </div>

    <div class="dashboard-stats-grid">
        <article class="dashboard-stat-card tone-ocean">
            <div class="dashboard-stat-card__icon"><i class="bi bi-tools"></i></div>
            <div>
                <span class="dashboard-stat-card__label">Equipamentos registados</span>
                <strong class="dashboard-stat-card__value"><?php echo $totalEquipamentos; ?></strong>
                <span class="dashboard-stat-card__meta">Base instalada sob gestão</span>
            </div>
        </article>

        <article class="dashboard-stat-card tone-gold">
            <div class="dashboard-stat-card__icon"><i class="bi bi-calendar-event"></i></div>
            <div>
                <span class="dashboard-stat-card__label">Inspeções nos próximos 7 dias</span>
                <strong class="dashboard-stat-card__value"><?php echo $totalProximas; ?></strong>
                <span class="dashboard-stat-card__meta">Janela curta de planeamento</span>
            </div>
        </article>

        <article class="dashboard-stat-card tone-coral">
            <div class="dashboard-stat-card__icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <span class="dashboard-stat-card__label">Inspeções em atraso</span>
                <strong class="dashboard-stat-card__value"><?php echo $totalVencidas; ?></strong>
                <span class="dashboard-stat-card__meta">Itens que exigem recuperação</span>
            </div>
        </article>

        <article class="dashboard-stat-card tone-mint">
            <div class="dashboard-stat-card__icon"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <span class="dashboard-stat-card__label">Relatórios recentes</span>
                <strong class="dashboard-stat-card__value"><?php echo $totalRelatorios; ?></strong>
                <span class="dashboard-stat-card__meta">Últimos 30 dias</span>
            </div>
        </article>
    </div>

    <div class="dashboard-panels-grid">
        <section class="dashboard-panel dashboard-panel--wide">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-panel__eyebrow">Planeamento</span>
                    <h2>Próximas inspeções</h2>
                </div>
                <a href="index.php?controler=calendario&acao=dashboard" class="dashboard-panel__link">Ver agenda</a>
            </div>

            <?php if (empty($proximasManutencoes)): ?>
                <div class="dashboard-empty-state">
                    <div class="dashboard-empty-state__icon"><i class="bi bi-calendar2-check"></i></div>
                    <div>
                        <strong>Sem inspeções imediatas</strong>
                        <p>Nenhuma inspeção está prevista para os próximos 7 dias.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="dashboard-list">
                    <?php foreach ($proximasManutencoes as $manutencao): ?>
                        <article class="dashboard-list__item">
                            <div class="dashboard-list__date">
                                <span><?php echo date('d', strtotime($manutencao['data_inspecao'])); ?></span>
                                <small><?php echo date('M', strtotime($manutencao['data_inspecao'])); ?></small>
                            </div>
                            <div class="dashboard-list__content">
                                <h3><?php echo htmlspecialchars($manutencao['localizacao'] ?: $manutencao['tipo_equipamento']); ?></h3>
                                <p><?php echo htmlspecialchars($manutencao['tipo_equipamento']); ?></p>
                            </div>
                            <div class="dashboard-list__meta">
                                <?php if (in_array(($manutencao['status'] ?? ''), ['concluido', 'concluida'], true)): ?>
                                    <span class="dashboard-done"><i class="bi bi-check-circle-fill"></i> Concluída</span>
                                <?php endif; ?>
                                <span class="dashboard-chip"><?php echo ucfirst($manutencao['tipo_inspecao']); ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-panel__eyebrow">Execução</span>
                    <h2>Atalhos rápidos</h2>
                </div>
            </div>

            <div class="dashboard-action-grid">
                <a href="index.php?controler=equipamento&acao=criar" class="dashboard-action-card">
                    <i class="bi bi-plus-square"></i>
                    <strong>Novo equipamento</strong>
                    <span>Adicionar um registo ao inventário</span>
                </a>

                <a href="index.php?controler=calendario&acao=agendar" class="dashboard-action-card">
                    <i class="bi bi-calendar-plus"></i>
                    <strong>Agendar inspeção</strong>
                    <span>Planear a próxima ação preventiva</span>
                </a>

                <a href="index.php?controler=inspecao&acao=listar" class="dashboard-action-card">
                    <i class="bi bi-clipboard-data"></i>
                    <strong>Executar inspeção</strong>
                    <span>Preencher inspeções previstas no calendário</span>
                </a>

                <a href="index.php?controler=relatorio&acao=pendentes" class="dashboard-action-card accent-warning">
                    <i class="bi bi-clock-history"></i>
                    <strong>Relatórios pendentes</strong>
                    <span>Fechar relatórios ainda por assinar</span>
                </a>
            </div>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-panel__eyebrow">Resumo</span>
                    <h2>Leitura rápida</h2>
                </div>
            </div>

            <div class="dashboard-summary-stack">
                <article class="dashboard-summary-row">
                    <span>Inspeções agendadas</span>
                    <strong><?php echo $totalAgendadas; ?></strong>
                </article>
                <article class="dashboard-summary-row">
                    <span>Relatórios dos últimos 30 dias</span>
                    <strong><?php echo $totalRelatorios; ?></strong>
                </article>
                <article class="dashboard-summary-row">
                    <span>Ocorrências em atraso</span>
                    <strong class="<?php echo $totalVencidas > 0 ? 'text-danger' : 'text-success'; ?>"><?php echo $totalVencidas; ?></strong>
                </article>
            </div>
        </section>
    </div>
</section>
