<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Equipamentos</h5>
                <h2><?php echo $totalEquipamentos; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Manutenções Próximas (7 dias)</h5>
                <h2><?php echo count($proximasManutencoes); ?></h2>
                <a href="index.php?controler=calendario&acao=dashboard" class="btn btn-sm btn-light">Ver</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Manutenções Vencidas</h5>
                <h2><?php echo count($manutencoeVencidas); ?></h2>
                <a href="index.php?controler=calendario&acao=dashboard" class="btn btn-sm btn-light">Ver</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Relatórios Recentes (30 dias)</h5>
                <h2><?php echo count($relatoriosRecentes); ?></h2>
                <a href="index.php?controler=relatorio&acao=listar" class="btn btn-sm btn-light">Ver</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Próximas Manutenções</h5>
            </div>
            <div class="card-body">
                <?php if (empty($proximasManutencoes)): ?>
                    <p class="text-muted">Nenhuma manutenção agendada para os próximos 7 dias.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Equipamento</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximasManutencoes as $manutencao): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($manutencao['data_inspecao'])); ?></td>
                                        <td><?php echo $manutencao['localizacao']; ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($manutencao['tipo_inspecao']); ?></span></td>
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
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <a href="index.php?controler=equipamento&acao=criar" class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="bi bi-plus-circle"></i> Registar Novo Equipamento
                </a>
                <a href="index.php?controler=relatorio&acao=criar" class="btn btn-success btn-sm w-100 mb-2">
                    <i class="bi bi-file-earmark-plus"></i> Criar Novo Relatório
                </a>
                <a href="index.php?controler=calendario&acao=agendar" class="btn btn-info btn-sm w-100 mb-2">
                    <i class="bi bi-calendar-plus"></i> Agendar Manutenção
                </a>
                <a href="index.php?controler=relatorio&acao=pendentes" class="btn btn-warning btn-sm w-100">
                    <i class="bi bi-clock-history"></i> Relatórios Pendentes
                </a>
            </div>
        </div>
    </div>
</div>
