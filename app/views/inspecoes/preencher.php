<section class="page-shell page-shell--narrow">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Registo em campo</span>
            <h1><i class="bi bi-pencil-square"></i> Preencher inspeção</h1>
            <p>Registe a execução real da inspeção prevista, o estado encontrado e as ações a programar a seguir.</p>
        </div>
    </div>

    <div class="content-grid-two">
        <aside class="panel-surface panel-surface--sidebar">
            <div class="panel-surface__header compact">
                <div>
                    <span class="panel-surface__eyebrow">Contexto</span>
                    <h2>Dados do agendamento</h2>
                </div>
            </div>
            <div class="detail-stack">
                <div class="detail-row"><span>Tipo de equipamento</span><strong><?php echo htmlspecialchars($inspecao['tipo_equipamento']); ?></strong></div>
                <div class="detail-row"><span>Âmbito</span><strong><?php echo !empty($inspecao['localizacao']) ? htmlspecialchars($inspecao['localizacao']) : 'Todos os equipamentos do tipo'; ?></strong></div>
                <div class="detail-row"><span>Data planeada</span><strong><?php echo date('d/m/Y', strtotime($inspecao['data_inspecao'])); ?></strong></div>
                <div class="detail-row"><span>Responsável</span><strong><?php echo htmlspecialchars($inspecao['responsavel_nome'] ?? '-'); ?></strong></div>
            </div>
        </aside>

        <form method="POST" action="index.php?controler=inspecao&acao=guardar&id=<?php echo $inspecao['id']; ?>" class="panel-surface modern-form">
            <div class="panel-surface__header compact">
                <div>
                    <span class="panel-surface__eyebrow">Execução</span>
                    <h2>Resultado da inspeção</h2>
                </div>
            </div>

            <div class="form-grid-two">
                <div class="mb-3">
                    <label for="condicoes_encontradas" class="form-label">Condição encontrada</label>
                    <select class="form-select" name="condicoes_encontradas" id="condicoes_encontradas">
                        <option value="">Selecione...</option>
                        <option value="bom" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'bom') echo 'selected'; ?>>Bom</option>
                        <option value="aceitavel" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'aceitavel') echo 'selected'; ?>>Aceitável</option>
                        <option value="deficiente" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'deficiente') echo 'selected'; ?>>Deficiente</option>
                        <option value="inservivel" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'inservivel') echo 'selected'; ?>>Inservível</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="proxima_inspecao" class="form-label">Próxima inspeção</label>
                    <input class="form-control" type="date" name="proxima_inspecao" id="proxima_inspecao" value="<?php echo htmlspecialchars($inspecao['proxima_inspecao'] ?? ''); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="parecer" class="form-label">Parecer técnico</label>
                <textarea class="form-control" name="parecer" id="parecer" rows="4" required><?php echo htmlspecialchars($inspecao['parecer'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="equipamentos_avariados" class="form-label">Equipamentos avariados</label>
                <textarea class="form-control" name="equipamentos_avariados" id="equipamentos_avariados" rows="3"><?php echo htmlspecialchars($inspecao['equipamentos_avariados'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações adicionais</label>
                <textarea class="form-control" name="observacoes" id="observacoes" rows="4"><?php echo htmlspecialchars($inspecao['observacoes'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions-bar">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Submeter inspeção</button>
                <a href="index.php?controler=inspecao&acao=listar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
            </div>
        </form>
    </div>
</section>
