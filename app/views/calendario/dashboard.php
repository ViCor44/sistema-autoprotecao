<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard de Manutenção</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Próximas Manutenções (30 dias)</h5>
                <h2><?php echo count($proximasManutencoes); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Manutenções Vencidas</h5>
                <h2><?php echo count($manutencoeVencidas); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Equipamentos com Manutenção Pendente</h5>
                <h2><?php echo count($equipamentosPendentes); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Próximas Manutenções</h5>
            </div>
            <div class="card-body">
                <?php if (empty($proximasManutencoes)): ?>
                    <p class="text-muted">Nenhuma manutenção próxima.</p>
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
                                <?php foreach ($proximasManutencoes as $m): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($m['data_inspecao'])); ?></td>
                                        <td><?php echo $m['localizacao']; ?></td>
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
                <h5 class="mb-0">Manutenções Vencidas</h5>
            </div>
            <div class="card-body">
                <?php if (empty($manutencoeVencidas)): ?>
                    <p class="text-muted text-success"><i class="bi bi-check-circle"></i> Nenhuma manutenção vencida!</p>
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
                                <?php foreach ($manutencoeVencidas as $m): ?>
                                    <tr class="table-danger">
                                        <td><?php echo date('d/m/Y', strtotime($m['data_inspecao'])); ?></td>
                                        <td><?php echo $m['localizacao']; ?></td>
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
