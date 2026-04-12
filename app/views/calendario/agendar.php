<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-calendar-plus"></i> Agendar Manutenção</h1>

        <form method="POST" action="index.php?controler=calendario&acao=salvar" class="card">
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
                        <label for="data_inspecao" class="form-label">Data da Inspeção *</label>
                        <input type="date" class="form-control" name="data_inspecao" id="data_inspecao" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo_inspecao" class="form-label">Tipo de Inspeção</label>
                        <select name="tipo_inspecao" id="tipo_inspecao" class="form-select">
                            <option value="inspeção">Inspeção</option>
                            <option value="manutenção">Manutenção</option>
                            <option value="reparação">Reparação</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" id="descricao" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prioridade" class="form-label">Prioridade</label>
                        <select name="prioridade" id="prioridade" class="form-select">
                            <option value="baixa">Baixa</option>
                            <option value="normal" selected>Normal</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="responsavel_id" class="form-label">Responsável</label>
                        <input type="text" class="form-control" name="responsavel_id" id="responsavel_id" placeholder="Opcional">
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Agendar</button>
                <a href="index.php?controler=calendario&acao=listar" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>
