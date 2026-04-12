<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-clock-history"></i> Relatórios Pendentes de Assinatura</h1>
    </div>
</div>

<?php if (empty($relatorios)): ?>
    <div class="alert alert-success">Todos os relatórios foram assinados!</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Data</th>
                    <th>Equipamento</th>
                    <th>Tipo</th>
                    <th>Responsável</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($relatorios as $rel): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($rel['data_relatorio'])); ?></td>
                        <td><?php echo $rel['localizacao']; ?></td>
                        <td><?php echo ucfirst($rel['tipo_relatorio']); ?></td>
                        <td><?php echo $rel['responsavel_nome']; ?></td>
                        <td>
                            <a href="index.php?controler=relatorio&acao=ver&id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-primary">Ver e Assinar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="mt-3">
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
