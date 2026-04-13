<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-calendar3"></i> Agendamentos de Inspeção</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <a href="index.php?controler=calendario&acao=agendar" class="btn btn-success">
            <i class="bi bi-calendar-plus"></i> Novo Agendamento
        </a>
    </div>
</div>

<?php if (empty($agendamentos)): ?>
    <div class="alert alert-info">Nenhum agendamento registado.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Data</th>
                    <th>Tipo de Equipamento</th>
                    <th>Âmbito</th>
                    <th>Tipo de Inspeção</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Responsável</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agendamentos as $ag): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($ag['data_inspecao'])); ?></td>
                        <td><?php echo $ag['tipo_equipamento']; ?></td>
                        <td>
                            <?php if (!empty($ag['equipamento_id'])): ?>
                                Equipamento: <?php echo $ag['localizacao']; ?>
                            <?php else: ?>
                                Todos do tipo
                            <?php endif; ?>
                        </td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $ag['tipo_inspecao'])); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                if ($ag['prioridade'] === 'urgente') echo 'danger';
                                elseif ($ag['prioridade'] === 'alta') echo 'warning';
                                else echo 'secondary';
                            ?>">
                                <?php echo ucfirst($ag['prioridade']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                if ($ag['status'] === 'concluido') echo 'success';
                                elseif ($ag['status'] === 'em_progresso') echo 'info';
                                elseif ($ag['status'] === 'cancelado') echo 'secondary';
                                else echo 'primary';
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $ag['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo $ag['responsavel_nome'] ?? '-'; ?></td>
                        <td>
                            <a href="index.php?controler=calendario&acao=ver&id=<?php echo $ag['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
