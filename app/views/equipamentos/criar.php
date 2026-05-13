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

                <div class="mb-3 p-3 border rounded bg-light">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_reserva" name="is_reserva" value="1">
                        <label class="form-check-label fw-bold" for="is_reserva">
                            <i class="bi bi-box-seam"></i> Equipamento de Reserva (stock)
                        </label>
                    </div>
                    <div class="form-text">
                        Marque para registar equipamentos em stock/reserva. Não recebem número de registo nem são alvo de inspeções calendarizadas, até serem colocados em serviço.
                    </div>
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

                <div class="row" id="bloco-numero-registo">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número de Registo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash"></i></span>
                            <div class="form-control text-muted" id="preview-numero-registo" style="background:#f8f9fa;">
                                Selecione um tipo de equipamento
                            </div>
                        </div>
                        <div id="bloco-intercalar" style="display:none;" class="mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="usar_intercalacao" name="usar_intercalacao" value="1">
                                <label class="form-check-label" for="usar_intercalacao">
                                    Intercalar — inserir em posição específica e avançar os seguintes
                                </label>
                            </div>
                            <div id="painel-intercalacao" class="mt-2" style="display:none;">
                                <input type="number" class="form-control" name="intercalar_posicao" id="intercalar_posicao" min="1" step="1" placeholder="Número da posição (ex: 3)">
                                <div class="form-text">O novo equipamento receberá esse número. Todos os seguintes avançam 1.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="localizacao" class="form-label"><span id="label-localizacao-asterisco">Localização *</span></label>
                        <input type="text" class="form-control" name="localizacao" id="localizacao" required>
                        <div class="form-text" id="hint-localizacao-reserva" style="display:none;">
                            Opcional para reservas. Se vazio, será gravado como "Reserva".
                        </div>
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
    const blocoCampos = document.getElementById('bloco-campos-dinamicos');
    const grupos = document.querySelectorAll('.campos-tipo');
    const previewEl = document.getElementById('preview-numero-registo');
    const blocoIntercalar = document.getElementById('bloco-intercalar');
    const usarIntercalacao = document.getElementById('usar_intercalacao');
    const painelIntercalacao = document.getElementById('painel-intercalacao');
    const inputPosicao = document.getElementById('intercalar_posicao');
    const checkReserva = document.getElementById('is_reserva');
    const blocoNumeroRegisto = document.getElementById('bloco-numero-registo');
    const inputLocalizacao = document.getElementById('localizacao');
    const labelLocAsterisco = document.getElementById('label-localizacao-asterisco');
    const hintLocReserva = document.getElementById('hint-localizacao-reserva');

    let prefixoAtual = '';

    function mostrarPreview(texto, bold) {
        previewEl.textContent = texto;
        if (bold) {
            previewEl.classList.remove('text-muted');
            previewEl.classList.add('fw-bold', 'text-dark');
        } else {
            previewEl.classList.add('text-muted');
            previewEl.classList.remove('fw-bold', 'text-dark');
        }
    }

    function previewComPosicao() {
        const pos = parseInt(inputPosicao.value, 10);
        if (prefixoAtual && pos > 0) {
            mostrarPreview(prefixoAtual + '-' + String(pos).padStart(3, '0'), true);
        } else {
            mostrarPreview('Introduza a posição', false);
        }
    }

    function atualizarPreviewNumero(tipoId) {
        if (!tipoId) {
            mostrarPreview('Selecione um tipo de equipamento', false);
            blocoIntercalar.style.display = 'none';
            usarIntercalacao.checked = false;
            painelIntercalacao.style.display = 'none';
            prefixoAtual = '';
            return;
        }

        blocoIntercalar.style.display = 'block';

        if (usarIntercalacao.checked && inputPosicao.value) {
            previewComPosicao();
            return;
        }

        mostrarPreview('A calcular...', false);

        fetch('index.php?controler=equipamento&acao=previewNumero&tipo_id=' + encodeURIComponent(tipoId))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.numero) {
                    prefixoAtual = data.numero.split('-').slice(0, -1).join('-');
                    mostrarPreview(data.numero, true);
                } else {
                    mostrarPreview('Prefixo não configurado', false);
                }
            })
            .catch(function () {
                mostrarPreview('Gerado ao guardar', false);
            });
    }

    usarIntercalacao.addEventListener('change', function () {
        painelIntercalacao.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            inputPosicao.value = '';
            atualizarPreviewNumero(selectTipo.value);
        } else {
            previewComPosicao();
        }
    });

    inputPosicao.addEventListener('input', previewComPosicao);

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

    selectTipo.addEventListener('change', function () {
        usarIntercalacao.checked = false;
        painelIntercalacao.style.display = 'none';

    function aplicarModoReserva() {
        const reserva = checkReserva.checked;
        blocoNumeroRegisto.style.display = reserva ? 'none' : '';
        inputLocalizacao.required = !reserva;
        labelLocAsterisco.textContent = reserva ? 'Localização' : 'Localização *';
        hintLocReserva.style.display = reserva ? 'block' : 'none';
        if (reserva) {
            usarIntercalacao.checked = false;
            painelIntercalacao.style.display = 'none';
            inputPosicao.value = '';
        }
    }
    checkReserva.addEventListener('change', aplicarModoReserva);
    aplicarModoReserva();

        inputPosicao.value = '';
        prefixoAtual = '';
        atualizarCamposDinamicos();
        atualizarPreviewNumero(selectTipo.value);
    });
    atualizarCamposDinamicos();
    atualizarPreviewNumero(selectTipo.value);
});
</script>
