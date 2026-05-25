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
        'numero_registo' => (string)($equip['numero_registo'] ?? ''),
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

// Buscar valores de campos dinâmicos para os equipamentos visíveis (para tooltip)
$camposDinamicosPorEquip = [];
$idsEquipamentos = array_keys($equipamentosPayload);
if (!empty($idsEquipamentos)) {
    try {
        $db = new Database();
        $existe = $db->query("SHOW TABLES LIKE 'equipamentos_campos_valores'");
        if ($existe && $existe->num_rows > 0) {
            $idsSeguros = implode(',', array_map('intval', $idsEquipamentos));
            $sql = "SELECT ecv.equipamento_id, tec.nome_campo, tec.unidade, ecv.valor
                    FROM equipamentos_campos_valores ecv
                    JOIN tipos_equipamentos_campos tec ON tec.id = ecv.campo_id
                    WHERE ecv.equipamento_id IN ({$idsSeguros})
                      AND tec.ativo = TRUE
                    ORDER BY tec.ordem ASC, tec.nome_campo ASC";
            $res = $db->query($sql);
            if ($res) {
                foreach ($res->fetch_all(MYSQLI_ASSOC) as $r) {
                    $eid = (int)$r['equipamento_id'];
                    $valor = trim((string)$r['valor']);
                    if ($valor === '') {
                        continue;
                    }
                    $unidade = trim((string)($r['unidade'] ?? ''));
                    $camposDinamicosPorEquip[$eid][] = [
                        'nome' => (string)$r['nome_campo'],
                        'valor' => $valor . ($unidade !== '' ? ' ' . $unidade : ''),
                    ];
                }
            }
        }
    } catch (\Throwable $e) {
        // silencioso: tooltip continua sem campos dinâmicos
    }
}
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
            <a
                href="index.php?controler=equipamento&acao=etiquetas&amp;tipo=<?php echo $tipoAtual; ?>&amp;estado=<?php echo urlencode($estadoAtual); ?>&amp;localizacao=<?php echo urlencode($localizacaoAtual); ?>"
                class="btn btn-outline-primary"
                target="_blank"
                rel="noopener"
            >
                <i class="bi bi-printer"></i>
                Imprimir Etiquetas
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
                <div>
                    <a
                        href="index.php?controler=equipamento&acao=exportar_pdf&amp;tipo=<?php echo $tipoAtual; ?>&amp;estado=<?php echo urlencode($estadoAtual); ?>&amp;localizacao=<?php echo urlencode($localizacaoAtual); ?>&amp;ordenar=<?php echo urlencode($ordenarAtual); ?>&amp;direcao=<?php echo urlencode($direcaoAtual); ?>"
                        class="btn btn-outline-danger btn-sm"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($equipamentos as $equip): ?>
                    <?php
                        $tooltipLinhas = [];
                        $marca = trim((string)($equip['marca'] ?? ''));
                        $modelo = trim((string)($equip['modelo'] ?? ''));
                        $numSerie = trim((string)($equip['numero_serie'] ?? ''));
                        $estadoEq = trim((string)($equip['estado'] ?? ''));
                        $proxMan = trim((string)($equip['data_proxima_manutencao'] ?? ''));
                        $obs = trim((string)($equip['observacoes'] ?? ''));

                        if ($marca !== '' || $modelo !== '') {
                            $tooltipLinhas[] = '<strong>Marca/Modelo:</strong> ' . htmlspecialchars(trim($marca . ' ' . $modelo), ENT_QUOTES, 'UTF-8');
                        }
                        if ($numSerie !== '') {
                            $tooltipLinhas[] = '<strong>N.º Série:</strong> ' . htmlspecialchars($numSerie, ENT_QUOTES, 'UTF-8');
                        }
                        if ($estadoEq !== '') {
                            $tooltipLinhas[] = '<strong>Estado:</strong> ' . htmlspecialchars(ucfirst(str_replace('_', ' ', $estadoEq)), ENT_QUOTES, 'UTF-8');
                        }
                        if ($proxMan !== '' && $proxMan !== '0000-00-00') {
                            $partesData = explode('-', $proxMan);
                            $dataFmt = count($partesData) === 3 ? $partesData[2] . '/' . $partesData[1] . '/' . $partesData[0] : $proxMan;
                            $tooltipLinhas[] = '<strong>Próxima vistoria:</strong> ' . htmlspecialchars($dataFmt, ENT_QUOTES, 'UTF-8');
                        }

                        // Campos dinâmicos (ex.: capacidade, agente extintor)
                        $camposEquip = $camposDinamicosPorEquip[(int)$equip['id']] ?? [];
                        foreach ($camposEquip as $cd) {
                            $tooltipLinhas[] = '<strong>' . htmlspecialchars($cd['nome'], ENT_QUOTES, 'UTF-8') . ':</strong> '
                                . htmlspecialchars($cd['valor'], ENT_QUOTES, 'UTF-8');
                        }

                        if ($obs !== '') {
                            $obsCurta = mb_strlen($obs) > 120 ? mb_substr($obs, 0, 117) . '…' : $obs;
                            $tooltipLinhas[] = '<em>' . htmlspecialchars($obsCurta, ENT_QUOTES, 'UTF-8') . '</em>';
                        }

                        $tooltipHtml = empty($tooltipLinhas)
                            ? 'Sem características registadas'
                            : implode('<br>', $tooltipLinhas);
                    ?>
                    <button
                        type="button"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center js-abrir-equipamento"
                        data-equip-id="<?php echo (int)$equip['id']; ?>"
                        data-bs-toggle="tooltip"
                        data-bs-html="true"
                        data-bs-placement="left"
                        data-bs-custom-class="equipamento-tooltip"
                        title="<?php echo htmlspecialchars($tooltipHtml, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <span>
                            <strong><?php echo htmlspecialchars($equip['tipo_nome'] ?? 'Equipamento', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span class="text-muted"> - <?php echo htmlspecialchars($equip['localizacao'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                        <?php if ((int)($equip['is_reserva'] ?? 0) === 1): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-box-seam"></i> Reserva</span>
                        <?php else: ?>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($equip['numero_registo'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php if ($totalPaginas > 1): ?>
            <?php
                $queryPaginacao = array_merge($_GET, []);
                unset($queryPaginacao['pagina']);
                $queryBase = http_build_query($queryPaginacao);
                $inicio = max(1, $paginaAtual - 2);
                $fim = min($totalPaginas, $paginaAtual + 2);
            ?>
            <nav class="d-flex justify-content-between align-items-center px-3 py-3 border-top" aria-label="Paginacao">
                <small class="text-muted">
                    A mostrar <?php echo (int)(($paginaAtual - 1) * $porPagina + 1); ?>–<?php echo min((int)($paginaAtual * $porPagina), (int)$totalResultados); ?>
                    de <?php echo (int)$totalResultados; ?> resultado(s)
                </small>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?php echo $paginaAtual <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?<?php echo $queryBase; ?>&pagina=<?php echo $paginaAtual - 1; ?>">&laquo;</a>
                    </li>
                    <?php if ($inicio > 1): ?>
                        <li class="page-item"><a class="page-link" href="index.php?<?php echo $queryBase; ?>&pagina=1">1</a></li>
                        <?php if ($inicio > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($p = $inicio; $p <= $fim; $p++): ?>
                        <li class="page-item <?php echo $p === $paginaAtual ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?<?php echo $queryBase; ?>&pagina=<?php echo $p; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($fim < $totalPaginas): ?>
                        <?php if ($fim < $totalPaginas - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="index.php?<?php echo $queryBase; ?>&pagina=<?php echo $totalPaginas; ?>"><?php echo $totalPaginas; ?></a></li>
                    <?php endif; ?>
                    <li class="page-item <?php echo $paginaAtual >= $totalPaginas ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?<?php echo $queryBase; ?>&pagina=<?php echo $paginaAtual + 1; ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
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
                        <div class="mb-2"><strong>Numero de registo:</strong> <span id="modal-numero-registo" class="badge bg-primary">-</span></div>
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
                    <a id="modal-link-etiqueta" href="#" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                        <i class="bi bi-printer"></i> Etiqueta
                    </a>
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

    function normalizarTextoQr(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\x20-\x7E]/g, '')
            .trim();
    }

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
        document.getElementById('modal-numero-registo').textContent = valorOuTraco(equipamento.numero_registo);
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
        const linkEtiqueta = 'index.php?controler=equipamento&acao=etiquetas&id=' + id;

        document.getElementById('modal-link-ver').setAttribute('href', linkVer);
        document.getElementById('modal-link-editar').setAttribute('href', linkEditar);
        document.getElementById('modal-link-deletar').setAttribute('href', linkDeletar);
        document.getElementById('modal-link-etiqueta').setAttribute('href', linkEtiqueta);

        modalQr.innerHTML = '';
        const numeroSerie = normalizarTextoQr(valorOuTraco(equipamento.numero_registo));
        const localizacao = normalizarTextoQr(valorOuTraco(equipamento.localizacao));
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

    // Inicializar tooltips do Bootstrap para os itens da lista
    if (window.bootstrap && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el, { container: 'body', delay: { show: 250, hide: 100 } });
        });
    }

    if (autoAbrirEquipamentoId > 0) {
        abrirModalEquipamento(autoAbrirEquipamentoId);
    }
});
</script>

<style>
.equipamento-tooltip .tooltip-inner {
    max-width: 320px;
    text-align: left;
    padding: 0.6rem 0.75rem;
    background-color: #1f2937;
    color: #f9fafb;
    font-size: 0.85rem;
    line-height: 1.4;
}
.equipamento-tooltip.bs-tooltip-start .tooltip-arrow::before,
.equipamento-tooltip.bs-tooltip-auto[data-popper-placement^="left"] .tooltip-arrow::before {
    border-left-color: #1f2937;
}
</style>
