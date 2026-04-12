<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-clipboard-data"></i> Relatórios</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <a href="index.php?controler=relatorio&acao=criar" class="btn btn-success">
            <i class="bi bi-file-earmark-plus"></i> Novo Relatório
        </a>
        <a href="index.php?controler=relatorio&acao=pendentes" class="btn btn-warning">
            <i class="bi bi-clock-history"></i> Pendentes de Assinatura
        </a>
    </div>
</div>

<?php if (empty($relatorios)): ?>
    <div class="alert alert-info">Nenhum relatório registado.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
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
                        <td><?php echo date('d/m/Y', strtotime($rel['data_relatorio'])); ?></td>
                        <td><?php echo $rel['localizacao']; ?></td>
                        <td><?php echo ucfirst($rel['tipo_relatorio']); ?></td>
                        <td><?php echo $rel['responsavel_nome']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $rel['assinado'] ? 'success' : 'warning'; ?>">
                                <?php echo $rel['assinado'] ? 'Assinado' : 'Pendente'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?controler=relatorio&acao=ver&id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
