<?php
$localizacaoAtual = $filtros['localizacao'] ?? '';
$tipoAtual = (int)($filtros['tipo_equipamento_id'] ?? 0);
$estadoAtual = $filtros['estado'] ?? '';
$ordenarAtual = $ordenar ?? 'tipo_nome';
$direcaoAtual = strtolower($direcao ?? 'asc');
$temPesquisaAtiva = ($localizacaoAtual !== '') || ($tipoAtual > 0) || ($estadoAtual !== '');
$autoAbrirEquipamentoId = isset($autoAbrirEquipamentoId) ? (int)$autoAbrirEquipamentoId : 0;

$equipamentosPayload = [];
foreach ($equipamentos as $equip) {
    $id = (int)($equip['id'] ?? 0);
    if ($id <= 0) {
        continue;
    }

    $equipamentosPayload[$id] = [
        'id' => $id,
        'tipo_nome' => (string)($equip['tipo_nome'] ?? ''),
        'estado' => (string)($equip['estado'] ?? ''),
        'localizacao' => (string)($equip['localizacao'] ?? ''),
        'numero_serie' => (string)($equip['numero_serie'] ?? ''),
        'marca' => (string)($equip['marca'] ?? ''),
        'modelo' => (string)($equip['modelo'] ?? ''),
        'data_aquisicao' => (string)($equip['data_aquisicao'] ?? ''),
        'data_instalacao' => (string)($equip['data_instalacao'] ?? ''),
        'data_proxima_manutencao' => (string)($equip['data_proxima_manutencao'] ?? ''),
        'observacoes' => (string)($equip['observacoes'] ?? ''),
    ];
}

