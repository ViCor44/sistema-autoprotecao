<?php
$totalRelatorios = count($relatorios);
$pendentes = count(array_filter($relatorios, function ($rel) {
    return empty($rel['assinado']);
}));
?>

<section class="page-shell">
    <div class="page-hero">
        <div>
            <span class="page-hero__eyebrow">Documentação técnica</span>
            <h1><i class="bi bi-clipboard-data"></i> Relatórios</h1>
            <p>Acompanhe relatórios emitidos, pendências de assinatura e o histórico recente das medidas de autoproteção.</p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=relatorio&acao=criar" class="btn btn-dashboard-primary">
                <i class="bi bi-file-earmark-plus"></i> Novo relatório
            </a>
            <a href="index.php?controler=relatorio&acao=pendentes" class="btn btn-dashboard-secondary">
                <i class="bi bi-clock-history"></i> Pendentes
            </a>
        </div>
    </div>

    <div class="mini-stats-grid">
        <article class="mini-stat-card">
            <span>Total de relatórios</span>
            <strong><?php echo $totalRelatorios; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Pendentes de assinatura</span>
            <strong><?php echo $pendentes; ?></strong>
        </article>
    </div>

    <section class="panel-surface">
        <div class="panel-surface__header">
            <div>
                <span class="panel-surface__eyebrow">Arquivo</span>
                <h2>Registos emitidos</h2>
            </div>
        </div>

        <?php if (empty($relatorios)): ?>
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon"><i class="bi bi-file-earmark-x"></i></div>
                <div>
                    <strong>Sem relatórios registados</strong>
                    <p>Quando forem emitidos relatórios, passam a ficar disponíveis nesta listagem.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive modern-table-wrap">
                <table class="table modern-table align-middle">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Equipamento</th>
                            <th>Tipo</th>
                            <th>Responsável</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatorios as $rel): ?>
                            <tr>
                                <td><strong><?php echo date('d/m/Y', strtotime($rel['data_relatorio'])); ?></strong></td>
                                <td><?php echo htmlspecialchars($rel['localizacao'] ?: $rel['tipo_equipamento']); ?></td>
                                <td><?php echo ucfirst($rel['tipo_relatorio']); ?></td>
                                <td><?php echo htmlspecialchars($rel['responsavel_nome']); ?></td>
                                <td>
                                    <span class="status-pill status-pill--<?php echo $rel['assinado'] ? 'success' : 'warning'; ?>">
                                        <?php echo $rel['assinado'] ? 'Assinado' : 'Pendente'; ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="index.php?controler=relatorio&acao=ver&id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
