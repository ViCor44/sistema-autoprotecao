<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-pencil"></i> Editar Equipamento</h1>

        <form method="POST" action="index.php?controler=equipamento&acao=atualizar&id=<?php echo $equipamento['id']; ?>" class="card">
            <div class="card-body">
                <!-- Código QR (Leitura apenas) -->
                <div class="alert alert-info mb-3" role="alert">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div id="qrcode-editar" style="width: 100px; height: 100px;"></div>
                        </div>
                        <div class="col">
                            <strong>Código QR do Equipamento</strong>
                            <p class="mb-0 small text-muted">QR com numero de registo e localizacao do equipamento.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tipo_equipamento_id" class="form-label">Tipo de Equipamento *</label>
                    <select name="tipo_equipamento_id" id="tipo_equipamento_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>" data-prefixo="<?php echo htmlspecialchars((string)($tipo['prefixo_numeracao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?php echo $tipo['id'] === $equipamento['tipo_equipamento_id'] ? 'selected' : ''; ?>><?php echo $tipo['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3" id="bloco-campos-dinamicos" style="display: none;">
                    <h5 class="mb-3">Características Específicas do Tipo</h5>
                    <?php foreach ($camposDinamicosPorTipo as $tipoId => $campos): ?>
                        <div class="row g-3 campos-tipo" data-tipo-id="<?php echo $tipoId; ?>" style="display: none;">
                            <?php foreach ($campos as $campo): ?>
                                <?php $valorCampo = $valoresCamposDinamicos[$campo['id']] ?? ''; ?>
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
                                            value="<?php echo $valorCampo; ?>"
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
                                                value="<?php echo $valorCampo; ?>"
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
                                            value="<?php echo htmlspecialchars((string)$valorCampo); ?>"
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
                        <div class="form-control-plaintext bg-light border rounded px-3 py-2">
                            <strong><?php echo htmlspecialchars($equipamento['numero_registo'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="localizacao" class="form-label">Localização *</label>
                        <input type="text" class="form-control" name="localizacao" id="localizacao" value="<?php echo htmlspecialchars($equipamento['localizacao'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="numero_serie" class="form-label">Número de Série (Fabricante)</label>
                        <input type="text" class="form-control" name="numero_serie" id="numero_serie" value="<?php echo htmlspecialchars($equipamento['numero_serie'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" name="marca" id="marca" value="<?php echo $equipamento['marca'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="modelo" class="form-label">Modelo</label>
                        <input type="text" class="form-control" name="modelo" id="modelo" value="<?php echo $equipamento['modelo'] ?? ''; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="data_aquisicao" class="form-label">Data de Aquisição</label>
                        <input type="date" class="form-control" name="data_aquisicao" id="data_aquisicao" value="<?php echo $equipamento['data_aquisicao'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="data_instalacao" class="form-label">Data de Instalação</label>
                        <input type="date" class="form-control" name="data_instalacao" id="data_instalacao" value="<?php echo $equipamento['data_instalacao'] ?? ''; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="data_proxima_manutencao" class="form-label">Data da Próxima Vistoria</label>
                    <input type="date" class="form-control" name="data_proxima_manutencao" id="data_proxima_manutencao" value="<?php echo $equipamento['data_proxima_manutencao'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="operacional" <?php echo $equipamento['estado'] === 'operacional' ? 'selected' : ''; ?>>Operacional</option>
                        <option value="inservivel" <?php echo $equipamento['estado'] === 'inservivel' ? 'selected' : ''; ?>>Inservível</option>
                        <option value="aguardando_reparacao" <?php echo $equipamento['estado'] === 'aguardando_reparacao' ? 'selected' : ''; ?>>Aguardando Reparação</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="3"><?php echo $equipamento['observacoes'] ?? ''; ?></textarea>
                </div>
            </div>

            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Atualizar</button>
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

    selectTipo.addEventListener('change', atualizarCamposDinamicos);
    atualizarCamposDinamicos();
});
</script>

<!-- Script para gerar o código QR -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function normalizarTextoQr(texto) {
            return String(texto || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^\x20-\x7E]/g, '')
                .trim();
        }

        const numeroSerie = <?php echo json_encode((string)($equipamento['numero_registo'] ?? ''), JSON_UNESCAPED_UNICODE); ?>;
        const localizacao = <?php echo json_encode((string)($equipamento['localizacao'] ?? ''), JSON_UNESCAPED_UNICODE); ?>;
        const qrPayload = 'NR=' + normalizarTextoQr(numeroSerie) + ';LOC=' + normalizarTextoQr(localizacao);
        
        new QRCode(document.getElementById('qrcode-editar'), {
            text: qrPayload,
            width: 100,
            height: 100,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>