$equipamentosJson = json_encode($equipamentosPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
?>

<section class="page-shell page-shell--narrow equipamentos-page">
    <header class="page-hero compact equipamentos-hero">
        <div>
            <span class="page-hero__eyebrow">Inventario Tecnico</span>
            <h1><i class="bi bi-search"></i> Pesquisa de Equipamentos</h1>
            <p>
                Pesquise por localizacao, tipo e estado. Ao pesquisar, os detalhes do equipamento abrem em modal com QR grande e acoes rapidas.
            </p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=equipamento&acao=criar" class="btn btn-dashboard-primary">
                <i class="bi bi-plus-circle"></i>
                Novo Equipamento
            </a>
        </div>
    </header>

    <section class="panel-surface equipamentos-filter-panel">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Pesquisa</span>
                <h2>Encontrar equipamento</h2>
            </div>
        </div>
        <form method="GET" action="index.php" class="modern-form equipamentos-filter-form">
            <input type="hidden" name="controler" value="equipamento">
            <input type="hidden" name="acao" value="listar">

            <div class="equipamentos-filter-grid">
                <input
                    type="text"
                    name="localizacao"
                    class="form-control"
                    placeholder="Ex.: Cozinha, Sala tecnica, Armazem ou numero de registo..."
                    value="<?php echo htmlspecialchars($localizacaoAtual, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <select name="tipo" class="form-select">
                    <option value="0">Todos os tipos</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?php echo (int)$tipo['id']; ?>" <?php echo $tipoAtual === (int)$tipo['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="estado" class="form-select">
                    <option value="">Todos os estados</option>
                    <option value="operacional" <?php echo $estadoAtual === 'operacional' ? 'selected' : ''; ?>>Operacional</option>
                    <option value="inoperacional" <?php echo $estadoAtual === 'inoperacional' ? 'selected' : ''; ?>>Inoperacional</option>
                    <option value="avariado" <?php echo $estadoAtual === 'avariado' ? 'selected' : ''; ?>>Avariado</option>
                    <option value="inservivel" <?php echo $estadoAtual === 'inservivel' ? 'selected' : ''; ?>>Inservivel</option>
                    <option value="aguardando_reparacao" <?php echo $estadoAtual === 'aguardando_reparacao' ? 'selected' : ''; ?>>Aguardando reparacao</option>
                </select>
                <select name="ordenar" class="form-select" aria-label="Ordenar por">
                    <option value="tipo_nome" <?php echo $ordenarAtual === 'tipo_nome' ? 'selected' : ''; ?>>Ordenar: Tipo</option>
                    <option value="localizacao" <?php echo $ordenarAtual === 'localizacao' ? 'selected' : ''; ?>>Ordenar: Localizacao</option>
                    <option value="estado" <?php echo $ordenarAtual === 'estado' ? 'selected' : ''; ?>>Ordenar: Estado</option>
                    <option value="proxima_manutencao" <?php echo $ordenarAtual === 'proxima_manutencao' ? 'selected' : ''; ?>>Ordenar: Proxima manutencao</option>
                </select>
                <select name="direcao" class="form-select" aria-label="Direcao da ordenacao">
                    <option value="asc" <?php echo $direcaoAtual === 'asc' ? 'selected' : ''; ?>>Ascendente</option>
                    <option value="desc" <?php echo $direcaoAtual === 'desc' ? 'selected' : ''; ?>>Descendente</option>
                </select>
                <button type="submit" class="btn btn-primary">Pesquisar</button>
                <a href="index.php?controler=equipamento&acao=listar" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </section>

    <?php if (!$temPesquisaAtiva): ?>
        <section class="panel-surface">
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon">
                    <i class="bi bi-search"></i>
                </div>
                <div>
                    <strong>Inicie uma pesquisa para abrir o modal do equipamento</strong>
                    <p>Use os filtros acima. Se houver resultados, pode abrir os detalhes por modal.</p>
                </div>
            </div>
        </section>
    <?php elseif (empty($equipamentos)): ?>
        <section class="panel-surface">
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon">
                    <i class="bi bi-inboxes"></i>
                </div>
                <div>
                    <strong>Nenhum equipamento encontrado</strong>
                    <p>Ajuste os filtros para encontrar um equipamento.</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="panel-surface">
            <div class="panel-surface__header compact">
                <div>
                    <span class="panel-surface__eyebrow">Resultados</span>
                    <h2><?php echo (int)$totalResultados; ?> equipamento(s) encontrado(s)</h2>
                    <p class="text-muted mb-0">Clique num equipamento da lista para abrir o detalhe em modal.</p>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($equipamentos as $equip): ?>
                    <button
                        type="button"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center js-abrir-equipamento"
                        data-equip-id="<?php echo (int)$equip['id']; ?>"
                    >
                        <span>
                            <strong><?php echo htmlspecialchars($equip['tipo_nome'] ?? 'Equipamento', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span class="text-muted"> - <?php echo htmlspecialchars($equip['localizacao'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($equip['numero_serie'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

<div class="modal fade" id="equipamentoDetalheModal" tabindex="-1" aria-labelledby="equipamentoDetalheModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="equipamentoDetalheModalLabel">Detalhes do Equipamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 align-items-start">
                    <div class="col-md-5 text-center">
                        <div id="equipamento-modal-qr" class="d-inline-block"></div>
                        <p class="small text-muted mt-2 mb-0">QR com numero de registo e localizacao</p>
                    </div>
                    <div class="col-md-7">
                        <div class="mb-2"><strong>Tipo:</strong> <span id="modal-tipo">-</span></div>
                        <div class="mb-2"><strong>Estado:</strong> <span id="modal-estado" class="badge bg-secondary">-</span></div>
                        <div class="mb-2"><strong>Localizacao:</strong> <span id="modal-localizacao">-</span></div>
                        <div class="mb-2"><strong>Numero de registo:</strong> <span id="modal-numero-serie">-</span></div>
                        <div class="mb-2"><strong>Marca:</strong> <span id="modal-marca">-</span></div>
                        <div class="mb-2"><strong>Modelo:</strong> <span id="modal-modelo">-</span></div>
                        <div class="mb-2"><strong>Data de aquisicao:</strong> <span id="modal-data-aquisicao">-</span></div>
                        <div class="mb-2"><strong>Data de instalacao:</strong> <span id="modal-data-instalacao">-</span></div>
                        <div class="mb-2"><strong>Proxima vistoria:</strong> <span id="modal-data-proxima">-</span></div>
                        <div class="mb-2"><strong>Observacoes:</strong></div>
                        <div id="modal-observacoes" class="p-2 bg-light rounded small">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <a id="modal-link-ver" href="#" class="btn btn-primary">Ver completo</a>
                </div>
                <div class="d-flex gap-2">
                    <a id="modal-link-editar" href="#" class="btn btn-warning">Editar</a>
                    <a id="modal-link-deletar" href="#" class="btn btn-danger" onclick="return confirm('Tem a certeza?');">Eliminar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const equipamentos = <?php echo $equipamentosJson; ?>;
    const modalEl = document.getElementById('equipamentoDetalheModal');
    const modalQr = document.getElementById('equipamento-modal-qr');
    const btnAbrir = document.querySelectorAll('.js-abrir-equipamento');
    const autoAbrirEquipamentoId = <?php echo (int)$autoAbrirEquipamentoId; ?>;

    function valorOuTraco(valor) {
        return valor && String(valor).trim() !== '' ? String(valor) : '-';
    }

    function formatarData(dataIso) {
        if (!dataIso || dataIso === '0000-00-00') {
            return '-';
        }

        const partes = String(dataIso).split('-');
        if (partes.length !== 3) {
            return String(dataIso);
        }

        const ano = parseInt(partes[0], 10);
        if (Number.isNaN(ano) || ano <= 1) {
            return '-';
        }

        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    function atualizarEstado(estado) {
        const estadoEl = document.getElementById('modal-estado');
        const texto = valorOuTraco(estado);
        estadoEl.textContent = texto.charAt(0).toUpperCase() + texto.slice(1);
        estadoEl.className = 'badge ' + (estado === 'operacional' ? 'bg-success' : 'bg-danger');
    }

    function abrirModalEquipamento(id) {
        const equipamento = equipamentos[id];
        if (!equipamento || !window.bootstrap) {
            return;
        }

        document.getElementById('modal-tipo').textContent = valorOuTraco(equipamento.tipo_nome);
        document.getElementById('modal-localizacao').textContent = valorOuTraco(equipamento.localizacao);
        document.getElementById('modal-numero-serie').textContent = valorOuTraco(equipamento.numero_serie);
        document.getElementById('modal-marca').textContent = valorOuTraco(equipamento.marca);
        document.getElementById('modal-modelo').textContent = valorOuTraco(equipamento.modelo);
        document.getElementById('modal-data-aquisicao').textContent = formatarData(equipamento.data_aquisicao);
        document.getElementById('modal-data-instalacao').textContent = formatarData(equipamento.data_instalacao);
        document.getElementById('modal-data-proxima').textContent = formatarData(equipamento.data_proxima_manutencao);
        document.getElementById('modal-observacoes').textContent = valorOuTraco(equipamento.observacoes);
        atualizarEstado(equipamento.estado);

        const linkVer = 'index.php?controler=equipamento&acao=ver&id=' + id;
        const linkEditar = 'index.php?controler=equipamento&acao=editar&id=' + id;
        const linkDeletar = 'index.php?controler=equipamento&acao=deletar&id=' + id;

        document.getElementById('modal-link-ver').setAttribute('href', linkVer);
        document.getElementById('modal-link-editar').setAttribute('href', linkEditar);
        document.getElementById('modal-link-deletar').setAttribute('href', linkDeletar);

        modalQr.innerHTML = '';
        const numeroSerie = valorOuTraco(equipamento.numero_serie);
        const localizacao = valorOuTraco(equipamento.localizacao);
        const qrPayload = 'NR=' + numeroSerie + ';LOC=' + localizacao;

        new QRCode(modalQr, {
            text: qrPayload,
            width: 220,
            height: 220,
            colorDark: '#111111',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    btnAbrir.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = parseInt(btn.getAttribute('data-equip-id'), 10);
            if (!Number.isNaN(id)) {
                abrirModalEquipamento(id);
            }
        });
    });

    if (autoAbrirEquipamentoId > 0) {
        abrirModalEquipamento(autoAbrirEquipamentoId);
    }
});
</script>
