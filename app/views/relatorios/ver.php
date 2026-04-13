<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-clipboard-check"></i> Relatório</h1>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Informações do Relatório</h5>
            </div>
            <div class="card-body">
                <p>
                    <strong>Equipamento:</strong> <?php echo $relatorio['tipo_equipamento']; ?> (<?php echo $relatorio['localizacao']; ?>)<br>
                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($relatorio['data_relatorio'])); ?><br>
                    <strong>Tipo:</strong> <?php echo ucfirst($relatorio['tipo_relatorio']); ?><br>
                    <strong>Responsável:</strong> <?php echo $relatorio['responsavel_nome']; ?><br>
                    <strong>Condição:</strong> <?php echo ucfirst($relatorio['condicoes_encontradas'] ?? '-'); ?><br>
                    <strong>Estado:</strong> 
                    <span class="badge bg-<?php echo $relatorio['assinado'] ? 'success' : 'warning'; ?>">
                        <?php echo $relatorio['assinado'] ? 'Assinado' : 'Pendente de Assinatura'; ?>
                    </span><br>
                    <strong>Descrição:</strong> <?php echo nl2br($relatorio['descricao']); ?><br>
                    <strong>Próxima Inspeção:</strong> <?php echo $relatorio['proxima_inspecao'] ? date('d/m/Y', strtotime($relatorio['proxima_inspecao'])) : '-'; ?>
                </p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Itens de Verificação</h5>
            </div>
            <div class="card-body">
                <?php if (empty($itens)): ?>
                    <p class="text-muted">Nenhum item registado.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Verificação</th>
                                    <th>Resultado</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td><?php echo $item['descricao_verificacao']; ?></td>
                                        <td><?php echo ucfirst($item['resultado']); ?></td>
                                        <td><?php echo $item['observacao']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer bg-white">
            <?php if (!$relatorio['assinado']): ?>
                <a href="index.php?controler=relatorio&acao=editar&id=<?php echo $relatorio['id']; ?>" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Preencher/Editar Formulário
                </a>
            <?php endif; ?>
            <?php if (!$relatorio['assinado']): ?>
                <a href="index.php?controler=relatorio&acao=assinar&id=<?php echo $relatorio['id']; ?>" class="btn btn-success" onclick="return confirm('Tem a certeza que deseja assinar este relatório?');">
                    <i class="bi bi-check-circle"></i> Assinar
                </a>
            <?php endif; ?>
            <a href="index.php?controler=relatorio&acao=exportar_pdf&id=<?php echo $relatorio['id']; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
            <a href="index.php?controler=relatorio&acao=listar" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>
