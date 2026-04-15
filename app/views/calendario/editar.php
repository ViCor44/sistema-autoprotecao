<section class="page-shell page-shell--narrow">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Edição</span>
            <h1><i class="bi bi-pencil-square"></i> Editar agendamento</h1>
            <p>Atualize os dados do agendamento e mantenha o plano de inspeções alinhado com a operação.</p>
        </div>
    </div>

    <form method="POST" action="index.php?controler=calendario&acao=atualizar&id=<?php echo (int)$agendamento['id']; ?>" class="panel-surface modern-form">
        <input type="hidden" name="return_mes" value="<?php echo (int)$returnMes; ?>">
        <input type="hidden" name="return_ano" value="<?php echo (int)$returnAno; ?>">

        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Dados do agendamento</span>
                <h2>Planeamento da inspeção</h2>
            </div>
        </div>

        <div class="mb-3">
            <label for="tipo_equipamento_id" class="form-label">Tipo de equipamento *</label>
            <select name="tipo_equipamento_id" id="tipo_equipamento_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($tiposEquipamentos as $tipo): ?>
                    <option value="<?php echo (int)$tipo['id']; ?>" <?php echo ((int)$agendamento['tipo_equipamento_id'] === (int)$tipo['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tipo['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="equipamento_id" class="form-label">Equipamento específico (opcional)</label>
            <select name="equipamento_id" id="equipamento_id" class="form-select">
                <option value="0">Todos do tipo selecionado</option>
                <?php foreach ($equipamentos as $equip): ?>
                    <option
                        value="<?php echo (int)$equip['id']; ?>"
                        data-tipo-id="<?php echo (int)$equip['tipo_equipamento_id']; ?>"
                        <?php echo ((int)$agendamento['equipamento_id'] === (int)$equip['id']) ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($equip['tipo_nome'] . ' - ' . $equip['localizacao']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-grid-two">
            <div class="mb-3">
                <label for="tipo_inspecao" class="form-label">Tipo de inspeção</label>
                <select name="tipo_inspecao" id="tipo_inspecao" class="form-select">
                    <option value="inspecao" <?php echo ($agendamento['tipo_inspecao'] === 'inspecao') ? 'selected' : ''; ?>>Inspeção de rotina</option>
                    <option value="inspecao_extraordinaria" <?php echo ($agendamento['tipo_inspecao'] === 'inspecao_extraordinaria') ? 'selected' : ''; ?>>Inspeção extraordinária</option>
                    <option value="vistoria_corretiva" <?php echo ($agendamento['tipo_inspecao'] === 'vistoria_corretiva') ? 'selected' : ''; ?>>Vistoria corretiva</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="prioridade" class="form-label">Prioridade</label>
                <select name="prioridade" id="prioridade" class="form-select">
                    <option value="baixa" <?php echo ($agendamento['prioridade'] === 'baixa') ? 'selected' : ''; ?>>Baixa</option>
                    <option value="normal" <?php echo ($agendamento['prioridade'] === 'normal') ? 'selected' : ''; ?>>Normal</option>
                    <option value="alta" <?php echo ($agendamento['prioridade'] === 'alta') ? 'selected' : ''; ?>>Alta</option>
                    <option value="urgente" <?php echo ($agendamento['prioridade'] === 'urgente') ? 'selected' : ''; ?>>Urgente</option>
                </select>
            </div>
        </div>

        <div class="form-grid-two">
            <div class="mb-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" id="status" class="form-select">
                    <option value="agendado" <?php echo ($agendamento['status'] === 'agendado') ? 'selected' : ''; ?>>Agendado</option>
                    <option value="em_progresso" <?php echo ($agendamento['status'] === 'em_progresso') ? 'selected' : ''; ?>>Em progresso</option>
                    <option value="concluido" <?php echo ($agendamento['status'] === 'concluido') ? 'selected' : ''; ?>>Concluido</option>
                    <option value="cancelado" <?php echo ($agendamento['status'] === 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="responsavel_id" class="form-label">Responsável (ID)</label>
                <input type="number" class="form-control" name="responsavel_id" id="responsavel_id" min="1" value="<?php echo !empty($agendamento['responsavel_id']) ? (int)$agendamento['responsavel_id'] : ''; ?>" placeholder="Opcional">
            </div>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" name="descricao" id="descricao" rows="4"><?php echo htmlspecialchars($agendamento['descricao'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions-bar">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar alterações</button>
            <a href="index.php?controler=calendario&acao=ver&id=<?php echo (int)$agendamento['id']; ?>&mes=<?php echo (int)$returnMes; ?>&ano=<?php echo (int)$returnAno; ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectTipo = document.getElementById('tipo_equipamento_id');
    const selectEquipamento = document.getElementById('equipamento_id');
    const opcoesEquipamento = Array.from(selectEquipamento.querySelectorAll('option'));

    function filtrarEquipamentosPorTipo() {
        const tipoId = selectTipo.value;

        opcoesEquipamento.forEach(function (opcao, index) {
            if (index === 0) {
                opcao.hidden = false;
                return;
            }

            const tipoOpcao = opcao.getAttribute('data-tipo-id');
            opcao.hidden = !!tipoId && tipoOpcao !== tipoId;
        });

        if (selectEquipamento.selectedOptions[0] && selectEquipamento.selectedOptions[0].hidden) {
            selectEquipamento.value = '0';
        }
    }

    selectTipo.addEventListener('change', filtrarEquipamentosPorTipo);
    filtrarEquipamentosPorTipo();
});
</script>
