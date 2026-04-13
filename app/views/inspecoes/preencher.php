<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Preencher Inspeção</h1>
        <form method="POST" action="index.php?controler=inspecao&acao=guardar&id=<?php echo $inspecao['id']; ?>" class="card">
            <div class="card-body">
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
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="2"><?php echo htmlspecialchars($inspecao['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Submeter Inspeção</button>
                <a href="index.php?controler=inspecao&acao=listar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>
