<?php
$totalInspecoes = count($inspecoes);
$inspecoesConcluidas = count(array_filter($inspecoes, function ($insp) {
    return ($insp['status'] ?? '') === 'concluido';
}));
$inspecoesPendentes = $totalInspecoes - $inspecoesConcluidas;
?>

<section class="page-shell">
    <div class="page-hero">
        <div>
            <span class="page-hero__eyebrow">Execução operacional</span>
            <h1><i class="bi bi-clipboard-data"></i> Inspeções agendadas</h1>
            <p>Consulte o plano definido no calendário, identifique o que está por executar e avance diretamente para o registo da inspeção.</p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=calendario&acao=listar" class="btn btn-dashboard-secondary">
                <i class="bi bi-calendar3"></i> Ver calendário
            </a>
        </div>
    </div>

    <div class="mini-stats-grid">
        <article class="mini-stat-card">
            <span>Total planeado</span>
            <strong><?php echo $totalInspecoes; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Concluídas</span>
            <strong><?php echo $inspecoesConcluidas; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Por executar</span>
            <strong><?php echo $inspecoesPendentes; ?></strong>
        </article>
    </div>

    <section class="panel-surface">
        <div class="panel-surface__header">
            <div>
                <span class="panel-surface__eyebrow">Lista operacional</span>
                <h2>Inspeções do calendário</h2>
            </div>
        </div>

        <?php if (empty($inspecoes)): ?>
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon"><i class="bi bi-calendar2-x"></i></div>
                <div>
                    <strong>Nenhuma inspeção agendada</strong>
                    <p>Quando existirem ações programadas, passam a aparecer aqui para execução e fecho.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive modern-table-wrap">
                <table class="table modern-table align-middle">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Âmbito</th>
                            <th>Estado</th>
                            <th>Responsável</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inspecoes as $insp): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('d/m/Y', strtotime($insp['data_inspecao'])); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($insp['tipo_equipamento']); ?></td>
                                <td><?php echo htmlspecialchars($insp['localizacao'] ?? 'Todos do tipo'); ?></td>
                                <td>
                                    <span class="status-pill status-pill--<?php echo $insp['status'] === 'concluido' ? 'success' : (($insp['status'] ?? '') === 'em_progresso' ? 'info' : 'warning'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $insp['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($insp['responsavel_nome'] ?? '-'); ?></td>
                                <td class="table-actions">
                                    <?php if ($insp['status'] !== 'concluido'): ?>
                                        <a href="index.php?controler=inspecao&acao=preencher&id=<?php echo $insp['id']; ?>" class="btn btn-sm btn-warning">Preencher</a>
                                    <?php else: ?>
                                        <a href="index.php?controler=inspecao&acao=ver&id=<?php echo $insp['id']; ?>" class="btn btn-sm btn-primary">Ver relatório</a>
                                        <a href="index.php?controler=inspecao&acao=exportar_pdf&id=<?php echo $insp['id']; ?>" class="btn btn-sm btn-outline-secondary">PDF</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
