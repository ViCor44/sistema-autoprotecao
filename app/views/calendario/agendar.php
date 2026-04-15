<section class="page-shell page-shell--narrow">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Novo agendamento</span>
            <h1><i class="bi bi-calendar-plus"></i> Agendar inspeção</h1>
            <p>O formulário abre já associado ao dia selecionado no calendário para acelerar o planeamento.</p>
        </div>
    </div>

    <form method="POST" action="index.php?controler=calendario&acao=salvar" class="panel-surface modern-form">
        <input type="hidden" name="return_mes" value="<?php echo (int)$returnMes; ?>">
        <input type="hidden" name="return_ano" value="<?php echo (int)$returnAno; ?>">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Planeamento</span>
                <h2>Dados do agendamento</h2>
            </div>
        </div>

        <div class="card-body px-0 pb-0">
                <div class="mb-3">
                    <label for="tipo_equipamento_id" class="form-label">Tipo de Equipamento *</label>
                    <select name="tipo_equipamento_id" id="tipo_equipamento_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($tiposEquipamentos as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>"><?php echo $tipo['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="equipamento_id" class="form-label">Equipamento Específico (opcional)</label>
                    <select name="equipamento_id" id="equipamento_id" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($equipamentos as $equip): ?>
                            <option value="<?php echo $equip['id']; ?>" data-tipo-id="<?php echo $equip['tipo_equipamento_id']; ?>"><?php echo $equip['tipo_nome']; ?> - <?php echo $equip['localizacao']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Deixe vazio para agendar a inspeção para todos os equipamentos do tipo selecionado.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="data_inspecao" class="form-label">Data da Inspeção *</label>
                        <input type="date" class="form-control" name="data_inspecao" id="data_inspecao" value="<?php echo htmlspecialchars($dataSelecionada); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo_inspecao" class="form-label">Tipo de Inspeção</label>
                        <select name="tipo_inspecao" id="tipo_inspecao" class="form-select">
                            <option value="inspecao">Inspeção de Rotina</option>
                            <option value="inspecao_extraordinaria">Inspeção Extraordinária</option>
                            <option value="vistoria_corretiva">Vistoria Corretiva</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" id="descricao" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="prioridade" class="form-label">Prioridade</label>
                        <select name="prioridade" id="prioridade" class="form-select">
                            <option value="baixa">Baixa</option>
                            <option value="normal" selected>Normal</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="responsavel_id" class="form-label">Responsável</label>
                        <input type="text" class="form-control" name="responsavel_id" id="responsavel_id" placeholder="Opcional">
                    </div>
                </div>

            <div class="form-actions-bar">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Agendar</button>
                <a href="index.php?controler=calendario&acao=calendario&mes=<?php echo (int)$returnMes; ?>&ano=<?php echo (int)$returnAno; ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
            </div>
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
            selectEquipamento.value = '';
        }
    }

    selectTipo.addEventListener('change', filtrarEquipamentosPorTipo);
});
</script>
