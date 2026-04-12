<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-file-earmark-plus"></i> Novo Relatório</h1>

        <form method="POST" action="index.php?controler=relatorio&acao=salvar" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="equipamento_id" class="form-label">Equipamento *</label>
                    <select name="equipamento_id" id="equipamento_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($equipamentos as $equip): ?>
                            <option value="<?php echo $equip['id']; ?>"><?php echo $equip['tipo_nome']; ?> - <?php echo $equip['localizacao']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="data_relatorio" class="form-label">Data do Relatório</label>
                        <input type="date" class="form-control" name="data_relatorio" id="data_relatorio" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo_relatorio" class="form-label">Tipo de Relatório</label>
                        <select name="tipo_relatorio" id="tipo_relatorio" class="form-select">
                            <option value="inspecao">Inspeção</option>
                            <option value="manutencao">Manutenção</option>
                            <option value="reparacao">Reparação</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" id="descricao" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="condicoes_encontradas" class="form-label">Condição</label>
                        <select name="condicoes_encontradas" id="condicoes_encontradas" class="form-select">
                            <option value="">Selecione...</option>
                            <option value="bom">Bom</option>
                            <option value="aceitavel">Aceitável</option>
                            <option value="deficiente">Deficiente</option>
                            <option value="inservivel">Inservível</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="proxima_inspecao" class="form-label">Próxima Inspeção</label>
                        <input type="date" class="form-control" name="proxima_inspecao" id="proxima_inspecao">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="3"></textarea>
                </div>
            </div>

            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Salvar</button>
                <a href="index.php?controler=relatorio&acao=listar" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>
