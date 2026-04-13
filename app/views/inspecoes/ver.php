<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-clipboard-check"></i> Relatório da Inspeção</h1>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Informações da Inspeção</h5>
            </div>
            <div class="card-body">
                <p>
                    <strong>Tipo de Equipamento:</strong> <?php echo $inspecao['tipo_equipamento']; ?><br>
                    <strong>Localização:</strong> <?php echo $inspecao['localizacao'] ?? '-'; ?><br>
                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($inspecao['data_inspecao'])); ?><br>
                    <strong>Responsável:</strong> <?php echo $inspecao['responsavel_nome'] ?? '-'; ?><br>
                </p>
                <hr>
                <p>
                    <strong>Parecer:</strong> <?php echo nl2br($inspecao['parecer'] ?? '-'); ?><br>
                    <strong>Equipamentos Avariados:</strong> <?php echo nl2br($inspecao['equipamentos_avariados'] ?? '-'); ?><br>
                    <strong>Observações:</strong> <?php echo nl2br($inspecao['observacoes'] ?? '-'); ?><br>
                </p>
            </div>
        </div>
        <div class="card-footer bg-white">
            <a href="index.php?controler=inspecao&acao=exportar_pdf&id=<?php echo $inspecao['id']; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </a>
            <a href="index.php?controler=inspecao&acao=listar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        </div>
    </div>
</div>
