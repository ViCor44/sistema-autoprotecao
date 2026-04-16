<?php
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Marco', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
$diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
$primeiroDiaMes = mktime(0, 0, 0, $mes, 1, $ano);
$diasMes = (int)date('t', $primeiroDiaMes);
$diaInicio = (int)date('w', $primeiroDiaMes);
$mesAnteriorTs = mktime(0, 0, 0, $mes - 1, 1, $ano);
$mesSeguinteTs = mktime(0, 0, 0, $mes + 1, 1, $ano);
$hoje = date('Y-m-d');
$queryFiltros = [
    'tipo' => (int)($tipoFiltro ?? 0),
    'status' => $statusFiltro ?? ''
];

$agendamentosMap = [];
foreach ($agendamentos as $ag) {
    $chaveData = date('Y-m-d', strtotime($ag['data_inspecao']));
    if (!isset($agendamentosMap[$chaveData])) {
        $agendamentosMap[$chaveData] = [];
    }
    $agendamentosMap[$chaveData][] = $ag;
}
?>

<section class="page-shell">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Planeamento</span>
            <h1><i class="bi bi-calendar3"></i> Calendário de inspeções</h1>
            <p>Clique num dia para abrir imediatamente o agendamento dessa data e acompanhe as inspeções previstas no mês.</p>
        </div>
        <div class="page-hero__actions">
            <a href="index.php?controler=calendario&acao=agendar&data=<?php echo date('Y-m-d'); ?>&mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" class="btn btn-dashboard-primary">
                <i class="bi bi-calendar-plus"></i> Novo agendamento
            </a>
            <a href="index.php?controler=calendario&acao=dashboard" class="btn btn-dashboard-secondary">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>
    </div>

    <section class="panel-surface calendar-surface">
        <form method="GET" action="index.php" class="calendar-filters">
            <input type="hidden" name="controler" value="calendario">
            <input type="hidden" name="acao" value="calendario">
            <input type="hidden" name="mes" value="<?php echo (int)$mes; ?>">
            <input type="hidden" name="ano" value="<?php echo (int)$ano; ?>">

            <div class="calendar-filters__group">
                <label for="filtro_tipo">Tipo de equipamento</label>
                <select id="filtro_tipo" name="tipo" class="form-select form-select-sm">
                    <option value="0">Todos</option>
                    <?php foreach ($tiposEquipamentos as $tipo): ?>
                        <option value="<?php echo (int)$tipo['id']; ?>" <?php echo ((int)$tipoFiltro === (int)$tipo['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="calendar-filters__group">
                <label for="filtro_status">Estado</label>
                <select id="filtro_status" name="status" class="form-select form-select-sm">
                    <option value="" <?php echo ($statusFiltro === '') ? 'selected' : ''; ?>>Todos</option>
                    <option value="agendado" <?php echo ($statusFiltro === 'agendado') ? 'selected' : ''; ?>>Agendado</option>
                    <option value="em_progresso" <?php echo ($statusFiltro === 'em_progresso') ? 'selected' : ''; ?>>Em progresso</option>
                    <option value="concluido" <?php echo ($statusFiltro === 'concluido') ? 'selected' : ''; ?>>Concluido</option>
                    <option value="cancelado" <?php echo ($statusFiltro === 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>

            <div class="calendar-filters__actions">
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                <a href="index.php?controler=calendario&acao=calendario&mes=<?php echo (int)$mes; ?>&ano=<?php echo (int)$ano; ?>" class="btn btn-sm btn-outline-secondary">Limpar</a>
            </div>
        </form>

        <div class="calendar-toolbar">
            <a href="index.php?controler=calendario&acao=calendario&mes=<?php echo date('n', $mesAnteriorTs); ?>&ano=<?php echo date('Y', $mesAnteriorTs); ?>&tipo=<?php echo (int)$queryFiltros['tipo']; ?>&status=<?php echo urlencode($queryFiltros['status']); ?>" class="calendar-nav-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="calendar-toolbar__title">
                <span class="panel-surface__eyebrow">Vista mensal</span>
                <h2><?php echo $meses[(int)$mes] . ' ' . $ano; ?></h2>
            </div>
            <a href="index.php?controler=calendario&acao=calendario&mes=<?php echo date('n', $mesSeguinteTs); ?>&ano=<?php echo date('Y', $mesSeguinteTs); ?>&tipo=<?php echo (int)$queryFiltros['tipo']; ?>&status=<?php echo urlencode($queryFiltros['status']); ?>" class="calendar-nav-btn">
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="calendar-layout">
            <div class="calendar-grid" data-calendar-grid>
            <?php foreach ($diasSemana as $diaSemana): ?>
                <div class="calendar-grid__weekday"><?php echo $diaSemana; ?></div>
            <?php endforeach; ?>

            <?php for ($vazio = 0; $vazio < $diaInicio; $vazio++): ?>
                <div class="calendar-day calendar-day--empty"></div>
            <?php endfor; ?>

            <?php for ($dia = 1; $dia <= $diasMes; $dia++): ?>
                <?php
                $dataAtual = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                $itensDia = $agendamentosMap[$dataAtual] ?? [];
                $isToday = $dataAtual === $hoje;
                $classes = 'calendar-day';
                if ($isToday) {
                    $classes .= ' calendar-day--today';
                }
                if (!empty($itensDia)) {
                    $classes .= ' calendar-day--scheduled';
                }
                ?>
                <a
                    href="index.php?controler=calendario&acao=agendar&data=<?php echo $dataAtual; ?>&mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>"
                    class="<?php echo $classes; ?>"
                    data-day-link
                    data-date="<?php echo $dataAtual; ?>"
                    data-items="<?php echo htmlspecialchars(json_encode($itensDia, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                    title="Agendar inspeção para <?php echo date('d/m/Y', strtotime($dataAtual)); ?>"
                >
                    <div class="calendar-day__header">
                        <span class="calendar-day__number"><?php echo $dia; ?></span>
                        <?php if (!empty($itensDia)): ?>
                            <span class="calendar-day__count"><?php echo count($itensDia); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="calendar-day__content">
                        <?php if (empty($itensDia)): ?>
                            <span class="calendar-day__hint">Clique para agendar</span>
                        <?php else: ?>
                            <?php foreach (array_slice($itensDia, 0, 3) as $ag): ?>
                                <span
                                    class="calendar-event-pill"
                                    title="<?php echo htmlspecialchars(($ag['tipo_equipamento'] ?? 'Sem tipo') . ' | ' . ucfirst(str_replace('_', ' ', $ag['tipo_inspecao'])) . ' | ' . ($ag['localizacao'] ?? 'Todos os equipamentos do tipo')); ?>"
                                ><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $ag['tipo_inspecao']))); ?></span>
                            <?php endforeach; ?>
                            <?php if (count($itensDia) > 3): ?>
                                <span class="calendar-day__more">+<?php echo count($itensDia) - 3; ?> mais</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endfor; ?>
            </div>

            <aside class="calendar-day-panel" data-day-panel>
                <div class="calendar-day-panel__header">
                    <span class="panel-surface__eyebrow">Dia selecionado</span>
                    <h3 data-day-panel-date><?php echo date('d/m/Y'); ?></h3>
                </div>

                <div class="calendar-day-panel__body" data-day-panel-body>
                    <p class="calendar-day-panel__empty">Selecione um dia do calendário para ver os agendamentos e ações disponíveis.</p>
                </div>

                <div class="calendar-day-panel__footer">
                    <a href="index.php?controler=calendario&acao=agendar&data=<?php echo date('Y-m-d'); ?>&mes=<?php echo (int)$mes; ?>&ano=<?php echo (int)$ano; ?>" class="btn btn-sm btn-primary" data-day-panel-create>
                        <i class="bi bi-calendar-plus"></i> Agendar neste dia
                    </a>
                </div>
            </aside>
        </div>
    </section>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dayLinks = Array.from(document.querySelectorAll('[data-day-link]'));
    const panel = document.querySelector('[data-day-panel]');
    const panelDate = panel ? panel.querySelector('[data-day-panel-date]') : null;
    const panelBody = panel ? panel.querySelector('[data-day-panel-body]') : null;
    const panelCreate = panel ? panel.querySelector('[data-day-panel-create]') : null;

    if (!panel || !panelDate || !panelBody || !panelCreate) {
        return;
    }

    function formatDateLabel(dateISO) {
        const [year, month, day] = dateISO.split('-');
        return day + '/' + month + '/' + year;
    }

    function sanitize(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function buildEventCard(item) {
        const status = (item.status || 'agendado').replace(/_/g, ' ');
        const tipo = (item.tipo_inspecao || 'inspecao').replace(/_/g, ' ');
        const tipoEquipamento = item.tipo_equipamento || 'Tipo de equipamento não definido';
        const local = item.localizacao || 'Todos os equipamentos do tipo';
        const verUrl = 'index.php?controler=calendario&acao=ver&id=' + item.id + '&mes=<?php echo (int)$mes; ?>&ano=<?php echo (int)$ano; ?>';
        const editarUrl = 'index.php?controler=calendario&acao=editar&id=' + item.id + '&mes=<?php echo (int)$mes; ?>&ano=<?php echo (int)$ano; ?>';

        return '' +
            '<article class="calendar-day-event">' +
                '<div class="calendar-day-event__top">' +
                    '<strong>' + sanitize(tipo.charAt(0).toUpperCase() + tipo.slice(1)) + '</strong>' +
                    '<span class="status-pill status-pill--info">' + sanitize(status.charAt(0).toUpperCase() + status.slice(1)) + '</span>' +
                '</div>' +
                '<p><strong>' + sanitize(tipoEquipamento) + '</strong></p>' +
                '<p>' + sanitize(local) + '</p>' +
                '<div class="calendar-day-event__actions">' +
                    '<a class="btn btn-sm btn-outline-primary" href="' + verUrl + '">Ver</a>' +
                    '<a class="btn btn-sm btn-primary" href="' + editarUrl + '">Editar</a>' +
                '</div>' +
            '</article>';
    }

    function renderDay(linkElement) {
        dayLinks.forEach((link) => link.classList.remove('calendar-day--active'));
        linkElement.classList.add('calendar-day--active');

        const dateISO = linkElement.dataset.date;
        panelDate.textContent = formatDateLabel(dateISO);
        panelCreate.href = 'index.php?controler=calendario&acao=agendar&data=' + dateISO + '&mes=<?php echo (int)$mes; ?>&ano=<?php echo (int)$ano; ?>';

        let items = [];
        try {
            items = JSON.parse(linkElement.dataset.items || '[]');
        } catch (e) {
            items = [];
        }

        if (!Array.isArray(items) || items.length === 0) {
            panelBody.innerHTML = '<p class="calendar-day-panel__empty">Sem agendamentos para este dia. Use o botão abaixo para marcar uma nova inspeção.</p>';
            return;
        }

        panelBody.innerHTML = items.map(buildEventCard).join('');
    }

    dayLinks.forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            renderDay(link);
        });
    });

    const initialDay = dayLinks.find((link) => link.classList.contains('calendar-day--today')) || dayLinks[0];
    if (initialDay) {
        renderDay(initialDay);
    }
});
</script>
