<?php
$totalAgendamentos = count($agendamentos);
$pendentes = 0;
$concluidos = 0;
$urgentes = 0;

foreach ($agendamentos as $agendamento) {
    if (($agendamento['status'] ?? '') === 'concluido') {
        $concluidos++;
    } else {
        $pendentes++;
    }

    if (($agendamento['prioridade'] ?? '') === 'urgente') {
        $urgentes++;
    }
}
?>

<section class="page-shell page-shell--narrow">
    <header class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Planeamento</span>
            <h1><i class="bi bi-calendar3"></i> Agendamentos de Inspecao</h1>
            <p>Controle o plano de inspecoes, filtre por estado, prioridade e tipo de equipamento, e acompanhe o ritmo operacional da equipa.</p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=calendario&acao=agendar" class="btn btn-dashboard-primary">
                <i class="bi bi-calendar-plus"></i>
                Novo Agendamento
            </a>
        </div>
    </header>

    <section class="mini-stats-grid">
        <article class="mini-stat-card">
            <span>Total de agendamentos</span>
            <strong><?php echo $totalAgendamentos; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Pendentes</span>
            <strong><?php echo $pendentes; ?></strong>
        </article>
        <article class="mini-stat-card">
            <span>Urgentes</span>
            <strong><?php echo $urgentes; ?></strong>
        </article>
    </section>

    <section class="panel-surface">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Filtros</span>
                <h2>Refinar listagem</h2>
            </div>
        </div>

        <form method="GET" action="index.php" class="modern-form">
            <input type="hidden" name="controler" value="calendario">
            <input type="hidden" name="acao" value="listar">

            <div class="equipamentos-filter-grid">
                <select name="status" class="form-select">
                    <option value="">Todos os estados</option>
                    <option value="agendado" <?php echo ($statusFiltro ?? '') === 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                    <option value="em_progresso" <?php echo ($statusFiltro ?? '') === 'em_progresso' ? 'selected' : ''; ?>>Em progresso</option>
                    <option value="concluido" <?php echo ($statusFiltro ?? '') === 'concluido' ? 'selected' : ''; ?>>Concluido</option>
                    <option value="cancelado" <?php echo ($statusFiltro ?? '') === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>

                <select name="tipo" class="form-select">
                    <option value="0">Todos os tipos</option>
                    <?php foreach (($tiposEquipamentos ?? []) as $tipo): ?>
                        <option value="<?php echo (int)$tipo['id']; ?>" <?php echo ((int)($tipoFiltro ?? 0) === (int)$tipo['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="prioridade" class="form-select">
                    <option value="">Todas as prioridades</option>
                    <option value="normal" <?php echo ($prioridadeFiltro ?? '') === 'normal' ? 'selected' : ''; ?>>Normal</option>
                    <option value="alta" <?php echo ($prioridadeFiltro ?? '') === 'alta' ? 'selected' : ''; ?>>Alta</option>
                    <option value="urgente" <?php echo ($prioridadeFiltro ?? '') === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                </select>

                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="index.php?controler=calendario&acao=listar" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </section>

    <section class="panel-surface">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Listagem</span>
                <h2>Agenda de inspecoes</h2>
            </div>
            <a href="index.php?controler=calendario&acao=calendario" class="dashboard-panel__link">Ver calendario mensal</a>
        </div>

        <?php if (empty($agendamentos)): ?>
            <div class="dashboard-empty-state">
                <div class="dashboard-empty-state__icon">
                    <i class="bi bi-calendar2-x"></i>
                </div>
                <div>
                    <strong>Sem agendamentos para os filtros escolhidos</strong>
                    <p>Crie um novo agendamento ou ajuste os filtros para visualizar o planeamento.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="modern-table-wrap">
                <table class="table modern-table align-middle">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Ambito</th>
                            <th>Inspecao</th>
                            <th>Prioridade</th>
                            <th>Estado</th>
                            <th>Responsavel</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos as $ag): ?>
                            <?php
                            $prioridade = $ag['prioridade'] ?? 'normal';
                            $status = $ag['status'] ?? 'agendado';

                            $prioridadeClass = 'status-pill--info';
                            if ($prioridade === 'urgente') {
                                $prioridadeClass = 'status-pill--warning';
                            } elseif ($prioridade === 'alta') {
                                $prioridadeClass = 'status-pill--warning';
                            }

                            $statusClass = 'status-pill--info';
                            if ($status === 'concluido') {
                                $statusClass = 'status-pill--success';
                            } elseif ($status === 'cancelado') {
                                $statusClass = 'status-pill--warning';
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($ag['data_inspecao'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($ag['tipo_equipamento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if (!empty($ag['equipamento_id'])): ?>
                                        Equipamento: <?php echo htmlspecialchars($ag['localizacao'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                    <?php else: ?>
                                        Todos do tipo
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $ag['tipo_inspecao'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="status-pill <?php echo $prioridadeClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst($prioridade), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($ag['responsavel_nome'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="index.php?controler=calendario&acao=ver&id=<?php echo (int)$ag['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
