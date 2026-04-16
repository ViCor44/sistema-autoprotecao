<section class="page-shell page-shell--narrow">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Fecho da inspeção</span>
            <h1><i class="bi bi-clipboard-check"></i> Resultado da inspeção</h1>
            <p>Visualize o resumo executivo da inspeção concluída e siga para o relatório formal ou exportação em PDF.</p>
        </div>
        <div class="page-hero__actions">
            <?php if (!empty($relatorio)): ?>
                <a href="index.php?controler=relatorio&acao=ver&id=<?php echo $relatorio['id']; ?>" class="btn btn-dashboard-primary">
                    <i class="bi bi-file-earmark-text"></i> Abrir relatório
                </a>
            <?php endif; ?>
            <a href="index.php?controler=inspecao&acao=exportar_pdf&id=<?php echo $inspecao['id']; ?>" class="btn btn-dashboard-secondary" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
        </div>
    </div>

    <div class="content-grid-two">
        <section class="panel-surface">
            <div class="panel-surface__header compact">
                <div>
                    <span class="panel-surface__eyebrow">Resumo</span>
                    <h2>Informações da inspeção</h2>
                </div>
            </div>
            <div class="detail-stack">
                <div class="detail-row"><span>Tipo de equipamento</span><strong><?php echo htmlspecialchars($inspecao['tipo_equipamento']); ?></strong></div>
                <div class="detail-row"><span>Âmbito</span><strong><?php echo htmlspecialchars($inspecao['localizacao'] ?? 'Todos os equipamentos do tipo'); ?></strong></div>
                <div class="detail-row"><span>Data planeada</span><strong><?php echo date('d/m/Y', strtotime($inspecao['data_inspecao'])); ?></strong></div>
                <div class="detail-row"><span>Data de execução</span><strong><?php echo !empty($inspecao['data_realizacao']) ? date('d/m/Y H:i', strtotime($inspecao['data_realizacao'])) : '-'; ?></strong></div>
                <div class="detail-row"><span>Responsável</span><strong><?php echo htmlspecialchars($inspecao['responsavel_nome'] ?? '-'); ?></strong></div>
                <div class="detail-row"><span>Estado</span><strong><?php echo ucfirst(str_replace('_', ' ', $inspecao['status'])); ?></strong></div>
                <div class="detail-row"><span>Condição</span><strong><?php echo ucfirst($inspecao['condicoes_encontradas'] ?? '-'); ?></strong></div>
                <div class="detail-row"><span>Próxima inspeção</span><strong><?php echo !empty($inspecao['proxima_inspecao']) ? date('d/m/Y', strtotime($inspecao['proxima_inspecao'])) : '-'; ?></strong></div>
            </div>
        </section>

        <section class="panel-surface">
            <div class="panel-surface__header compact">
                <div>
                    <span class="panel-surface__eyebrow">Conteúdo</span>
                    <h2>Notas da execução</h2>
                </div>
            </div>
            <div class="narrative-block">
                <h3>Parecer técnico</h3>
                <p><?php echo nl2br(htmlspecialchars($inspecao['parecer'] ?? '-')); ?></p>
            </div>
            <div class="narrative-block compact">
                <h3>Equipamentos avariados</h3>
                <p><?php echo nl2br(htmlspecialchars($inspecao['equipamentos_avariados'] ?? '-')); ?></p>
            </div>
            <div class="narrative-block compact">
                <h3>Observações</h3>
                <p><?php echo nl2br(htmlspecialchars($inspecao['observacoes'] ?? '-')); ?></p>
            </div>
        </section>
    </div>

    <div class="form-actions-bar top-padding">
        <a href="index.php?controler=inspecao&acao=listar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</section>
