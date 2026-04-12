<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-plus-circle"></i> Novo Equipamento</h1>

        <form method="POST" action="index.php?controler=equipamento&acao=salvar" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="tipo_equipamento_id" class="form-label">Tipo de Equipamento *</label>
                    <select name="tipo_equipamento_id" id="tipo_equipamento_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>"><?php echo $tipo['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="numero_serie" class="form-label">Número de Registo / Património</label>
                        <input type="text" class="form-control" name="numero_serie" id="numero_serie" placeholder="Ex: EXT-00123">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="localizacao" class="form-label">Localização *</label>
                        <input type="text" class="form-control" name="localizacao" id="localizacao" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" name="marca" id="marca">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="modelo" class="form-label">Modelo</label>
                        <input type="text" class="form-control" name="modelo" id="modelo">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="data_aquisicao" class="form-label">Data de Aquisição</label>
                        <input type="date" class="form-control" name="data_aquisicao" id="data_aquisicao">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="data_instalacao" class="form-label">Data de Instalação</label>
                        <input type="date" class="form-control" name="data_instalacao" id="data_instalacao">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="data_proxima_manutencao" class="form-label">Data da Próxima Vistoria</label>
                    <input type="date" class="form-control" name="data_proxima_manutencao" id="data_proxima_manutencao">
                </div>

                <div class="alert alert-info" role="alert">
                    Para características específicas por tipo (ex: capacidade do extintor, classe de fogo), configure campos dinâmicos no backoffice após aplicar a migração da base de dados.
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="operacional">Operacional</option>
                        <option value="inservivel">Inservível</option>
                        <option value="aguardando_reparacao">Aguardando Reparação</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="3"></textarea>
                </div>
            </div>

            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Salvar</button>
                <a href="index.php?controler=equipamento&acao=listar" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>
