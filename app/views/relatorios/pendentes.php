<section class="page-shell">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Pendências</span>
            <h1><i class="bi bi-clock-history"></i> Relatórios pendentes de assinatura</h1>
            <p>Feche os relatórios que ainda aguardam validação final para manter o arquivo documental atualizado.</p>
        </div>
    </div>

    <section class="panel-surface">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Ação necessária</span>
                <h2>Documentos por assinar</h2>
            </div>
        </div>

        <?php if (empty($relatorios)): ?>
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon"><i class="bi bi-patch-check"></i></div>
                <div>
                    <strong>Tudo em dia</strong>
                    <p>Todos os relatórios já foram assinados.</p>
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
                                <td class="table-actions">
                                    <a href="index.php?controler=relatorio&acao=ver&id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-primary">Ver e assinar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <div class="form-actions-bar top-padding">
        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</section>
