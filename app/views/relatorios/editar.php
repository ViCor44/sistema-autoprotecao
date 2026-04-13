<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Editar Relatório</h1>
        <form method="POST" action="index.php?controler=relatorio&acao=atualizar&id=<?php echo $relatorio['id']; ?>" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" id="descricao" rows="3"><?php echo htmlspecialchars($relatorio['descricao']); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="condicoes_encontradas" class="form-label">Condição</label>
                        <select name="condicoes_encontradas" id="condicoes_encontradas" class="form-select">
                            <option value="">Selecione...</option>
                            <option value="bom" <?php if($relatorio['condicoes_encontradas']==='bom') echo 'selected'; ?>>Bom</option>
                            <option value="aceitavel" <?php if($relatorio['condicoes_encontradas']==='aceitavel') echo 'selected'; ?>>Aceitável</option>
                            <option value="deficiente" <?php if($relatorio['condicoes_encontradas']==='deficiente') echo 'selected'; ?>>Deficiente</option>
                            <option value="inservivel" <?php if($relatorio['condicoes_encontradas']==='inservivel') echo 'selected'; ?>>Inservível</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="proxima_inspecao" class="form-label">Próxima Inspeção</label>
                        <input type="date" class="form-control" name="proxima_inspecao" id="proxima_inspecao" value="<?php echo htmlspecialchars($relatorio['proxima_inspecao']); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="3"><?php echo htmlspecialchars($relatorio['observacoes']); ?></textarea>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar</button>
                <a href="index.php?controler=relatorio&acao=ver&id=<?php echo $relatorio['id']; ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>
