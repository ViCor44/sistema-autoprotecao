<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4">Agendamento</h1>

        <div class="card">
            <div class="card-body">
                <p>
                    <strong>Equipamento:</strong> <?php echo $agendamento['tipo_equipamento']; ?> (<?php echo $agendamento['localizacao']; ?>)<br>
                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($agendamento['data_inspecao'])); ?><br>
                    <strong>Tipo:</strong> <?php echo ucfirst($agendamento['tipo_inspecao']); ?><br>
                    <strong>Prioridade:</strong> <?php echo ucfirst($agendamento['prioridade']); ?><br>
                    <strong>Status:</strong> <?php echo ucfirst($agendamento['status']); ?><br>
                    <strong>Responsável:</strong> <?php echo $agendamento['responsavel_nome'] ?? '-'; ?><br>
                    <strong>Descrição:</strong> <?php echo nl2br($agendamento['descricao']); ?>
                </p>
            </div>
            <div class="card-footer bg-white">
                <a href="index.php?controler=calendario&acao=listar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>
