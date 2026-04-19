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

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-tools"></i> <?php echo $equipamento['tipo_nome']; ?></h1>

        <!-- Código QR -->
        <div class="card mb-3 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-qrcode"></i> Código QR</h5>
            </div>
            <div class="card-body text-center">
                <div id="qrcode-container" class="mb-3" style="display: flex; justify-content: center;"></div>
                <p class="text-muted mb-0">
                    <small>QR com numero de registo e localizacao do equipamento</small>
                </p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <p>
                    <strong>Localização:</strong> <?php echo $equipamento['localizacao']; ?><br>
                    <strong>Número de Registo:</strong> <?php echo $equipamento['numero_serie'] ?? '-'; ?><br>
                    <strong>Marca:</strong> <?php echo $equipamento['marca'] ?? '-'; ?><br>
                    <strong>Modelo:</strong> <?php echo $equipamento['modelo'] ?? '-'; ?><br>
                    <strong>Data de Aquisição:</strong> <?php echo $formatarDataSegura($equipamento['data_aquisicao'] ?? null); ?><br>
                    <strong>Data de Instalação:</strong> <?php echo $formatarDataSegura($equipamento['data_instalacao'] ?? null); ?><br>
                    <strong>Próxima Vistoria:</strong> <?php echo $formatarDataSegura($equipamento['data_proxima_manutencao'] ?? null); ?><br>
                    <strong>Estado:</strong> 
                    <span class="badge bg-<?php echo $equipamento['estado'] === 'operacional' ? 'success' : 'danger'; ?>">
                        <?php echo ucfirst($equipamento['estado']); ?>
                    </span><br>
                    <strong>Observações:</strong> <?php echo $equipamento['observacoes'] ?? '-'; ?>
                </p>

                <?php if (!empty($camposDinamicos)): ?>
                    <hr>
                    <h5>Características Específicas</h5>
                    <div class="row">
                        <?php foreach ($camposDinamicos as $campo): ?>
                            <div class="col-md-6 mb-2">
                                <strong><?php echo $campo['nome_campo']; ?>:</strong>
                                <?php
                                    $valor = $valoresCamposDinamicos[$campo['id']] ?? '-';
                                    $sufixo = !empty($campo['unidade']) && $valor !== '-' ? ' ' . $campo['unidade'] : '';
                                    echo htmlspecialchars((string)$valor) . $sufixo;
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer bg-white">
            <a href="index.php?controler=equipamento&acao=editar&id=<?php echo $equipamento['id']; ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="index.php?controler=equipamento&acao=deletar&id=<?php echo $equipamento['id']; ?>" class="btn btn-danger" onclick="return confirm('Tem a certeza?');">
                <i class="bi bi-trash"></i> Eliminar
            </a>
            <a href="index.php?controler=equipamento&acao=listar" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<!-- Script para gerar o código QR -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const numeroSerie = <?php echo json_encode((string)($equipamento['numero_serie'] ?? ''), JSON_UNESCAPED_UNICODE); ?>;
        const localizacao = <?php echo json_encode((string)($equipamento['localizacao'] ?? ''), JSON_UNESCAPED_UNICODE); ?>;
        const qrPayload = 'NR=' + numeroSerie + ';LOC=' + localizacao;
        
        new QRCode(document.getElementById('qrcode-container'), {
            text: qrPayload,
            width: 300,
            height: 300,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>
