<?php
$status = $agendamento['status'] ?? 'agendado';
$prioridade = $agendamento['prioridade'] ?? 'normal';

$statusClass = 'status-pill--info';
if ($status === 'concluido') {
    $statusClass = 'status-pill--success';
} elseif ($status === 'cancelado') {
    $statusClass = 'status-pill--warning';
}

$prioridadeClass = 'status-pill--info';
if ($prioridade === 'alta' || $prioridade === 'urgente') {
    $prioridadeClass = 'status-pill--warning';
}
?>

<section class="page-shell page-shell--narrow">
    <header class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Detalhe</span>
            <h1><i class="bi bi-calendar-check"></i> Agendamento de Inspecao</h1>
            <p>Consulte os dados completos deste agendamento e execute as proximas acoes operacionais.</p>
        </div>
        <div class="page-hero__actions">
            <span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="status-pill <?php echo $prioridadeClass; ?>"><?php echo htmlspecialchars('Prioridade: ' . ucfirst($prioridade), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </header>

    <section class="panel-surface">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Ficha</span>
                <h2>Dados do agendamento</h2>
            </div>
        </div>

        <div class="detail-stack">
            <div class="detail-row">
                <span>Tipo de equipamento</span>
                <strong><?php echo htmlspecialchars($agendamento['tipo_equipamento'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="detail-row">
                <span>Ambito</span>
                <strong>
                    <?php if (!empty($agendamento['equipamento_id'])): ?>
                        Equipamento especifico (<?php echo htmlspecialchars($agendamento['localizacao'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>)
                    <?php else: ?>
                        Todos os equipamentos do tipo
                    <?php endif; ?>
                </strong>
            </div>
            <div class="detail-row">
                <span>Data prevista</span>
                <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($agendamento['data_inspecao'])), ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="detail-row">
                <span>Tipo de inspecao</span>
                <strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $agendamento['tipo_inspecao'])), ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="detail-row">
                <span>Responsavel</span>
                <strong><?php echo htmlspecialchars($agendamento['responsavel_nome'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        </div>

        <div class="narrative-block agenda-ver__descricao">
            <h3>Descricao</h3>
            <p><?php echo nl2br(htmlspecialchars($agendamento['descricao'] ?? '-', ENT_QUOTES, 'UTF-8')); ?></p>
        </div>

        <div class="form-actions-bar agenda-ver__actions">
            <?php if (($agendamento['status'] ?? '') !== 'concluido'): ?>
                <a href="index.php?controler=calendario&acao=editar&id=<?php echo (int)$agendamento['id']; ?>&mes=<?php echo (int)$returnMes; ?>&ano=<?php echo (int)$returnAno; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square"></i> Editar agendamento
                </a>
            <?php endif; ?>

            <?php if (!empty($relatorioInspecao)): ?>
                <a href="index.php?controler=relatorio&acao=ver&id=<?php echo (int)$relatorioInspecao['id']; ?>" class="btn btn-primary">
                    <i class="bi bi-file-earmark-text"></i> Ver relatorio da inspecao
                </a>

                <?php if ($agendamento['status'] !== 'concluido'): ?>
                    <a href="index.php?controler=inspecao&acao=preencher&id=<?php echo (int)$agendamento['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-clipboard-check"></i> Registar execucao da inspecao
                    </a>
                <?php elseif (empty($relatorioInspecao['assinado'])): ?>
                    <a href="index.php?controler=relatorio&acao=editar&id=<?php echo (int)$relatorioInspecao['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Preencher formulario da inspecao
                    </a>
                <?php endif; ?>
            <?php elseif (($agendamento['tipo_inspecao'] ?? '') === 'inspecao'): ?>
                <a href="index.php?controler=inspecao&acao=preencher&id=<?php echo (int)$agendamento['id']; ?>" class="btn btn-warning">
                    <i class="bi bi-clipboard-check"></i> Registar execucao da inspecao
                </a>
            <?php endif; ?>

            <a href="index.php?controler=calendario&acao=calendario&mes=<?php echo (int)$returnMes; ?>&ano=<?php echo (int)$returnAno; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar ao calendario
            </a>
        </div>
    </section>
</section>
