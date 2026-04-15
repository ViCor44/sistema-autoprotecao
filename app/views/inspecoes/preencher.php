<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Preencher Inspeção</h1>
        <div class="card mb-3">
            <div class="card-body">
                <p class="mb-0">
                    <strong>Tipo de Equipamento:</strong> <?php echo htmlspecialchars($inspecao['tipo_equipamento']); ?><br>
                    <strong>Âmbito:</strong> <?php echo !empty($inspecao['localizacao']) ? htmlspecialchars($inspecao['localizacao']) : 'Todos os equipamentos do tipo'; ?><br>
                    <strong>Data Planeada:</strong> <?php echo date('d/m/Y', strtotime($inspecao['data_inspecao'])); ?><br>
                    <strong>Responsável:</strong> <?php echo htmlspecialchars($inspecao['responsavel_nome'] ?? '-'); ?>
                </p>
            </div>
        </div>
        <form method="POST" action="index.php?controler=inspecao&acao=guardar&id=<?php echo $inspecao['id']; ?>" class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="condicoes_encontradas" class="form-label">Condição Encontrada</label>
                        <select class="form-select" name="condicoes_encontradas" id="condicoes_encontradas">
                            <option value="">Selecione...</option>
                            <option value="bom" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'bom') echo 'selected'; ?>>Bom</option>
                            <option value="aceitavel" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'aceitavel') echo 'selected'; ?>>Aceitável</option>
                            <option value="deficiente" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'deficiente') echo 'selected'; ?>>Deficiente</option>
                            <option value="inservivel" <?php if (($inspecao['condicoes_encontradas'] ?? '') === 'inservivel') echo 'selected'; ?>>Inservível</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="proxima_inspecao" class="form-label">Próxima Inspeção</label>
                        <input class="form-control" type="date" name="proxima_inspecao" id="proxima_inspecao" value="<?php echo htmlspecialchars($inspecao['proxima_inspecao'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="parecer" class="form-label">Parecer</label>
                    <textarea class="form-control" name="parecer" id="parecer" rows="3" required><?php echo htmlspecialchars($inspecao['parecer'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="equipamentos_avariados" class="form-label">Equipamentos Avariados</label>
                    <textarea class="form-control" name="equipamentos_avariados" id="equipamentos_avariados" rows="2"><?php echo htmlspecialchars($inspecao['equipamentos_avariados'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="3"><?php echo htmlspecialchars($inspecao['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Submeter Inspeção</button>
                <a href="index.php?controler=inspecao&acao=listar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>
