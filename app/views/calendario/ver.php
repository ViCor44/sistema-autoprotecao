<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4">Agendamento de Inspeção</h1>

        <div class="card">
            <div class="card-body">
                <p>
                    <strong>Tipo de Equipamento:</strong> <?php echo $agendamento['tipo_equipamento']; ?><br>
                    <strong>Âmbito:</strong>
                    <?php if (!empty($agendamento['equipamento_id'])): ?>
                        Equipamento específico (<?php echo $agendamento['localizacao']; ?>)
                    <?php else: ?>
                        Todos os equipamentos do tipo
                    <?php endif; ?><br>
                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($agendamento['data_inspecao'])); ?><br>
                    <strong>Tipo:</strong> <?php echo ucfirst(str_replace('_', ' ', $agendamento['tipo_inspecao'])); ?><br>
                    <strong>Prioridade:</strong> <?php echo ucfirst($agendamento['prioridade']); ?><br>
                    <strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $agendamento['status'])); ?><br>
                    <strong>Responsável:</strong> <?php echo $agendamento['responsavel_nome'] ?? '-'; ?><br>
                    <strong>Descrição:</strong> <?php echo nl2br($agendamento['descricao']); ?>
                </p>
            </div>
            <div class="card-footer bg-white">
                <?php if (!empty($relatorioInspecao)): ?>
                    <a href="index.php?controler=relatorio&acao=ver&id=<?php echo $relatorioInspecao['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text"></i> Ver Relatório da Inspeção
                    </a>
                    <?php if ($agendamento['status'] !== 'concluido'): ?>
                        <a href="index.php?controler=inspecao&acao=preencher&id=<?php echo $agendamento['id']; ?>" class="btn btn-warning ms-2">
                            <i class="bi bi-clipboard-check"></i> Registar Execução da Inspeção
                        </a>
                    <?php elseif (!$relatorioInspecao['assinado']): ?>
                        <a href="index.php?controler=relatorio&acao=editar&id=<?php echo $relatorioInspecao['id']; ?>" class="btn btn-warning ms-2">
                            <i class="bi bi-pencil-square"></i> Preencher Formulário da Inspeção
                        </a>
                    <?php endif; ?>
                <?php elseif ($agendamento['tipo_inspecao'] === 'inspecao'): ?>
                    <a href="index.php?controler=inspecao&acao=preencher&id=<?php echo $agendamento['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-clipboard-check"></i> Registar Execução da Inspeção
                    </a>
                <?php endif; ?>
                <a href="index.php?controler=calendario&acao=listar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>
