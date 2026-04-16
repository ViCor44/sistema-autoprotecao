<section class="page-shell page-shell--narrow">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Documento técnico</span>
            <h1><i class="bi bi-clipboard-check"></i> Relatório</h1>
            <p>Consulte os dados do relatório, o estado de assinatura e os itens de verificação associados.</p>
        </div>
        <div class="page-hero__actions">
            <?php if (!$relatorio['assinado']): ?>
                <a href="index.php?controler=relatorio&acao=editar&id=<?php echo $relatorio['id']; ?>" class="btn btn-dashboard-primary">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
            <?php endif; ?>
            <a href="index.php?controler=relatorio&acao=exportar_pdf&id=<?php echo $relatorio['id']; ?>" class="btn btn-dashboard-secondary" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
        </div>
    </div>

    <div class="content-grid-two">
        <section class="panel-surface">
            <div class="panel-surface__header compact">
                <div>
                    <span class="panel-surface__eyebrow">Metadados</span>
                    <h2>Informações do relatório</h2>
                </div>
            </div>
            <div class="detail-stack">
                <div class="detail-row"><span>Equipamento</span><strong><?php echo htmlspecialchars($relatorio['tipo_equipamento'] . ' (' . ($relatorio['localizacao'] ?: 'Todos os equipamentos do tipo') . ')'); ?></strong></div>
                <div class="detail-row"><span>Data</span><strong><?php echo date('d/m/Y', strtotime($relatorio['data_relatorio'])); ?></strong></div>
                <div class="detail-row"><span>Tipo</span><strong><?php echo ucfirst($relatorio['tipo_relatorio']); ?></strong></div>
                <div class="detail-row"><span>Responsável</span><strong><?php echo htmlspecialchars($relatorio['responsavel_nome']); ?></strong></div>
                <div class="detail-row"><span>Condição</span><strong><?php echo ucfirst($relatorio['condicoes_encontradas'] ?? '-'); ?></strong></div>
                <div class="detail-row"><span>Estado</span><strong><?php echo $relatorio['assinado'] ? 'Assinado' : 'Pendente de assinatura'; ?></strong></div>
                <?php $proximaTs = !empty($relatorio['proxima_inspecao']) && $relatorio['proxima_inspecao'] !== '0000-00-00' ? strtotime($relatorio['proxima_inspecao']) : false; ?>
                <div class="detail-row"><span>Próxima inspeção</span><strong><?php echo $proximaTs ? date('d/m/Y', $proximaTs) : '-'; ?></strong></div>
            </div>
        </section>

        <section class="panel-surface">
            <div class="panel-surface__header compact">
                <div>
                    <span class="panel-surface__eyebrow">Conteúdo</span>
                    <h2>Descrição e observações</h2>
                </div>
            </div>
            <div class="narrative-block">
                <h3>Descrição</h3>
                <p><?php echo nl2br(htmlspecialchars($relatorio['descricao'])); ?></p>
            </div>
            <div class="narrative-block compact">
                <h3>Observações</h3>
                <p><?php echo nl2br(htmlspecialchars($relatorio['observacoes'] ?: '-')); ?></p>
            </div>
        </section>
    </div>

    <section class="panel-surface">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Checklist</span>
                <h2>Itens de verificação</h2>
            </div>
        </div>
        <?php if (empty($itens)): ?>
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon"><i class="bi bi-list-check"></i></div>
                <div>
                    <strong>Sem itens registados</strong>
                    <p>Este relatório ainda não tem itens de verificação associados.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive modern-table-wrap">
                <table class="table modern-table align-middle">
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
                                <td><?php echo htmlspecialchars($item['descricao_verificacao']); ?></td>
                                <td><span class="status-pill status-pill--info"><?php echo ucfirst($item['resultado']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['observacao']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <div class="form-actions-bar top-padding">
        <?php if (!$relatorio['assinado']): ?>
            <a href="index.php?controler=relatorio&acao=assinar&id=<?php echo $relatorio['id']; ?>" class="btn btn-success" onclick="return confirm('Tem a certeza que deseja assinar este relatório?');">
                <i class="bi bi-check-circle"></i> Assinar
            </a>
        <?php endif; ?>
        <a href="index.php?controler=relatorio&acao=listar" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</section>
