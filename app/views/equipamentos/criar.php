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
                            <option value="<?php echo $tipo['id']; ?>" data-prefixo="<?php echo htmlspecialchars((string)($tipo['prefixo_numeracao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $tipo['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3" id="bloco-campos-dinamicos" style="display: none;">
                    <h5 class="mb-3">Características Específicas do Tipo</h5>
                    <?php foreach ($camposDinamicosPorTipo as $tipoId => $campos): ?>
                        <div class="row g-3 campos-tipo" data-tipo-id="<?php echo $tipoId; ?>" style="display: none;">
                            <?php foreach ($campos as $campo): ?>
                                <div class="col-md-6">
                                    <label class="form-label" for="campo_<?php echo $campo['id']; ?>">
                                        <?php echo $campo['nome_campo']; ?><?php echo (int)$campo['obrigatorio'] === 1 ? ' *' : ''; ?>
                                    </label>
                                    <?php if ($campo['tipo_dado'] === 'data'): ?>
                                        <input
                                            type="date"
                                            class="form-control campo-dinamico-input"
                                            id="campo_<?php echo $campo['id']; ?>"
                                            name="campos_dinamicos[<?php echo $campo['id']; ?>]"
                                            data-obrigatorio="<?php echo (int)$campo['obrigatorio']; ?>"
                                        >
                                    <?php elseif ($campo['tipo_dado'] === 'numero'): ?>
                                        <div class="input-group">
                                            <input
                                                type="number"
                                                step="0.01"
                                                class="form-control campo-dinamico-input"
                                                id="campo_<?php echo $campo['id']; ?>"
                                                name="campos_dinamicos[<?php echo $campo['id']; ?>]"
                                                data-obrigatorio="<?php echo (int)$campo['obrigatorio']; ?>"
                                            >
                                            <?php if (!empty($campo['unidade'])): ?>
                                                <span class="input-group-text"><?php echo $campo['unidade']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <input
                                            type="text"
                                            class="form-control campo-dinamico-input"
                                            id="campo_<?php echo $campo['id']; ?>"
                                            name="campos_dinamicos[<?php echo $campo['id']; ?>]"
                                            data-obrigatorio="<?php echo (int)$campo['obrigatorio']; ?>"
                                        >
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número de Registo</label>
                        <div class="form-control-plaintext bg-light border rounded px-3 py-2 text-muted" id="preview-numero-registo">
                            <i class="bi bi-hash"></i> Gerado automaticamente ao guardar
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="localizacao" class="form-label">Localização *</label>
                        <input type="text" class="form-control" name="localizacao" id="localizacao" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="numero_serie" class="form-label">Número de Série (Fabricante)</label>
                        <input type="text" class="form-control" name="numero_serie" id="numero_serie" placeholder="Ex: SN-20240001">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectTipo = document.getElementById('tipo_equipamento_id');
    const inputNumeroSerie = document.getElementById('numero_serie');
    const blocoCampos = document.getElementById('bloco-campos-dinamicos');
    const grupos = document.querySelectorAll('.campos-tipo');

    function sugerirNumeroSerie() {
        const optionSelecionada = selectTipo.options[selectTipo.selectedIndex];
        const prefixo = optionSelecionada ? String(optionSelecionada.getAttribute('data-prefixo') || '').trim() : '';
        const valorAtual = String(inputNumeroSerie.value || '').trim();
        const valorFoiSugerido = inputNumeroSerie.getAttribute('data-prefixo-sugerido') === '1';

        if (prefixo === '') {
            return;
        }

        if (valorAtual === '' || valorFoiSugerido) {
            inputNumeroSerie.value = prefixo + '-';
            inputNumeroSerie.setAttribute('data-prefixo-sugerido', '1');
        }
    }

    function atualizarCamposDinamicos() {
        const tipoSelecionado = selectTipo.value;
        let existeGrupoVisivel = false;

        grupos.forEach(function (grupo) {
            const visivel = grupo.getAttribute('data-tipo-id') === tipoSelecionado;
            grupo.style.display = visivel ? 'flex' : 'none';

            const inputs = grupo.querySelectorAll('.campo-dinamico-input');
            inputs.forEach(function (input) {
                const obrigatorio = input.getAttribute('data-obrigatorio') === '1';
                input.required = visivel && obrigatorio;
            });

            if (visivel) {
                existeGrupoVisivel = true;
            }
        });

        blocoCampos.style.display = existeGrupoVisivel ? 'block' : 'none';
    }

    inputNumeroSerie.addEventListener('input', function () {
        const valorAtual = String(inputNumeroSerie.value || '').trim();
        if (valorAtual === '') {
            inputNumeroSerie.setAttribute('data-prefixo-sugerido', '0');
            return;
        }

        inputNumeroSerie.setAttribute('data-prefixo-sugerido', valorAtual.endsWith('-') ? '1' : '0');
    });

    selectTipo.addEventListener('change', function () {
        atualizarCamposDinamicos();
        sugerirNumeroSerie();
    });
    atualizarCamposDinamicos();
    sugerirNumeroSerie();
});
</script>
