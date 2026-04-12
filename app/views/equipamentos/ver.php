<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-tools"></i> <?php echo $equipamento['tipo_nome']; ?></h1>

        <div class="card mb-3">
            <div class="card-body">
                <p>
                    <strong>Localização:</strong> <?php echo $equipamento['localizacao']; ?><br>
                    <strong>Número de Registo:</strong> <?php echo $equipamento['numero_serie'] ?? '-'; ?><br>
                    <strong>Marca:</strong> <?php echo $equipamento['marca'] ?? '-'; ?><br>
                    <strong>Modelo:</strong> <?php echo $equipamento['modelo'] ?? '-'; ?><br>
                    <strong>Data de Aquisição:</strong> <?php echo $equipamento['data_aquisicao'] ? date('d/m/Y', strtotime($equipamento['data_aquisicao'])) : '-'; ?><br>
                    <strong>Data de Instalação:</strong> <?php echo $equipamento['data_instalacao'] ? date('d/m/Y', strtotime($equipamento['data_instalacao'])) : '-'; ?><br>
                    <strong>Próxima Vistoria:</strong> <?php echo $equipamento['data_proxima_manutencao'] ? date('d/m/Y', strtotime($equipamento['data_proxima_manutencao'])) : '-'; ?><br>
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
            <a href="index.php?controler=equipamento&acao=listar" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>
