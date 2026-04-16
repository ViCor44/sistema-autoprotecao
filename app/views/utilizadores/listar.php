<?php
$pendentesCount = count($pendentes);
$ativosCount = count(array_filter($utilizadores, function ($utilizador) {
    return !empty($utilizador['ativo']) && !empty($utilizador['aprovado']);
}));
$inativosCount = count(array_filter($utilizadores, function ($utilizador) {
    return empty($utilizador['ativo']) && !empty($utilizador['aprovado']);
}));
$utilizadoresAprovados = array_filter($utilizadores, function ($utilizador) {
    return !empty($utilizador['aprovado']);
});
?>

<section class="page-shell">
    <div class="page-hero">
        <div>
            <span class="page-hero__eyebrow">Administração</span>
            <h1><i class="bi bi-people"></i> Utilizadores</h1>
            <p>Gerencie pedidos de registo e o estado das contas aprovadas.</p>
        </div>
    </div>

    <div class="mini-stats-grid">
        <article class="mini-stat-card">
            <span>Pendentes de aprovação</span>
            <strong><?php echo $pendentesCount; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Utilizadores ativos</span>
            <strong><?php echo $ativosCount; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Utilizadores inativos</span>
            <strong><?php echo $inativosCount; ?></strong>
        </article>
    </div>

    <section class="panel-surface">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Aprovação</span>
                <h2>Pedidos pendentes</h2>
            </div>
        </div>

        <?php if (empty($pendentes)): ?>
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon"><i class="bi bi-patch-check"></i></div>
                <div>
                    <strong>Sem pedidos pendentes</strong>
                    <p>Os novos registos submetidos vão aparecer aqui para aprovação.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive modern-table-wrap">
                <table class="table modern-table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendentes as $pendente): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($pendente['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pendente['email']); ?></td>
                                <td><?php echo htmlspecialchars($pendente['telefone'] ?: '-'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pendente['data_criacao'])); ?></td>
                                <td class="table-actions">
                                    <form method="POST" action="index.php?controler=utilizador&acao=aprovar&id=<?php echo (int)$pendente['id']; ?>" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel-surface">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Contas</span>
                <h2>Utilizadores registados</h2>
            </div>
        </div>

        <div class="table-responsive modern-table-wrap">
            <table class="table modern-table align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Função</th>
                        <th>Estado</th>
                        <th>Aprovado por</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilizadoresAprovados as $utilizador): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($utilizador['nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($utilizador['email']); ?></td>
                            <td><?php echo htmlspecialchars($utilizador['funcao'] ?: 'tecnico'); ?></td>
                            <td>
                                <?php if (!empty($utilizador['ativo'])): ?>
                                    <span class="status-pill status-pill--success">Ativo</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill--info">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($utilizador['aprovado_por_nome'] ?: '-'); ?></td>
                            <td class="table-actions">
                                <?php if (!empty($utilizador['ativo'])): ?>
                                    <form method="POST" action="index.php?controler=utilizador&acao=inativar&id=<?php echo (int)$utilizador['id']; ?>" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" <?php echo ((int)$utilizador['id'] === (int)($_SESSION['utilizador_id'] ?? 0)) ? 'disabled' : ''; ?>>Inativar</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="index.php?controler=utilizador&acao=ativar&id=<?php echo (int)$utilizador['id']; ?>" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Ativar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>