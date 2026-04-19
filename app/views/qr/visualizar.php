<?php
$formatarDataSegura = function ($data) {
    $data = trim((string)$data);

    if ($data === '' || $data === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($data);
    if ($timestamp === false) {
        return '-';
    }

    $ano = (int)date('Y', $timestamp);
    if ($ano <= 1) {
        return '-';
    }

    return date('d/m/Y', $timestamp);
};
?>

<div class="qr-visualizacao-container">
    <div class="qr-header bg-primary text-white p-4 text-center">
        <h1 class="mb-0"><i class="bi bi-qrcode"></i></h1>
        <h2 class="mt-3 mb-1"><?php echo htmlspecialchars($equipamento['tipo_nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="mb-0 small">
            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($equipamento['localizacao'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </div>

    <div class="qr-content p-3">
        <!-- Informações Principais -->
        <div class="card mb-3 border-primary">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <small class="text-muted">Código de Equipamento</small>
                        <p class="mb-0 font-monospace fw-bold">
                            <code><?php echo htmlspecialchars($equipamento['codigo_barras'], ENT_QUOTES, 'UTF-8'); ?></code>
                        </p>
                    </div>

                    <div class="col-12">
                        <small class="text-muted">Número de Registo</small>
                        <p class="mb-0 fw-bold">
                            <?php echo htmlspecialchars($equipamento['numero_serie'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>

                    <div class="col-6">
                        <small class="text-muted">Marca</small>
                        <p class="mb-0">
                            <?php echo htmlspecialchars($equipamento['marca'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>

                    <div class="col-6">
                        <small class="text-muted">Modelo</small>
                        <p class="mb-0">
                            <?php echo htmlspecialchars($equipamento['modelo'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado -->
        <div class="card mb-3">
            <div class="card-body">
                <small class="text-muted">Estado Operacional</small>
                <p class="mb-0">
                    <span class="badge bg-<?php echo $equipamento['estado'] === 'operacional' ? 'success' : 'danger'; ?> fs-6">
                        <?php echo htmlspecialchars(ucfirst($equipamento['estado'] ?? 'indefinido'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </p>
            </div>
        </div>

        <!-- Datas Importantes -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 small">
                    <?php if ($formatarDataSegura($equipamento['data_aquisicao'] ?? null) !== '-'): ?>
                        <div class="col-6">
                            <small class="text-muted">Aquisição</small>
                            <p class="mb-0">
                                <i class="bi bi-calendar"></i> <?php echo $formatarDataSegura($equipamento['data_aquisicao'] ?? null); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($formatarDataSegura($equipamento['data_instalacao'] ?? null) !== '-'): ?>
                        <div class="col-6">
                            <small class="text-muted">Instalação</small>
                            <p class="mb-0">
                                <i class="bi bi-calendar"></i> <?php echo $formatarDataSegura($equipamento['data_instalacao'] ?? null); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($formatarDataSegura($equipamento['data_proxima_manutencao'] ?? null) !== '-'): ?>
                        <div class="col-12">
                            <small class="text-muted">Próxima Vistoria</small>
                            <p class="mb-0">
                                <i class="bi bi-calendar-check"></i> <?php echo $formatarDataSegura($equipamento['data_proxima_manutencao'] ?? null); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Campos Dinâmicos -->
        <?php if (!empty($camposDinamicos)): ?>
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-sliders"></i> Características Específicas</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 small">
                        <?php foreach ($camposDinamicos as $campo): ?>
                            <div class="col-6">
                                <small class="text-muted"><?php echo htmlspecialchars($campo['nome_campo'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <p class="mb-0 fw-bold">
                                    <?php
                                        $valor = $valoresCamposDinamicos[$campo['id']] ?? '-';
                                        $sufixo = !empty($campo['unidade']) && $valor !== '-' ? ' ' . $campo['unidade'] : '';
                                        echo htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8') . $sufixo;
                                    ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Observações -->
        <?php if (!empty($equipamento['observacoes'])): ?>
            <div class="card mb-3 border-warning">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Observações</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-0">
                        <?php echo nl2br(htmlspecialchars($equipamento['observacoes'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Ações -->
        <div class="d-grid gap-2 mt-4">
            <a href="index.php?controler=equipamento&acao=ver&id=<?php echo $equipamento['id']; ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-eye"></i> Ver Detalhes Completos
            </a>
            <a href="index.php?controler=equipamento&acao=listar" class="btn btn-outline-secondary">
                <i class="bi bi-list"></i> Voltar à Lista
            </a>
        </div>
    </div>
</div>

<style>
    .qr-visualizacao-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .qr-content {
        flex: 1;
        overflow-y: auto;
    }

    @media (max-width: 576px) {
        .qr-visualizacao-container {
            min-height: auto;
        }

        .qr-content {
            padding: 1rem !important;
        }
    }
</style>
