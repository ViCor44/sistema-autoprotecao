<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-clipboard-data"></i> Inspeções Agendadas</h1>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <a href="index.php?controler=calendario&acao=listar" class="btn btn-outline-primary">
            <i class="bi bi-calendar3"></i> Ver Calendário de Inspeções
        </a>
    </div>
</div>
<?php if (empty($inspecoes)): ?>
    <div class="alert alert-info">Nenhuma inspeção agendada.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Data</th>
                    <th>Tipo de Equipamento</th>
                    <th>Localização</th>
                    <th>Status</th>
                    <th>Responsável</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inspecoes as $insp): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($insp['data_inspecao'])); ?></td>
                        <td><?php echo $insp['tipo_equipamento']; ?></td>
                        <td><?php echo $insp['localizacao'] ?? '-'; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $insp['status'] === 'concluido' ? 'success' : ($insp['status'] === 'em_progresso' ? 'info' : 'warning'); ?>">
                                <?php echo ucfirst(str_replace('_',' ',$insp['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo $insp['responsavel_nome'] ?? '-'; ?></td>
                        <td>
                            <?php if ($insp['status'] !== 'concluido'): ?>
                                <a href="index.php?controler=inspecao&acao=preencher&id=<?php echo $insp['id']; ?>" class="btn btn-sm btn-warning">Preencher</a>
                            <?php else: ?>
                                <a href="index.php?controler=inspecao&acao=ver&id=<?php echo $insp['id']; ?>" class="btn btn-sm btn-primary">Ver Relatório</a>
                                <a href="index.php?controler=inspecao&acao=exportar_pdf&id=<?php echo $insp['id']; ?>" class="btn btn-sm btn-outline-secondary">PDF</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
