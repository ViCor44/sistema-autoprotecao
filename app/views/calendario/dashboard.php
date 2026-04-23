<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard de Inspeções</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <a href="index.php?controler=inspecao&acao=listar" class="card bg-info text-white text-decoration-none dashboard-kpi-link">
            <div class="card-body">
                <h5 class="card-title">Próximas Inspeções (30 dias)</h5>
                <h2><?php echo count($proximasInspecoes); ?></h2>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="index.php?controler=inspecao&acao=listar" class="card bg-danger text-white text-decoration-none dashboard-kpi-link">
            <div class="card-body">
                <h5 class="card-title">Inspeções em Atraso</h5>
                <h2><?php echo count($inspecoesVencidas); ?></h2>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="index.php?controler=equipamento&acao=listar" class="card bg-warning text-white text-decoration-none dashboard-kpi-link">
            <div class="card-body">
                <h5 class="card-title">Equipamentos com Vistoria Pendente</h5>
                <h2><?php echo count($equipamentosPendentes); ?></h2>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Próximas Inspeções</h5>
            </div>
            <div class="card-body">
                <?php if (empty($proximasInspecoes)): ?>
                    <p class="text-muted">Nenhuma inspeção próxima.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Equipamento</th>
                                    <th>Prioridade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximasInspecoes as $m): ?>
                                    <tr class="dashboard-table-clickable-row" onclick="window.location.href='index.php?controler=inspecao&acao=preencher&id=<?php echo (int)$m['id']; ?>'">
                                        <td><?php echo date('d/m/Y', strtotime($m['data_inspecao'])); ?></td>
                                        <td><?php echo htmlspecialchars((string)$m['localizacao']); ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($m['prioridade']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Inspeções em Atraso</h5>
            </div>
            <div class="card-body">
                <?php if (empty($inspecoesVencidas)): ?>
                    <p class="text-muted text-success"><i class="bi bi-check-circle"></i> Nenhuma inspeção em atraso!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Equipamento</th>
                                    <th>Dias Atraso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inspecoesVencidas as $m): ?>
                                    <tr class="table-danger dashboard-table-clickable-row" onclick="window.location.href='index.php?controler=inspecao&acao=preencher&id=<?php echo (int)$m['id']; ?>'">
                                        <td><?php echo date('d/m/Y', strtotime($m['data_inspecao'])); ?></td>
                                        <td><?php echo htmlspecialchars((string)$m['localizacao']); ?></td>
                                        <td><?php echo floor((strtotime('now') - strtotime($m['data_inspecao'])) / 86400); ?> dias</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
