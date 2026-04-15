<?php
$totalEquipamentos = (int)($resumo['total'] ?? 0);
$operacionais = (int)($resumo['operacionais'] ?? 0);
$emFalha = (int)($resumo['anomalias'] ?? 0);
$localizacaoAtual = $filtros['localizacao'] ?? '';
$tipoAtual = (int)($filtros['tipo_equipamento_id'] ?? 0);
$estadoAtual = $filtros['estado'] ?? '';
$ordenarAtual = $ordenar ?? 'tipo_nome';
$direcaoAtual = strtolower($direcao ?? 'asc');
$resultadosNaPagina = count($equipamentos);
$inicioPagina = $totalResultados > 0 ? ($offset + 1) : 0;
$fimPagina = $offset + $resultadosNaPagina;

$queryBase = [
    'controler' => 'equipamento',
    'acao' => 'listar',
];

if ($localizacaoAtual !== '') {
    $queryBase['localizacao'] = $localizacaoAtual;
}

if ($tipoAtual > 0) {
    $queryBase['tipo'] = $tipoAtual;
}

if ($estadoAtual !== '') {
    $queryBase['estado'] = $estadoAtual;
}

$queryBase['ordenar'] = $ordenarAtual;
$queryBase['direcao'] = $direcaoAtual;
?>

<section class="page-shell page-shell--narrow equipamentos-page">
    <header class="page-hero compact equipamentos-hero">
        <div>
            <span class="page-hero__eyebrow">Inventario Tecnico</span>
            <h1><i class="bi bi-tools"></i> Equipamentos</h1>
            <p>
                Consulte o estado operacional de cada equipamento, filtre por tipo, estado e localizacao, e mantenha o parque de autoprotecao sempre atualizado.
            </p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=equipamento&acao=criar" class="btn btn-dashboard-primary">
                <i class="bi bi-plus-circle"></i>
                Novo Equipamento
            </a>
        </div>
    </header>

    <section class="mini-stats-grid equipamentos-stats">
        <article class="mini-stat-card">
            <span>Total Registados</span>
            <strong><?php echo $totalEquipamentos; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Operacionais</span>
            <strong><?php echo $operacionais; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Com anomalias</span>
            <strong><?php echo $emFalha; ?></strong>
        </article>
    </section>

    <section class="panel-surface equipamentos-filter-panel">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Pesquisa</span>
                <h2>Filtrar equipamentos</h2>
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
                </select>
                <select name="ordenar" class="form-select" aria-label="Ordenar por">
                    <option value="tipo_nome" <?php echo $ordenarAtual === 'tipo_nome' ? 'selected' : ''; ?>>Ordenar: Tipo</option>
                    <option value="localizacao" <?php echo $ordenarAtual === 'localizacao' ? 'selected' : ''; ?>>Ordenar: Localizacao</option>
                    <option value="estado" <?php echo $ordenarAtual === 'estado' ? 'selected' : ''; ?>>Ordenar: Estado</option>
                    <option value="proxima_manutencao" <?php echo $ordenarAtual === 'proxima_manutencao' ? 'selected' : ''; ?>>Ordenar: Proxima manutencao</option>
                </select>
                <select name="direcao" class="form-select" aria-label="Direção da ordenação">
                    <option value="asc" <?php echo $direcaoAtual === 'asc' ? 'selected' : ''; ?>>Ascendente</option>
                    <option value="desc" <?php echo $direcaoAtual === 'desc' ? 'selected' : ''; ?>>Descendente</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="index.php?controler=equipamento&acao=listar" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </section>

    <section class="equipamentos-results-meta">
        <strong><?php echo $totalResultados; ?></strong>
        resultados
        <?php if ($totalResultados > 0): ?>
            <span>(a mostrar <?php echo $inicioPagina; ?>-<?php echo $fimPagina; ?>)</span>
        <?php endif; ?>
    </section>

    <?php if (empty($equipamentos)): ?>
        <section class="panel-surface">
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon">
                    <i class="bi bi-inboxes"></i>
                </div>
                <div>
                    <strong>Nenhum equipamento encontrado</strong>
                    <p>Registe o primeiro equipamento ou ajuste os filtros para visualizar resultados.</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="equipamentos-grid">
            <?php foreach ($equipamentos as $equip): ?>
                <?php
                $estado = $equip['estado'] ?? '';
                $estadoClass = $estado === 'operacional' ? 'status-pill--success' : 'status-pill--warning';
                $proximaManutencao = $equip['data_proxima_manutencao'] ?? null;
                $proximaManutencaoLabel = '-';
                if (!empty($proximaManutencao) && $proximaManutencao !== '0000-00-00') {
                    $timestamp = strtotime($proximaManutencao);
                    if ($timestamp !== false) {
                        $proximaManutencaoLabel = date('d/m/Y', $timestamp);
                    }
                }
                ?>
                <article class="equipamento-card">
                    <div class="equipamento-card__head">
                        <h3><?php echo htmlspecialchars($equip['tipo_nome'] ?? 'Equipamento', ENT_QUOTES, 'UTF-8'); ?></h3>
                        <span class="status-pill <?php echo $estadoClass; ?>">
                            <?php echo htmlspecialchars(ucfirst($estado ?: 'indefinido'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <div class="equipamento-card__body">
                        <div class="detail-row">
                            <span>Localizacao</span>
                            <strong><?php echo htmlspecialchars($equip['localizacao'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Numero de registo</span>
                            <strong><?php echo htmlspecialchars($equip['numero_serie'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Marca e modelo</span>
                            <strong>
                                <?php echo htmlspecialchars($equip['marca'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> /
                                <?php echo htmlspecialchars($equip['modelo'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                            </strong>
                        </div>
                        <div class="detail-row">
                            <span>Proxima manutencao</span>
                            <strong><?php echo htmlspecialchars($proximaManutencaoLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                    </div>

                    <footer class="equipamento-card__actions">
                        <a href="index.php?controler=equipamento&acao=ver&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                        <a href="index.php?controler=equipamento&acao=editar&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="index.php?controler=equipamento&acao=deletar&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem a certeza?');">Deletar</a>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>

        <?php if ($totalPaginas > 1): ?>
            <nav aria-label="Paginação de equipamentos" class="equipamentos-pagination-wrap">
                <ul class="pagination equipamentos-pagination">
                    <?php
                    $queryAnterior = $queryBase;
                    $queryAnterior['pagina'] = max(1, $paginaAtual - 1);
                    $linkAnterior = 'index.php?' . http_build_query($queryAnterior);

                    $querySeguinte = $queryBase;
                    $querySeguinte['pagina'] = min($totalPaginas, $paginaAtual + 1);
                    $linkSeguinte = 'index.php?' . http_build_query($querySeguinte);
                    ?>
                    <li class="page-item <?php echo $paginaAtual <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo htmlspecialchars($linkAnterior, ENT_QUOTES, 'UTF-8'); ?>">Anterior</a>
                    </li>

                    <?php
                    $inicio = max(1, $paginaAtual - 2);
                    $fim = min($totalPaginas, $paginaAtual + 2);

                    for ($pagina = $inicio; $pagina <= $fim; $pagina++):
                        $queryPagina = $queryBase;
                        $queryPagina['pagina'] = $pagina;
                        $linkPagina = 'index.php?' . http_build_query($queryPagina);
                    ?>
                        <li class="page-item <?php echo $pagina === $paginaAtual ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars($linkPagina, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $pagina; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo $paginaAtual >= $totalPaginas ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo htmlspecialchars($linkSeguinte, ENT_QUOTES, 'UTF-8'); ?>">Seguinte</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
