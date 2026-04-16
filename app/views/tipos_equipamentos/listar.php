<?php
$busca = $busca ?? '';
$mostrarAtual = $mostrar ?? 'todos';
?>

<section class="page-shell page-shell--narrow tipos-equipamentos-page">
    <header class="page-hero compact tipos-equipamentos-hero">
        <div>
            <span class="page-hero__eyebrow">Configuração</span>
            <h1><i class="bi bi-sliders"></i> Tipos de Equipamentos</h1>
            <p>
                Gerencie os tipos de equipamentos disponíveis no sistema, defina prefixos de numeração e frequências de inspeção.
            </p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=tipo_equipamento&acao=criar" class="btn btn-dashboard-primary">
                <i class="bi bi-plus-circle"></i>
                Novo Tipo
            </a>
        </div>
    </header>

    <section class="mini-stats-grid tipos-stats">
        <article class="mini-stat-card">
            <span>Total Registado</span>
            <strong><?php echo $totalResultados; ?></strong>
        </article>
    </section>

    <section class="panel-surface tipos-filter-panel">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Pesquisa</span>
                <h2>Filtrar tipos</h2>
            </div>
        </div>
        <form method="GET" action="index.php" class="modern-form tipos-filter-form">
            <input type="hidden" name="controler" value="tipo_equipamento">
            <input type="hidden" name="acao" value="listar">

            <div class="tipos-filter-grid" style="display: grid; grid-template-columns: 1fr auto auto; gap: 12px; align-items: end;">
                <input
                    type="text"
                    name="busca"
                    class="form-control"
                    placeholder="Pesquisar por nome..."
                    value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <select name="mostrar" class="form-select">
                    <option value="todos" <?php echo $mostrarAtual === 'todos' ? 'selected' : ''; ?>>Todos</option>
                    <option value="ativos" <?php echo $mostrarAtual === 'ativos' ? 'selected' : ''; ?>>Ativos</option>
                    <option value="inativos" <?php echo $mostrarAtual === 'inativos' ? 'selected' : ''; ?>>Inativos</option>
                </select>
                <button type="submit" class="btn btn-secondary">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </section>

    <section class="panel-surface tipos-list-panel">
        <div class="panel-surface__header compact">
            <span class="panel-surface__eyebrow">Resultados</span>
            <h2>
                Mostrando 
                <?php echo ($totalResultados > 0 ? (($paginaAtual - 1) * $porPagina + 1) : 0); ?>
                a 
                <?php echo min($paginaAtual * $porPagina, $totalResultados); ?>
                de <?php echo $totalResultados; ?> tipos
            </h2>
        </div>

        <?php if (empty($tipos)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">
                    <i class="bi bi-search"></i>
                </div>
                <h3>Nenhum tipo encontrado</h3>
                <p>Nenhum tipo de equipamento corresponde aos critérios de pesquisa.</p>
            </div>
        <?php else: ?>
            <div class="tipos-grid" style="display: grid; gap: 12px;">
                <?php foreach ($tipos as $tipo): ?>
                    <article class="tipo-card" style="display: grid; grid-template-columns: 1fr auto; gap: 16px; padding: 16px; border: 1px solid #e5e5e5; border-radius: 8px; align-items: center;">
                        <div>
                            <h3 style="margin: 0 0 4px 0; font-size: 16px;">
                                <i class="bi <?php echo htmlspecialchars($tipo['icone'] ?? 'bi-tools', ENT_QUOTES, 'UTF-8'); ?>" style="margin-right: 8px;"></i>
                                <a href="index.php?controler=tipo_equipamento&acao=ver&id=<?php echo (int)$tipo['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <?php echo htmlspecialchars($tipo['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h3>
                            
                            <?php if ($tipo['descricao']): ?>
                                <p style="margin: 4px 0; color: #666; font-size: 14px;">
                                    <?php echo htmlspecialchars(substr($tipo['descricao'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (strlen($tipo['descricao']) > 100): ?>...<?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <div style="display: flex; gap: 12px; margin-top: 8px; font-size: 13px; color: #999;">
                                <?php if ($tipo['prefixo_numeracao']): ?>
                                    <span><strong>Prefixo:</strong> <?php echo htmlspecialchars($tipo['prefixo_numeracao'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if ($tipo['frequencia_inspecao']): ?>
                                    <span><strong>Frequência:</strong> <?php echo (int)$tipo['frequencia_inspecao']; ?> dias</span>
                                <?php endif; ?>
                                <span><strong>Equipamentos:</strong> <?php echo (int)$tipo['total_equipamentos']; ?></span>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
                            <div style="display: flex; gap: 6px;">
                                <a href="index.php?controler=tipo_equipamento&acao=editar&id=<?php echo (int)$tipo['id']; ?>" 
                                   class="btn btn-sm" style="background: #0066cc; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 12px;">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <a href="index.php?controler=tipo_equipamento&acao=ver&id=<?php echo (int)$tipo['id']; ?>" 
                                   class="btn btn-sm" style="background: #6c757d; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 12px;">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <a href="index.php?controler=tipo_equipamento&acao=toggleAtivo&id=<?php echo (int)$tipo['id']; ?>" 
                                   class="btn btn-sm" style="background: <?php echo $tipo['ativo'] ? '#ffc107' : '#28a745'; ?>; color: <?php echo $tipo['ativo'] ? '#000' : 'white'; ?>; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 12px;"
                                   onclick="return confirm('Tem a certeza que deseja <?php echo $tipo['ativo'] ? 'inativar' : 'ativar'; ?> este tipo?');">
                                    <i class="bi <?php echo $tipo['ativo'] ? 'bi-pause-circle' : 'bi-play-circle'; ?>"></i> <?php echo $tipo['ativo'] ? 'Inativar' : 'Ativar'; ?>
                                </a>
                                <?php if ((int)$tipo['total_equipamentos'] === 0): ?>
                                    <a href="index.php?controler=tipo_equipamento&acao=deletar&id=<?php echo (int)$tipo['id']; ?>" 
                                       class="btn btn-sm" style="background: #dc3545; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 12px;"
                                       onclick="return confirm('Tem a certeza que deseja deletar este tipo? Esta ação não pode ser desfeita.');">
                                        <i class="bi bi-trash"></i> Deletar
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($tipo['ativo']): ?>
                                <span class="badge" style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <i class="bi bi-check-circle"></i> Ativo
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <i class="bi bi-x-circle"></i> Inativo
                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <nav class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 8px;">
                    <?php
                    $baseUrl = "index.php?controler=tipo_equipamento&acao=listar&mostrar={$mostrarAtual}";
                    if ($busca !== '') {
                        $baseUrl .= "&busca=" . urlencode($busca);
                    }
                    ?>
                    
                    <?php if ($paginaAtual > 1): ?>
                        <a href="<?php echo $baseUrl; ?>&pagina=1" class="btn btn-sm btn-secondary">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                        <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $paginaAtual - 1; ?>" class="btn btn-sm btn-secondary">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="font-size: 13px;">Página</span>
                        <input type="number" value="<?php echo $paginaAtual; ?>" min="1" max="<?php echo $totalPaginas; ?>" 
                            onchange="window.location.href='<?php echo $baseUrl; ?>&pagina=' + this.value"
                            style="width: 50px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                        <span style="font-size: 13px;">de <?php echo $totalPaginas; ?></span>
                    </div>

                    <?php if ($paginaAtual < $totalPaginas): ?>
                        <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $paginaAtual + 1; ?>" class="btn btn-sm btn-secondary">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $totalPaginas; ?>" class="btn btn-sm btn-secondary">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</section>
