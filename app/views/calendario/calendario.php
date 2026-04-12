<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-calendar3"></i> Calendário de Manutenção</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <a href="index.php?controler=calendario&acao=agendar" class="btn btn-success">
            <i class="bi bi-calendar-plus"></i> Novo Agendamento
        </a>
        <a href="index.php?controler=calendario&acao=dashboard" class="btn btn-info">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <a href="index.php?controler=calendario&acao=calendario&mes=<?php echo date('m', strtotime('-1 month')); ?>&ano=<?php echo date('Y', strtotime('-1 month')); ?>" class="btn btn-sm btn-secondary">&lt;</a>
                    <?php echo strftime('%B %Y', mktime(0, 0, 0, date('m'), 1, date('Y'))); ?>
                    <a href="index.php?controler=calendario&acao=calendario&mes=<?php echo date('m', strtotime('+1 month')); ?>&ano=<?php echo date('Y', strtotime('+1 month')); ?>" class="btn btn-sm btn-secondary">&gt;</a>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sab</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $mes = date('m') ?? 1;
                            $ano = date('Y') ?? date('Y');
                            $primeiro = mktime(0, 0, 0, $mes, 1, $ano);
                            $ultimo = mktime(0, 0, 0, $mes + 1, 0, $ano);
                            $diasMes = date('t', $primeiro);
                            $diaInicio = date('w', $primeiro);
                            
                            $agendamentosMap = [];
                            foreach ($agendamentos as $ag) {
                                $data = date('d', strtotime($ag['data_inspecao']));
                                if (!isset($agendamentosMap[$data])) {
                                    $agendamentosMap[$data] = [];
                                }
                                $agendamentosMap[$data][] = $ag;
                            }
                            
                            $dia = 1;
                            ?>
                            <tr>
                                <?php 
                                for ($i = 0; $i < $diaInicio; $i++) {
                                    echo '<td>&nbsp;</td>';
                                }
                                
                                for ($d = 1; $d <= $diasMes; $d++) {
                                    if (($dia - 1) % 7 === 0 && $dia > 1) echo '</tr><tr>';
                                    
                                    $temAgendamentos = isset($agendamentosMap[$d]);
                                    $classe = $temAgendamentos ? 'bg-light-warning' : '';
                                    echo '<td class="' . $classe . '" style="height: 80px; vertical-align: top;">';
                                    echo '<strong>' . str_pad($d, 2, '0', STR_PAD_LEFT) . '</strong>';
                                    
                                    if ($temAgendamentos) {
                                        echo '<br><small>';
                                        foreach ($agendamentosMap[$d] as $ag) {
                                            echo '<span class="badge bg-primary">' . ucfirst($ag['tipo_inspecao']) . '</span><br>';
                                        }
                                        echo '</small>';
                                    }
                                    echo '</td>';
                                    $dia++;
                                }
                                
                                while (($dia - 1) % 7 !== 0) {
                                    echo '<td>&nbsp;</td>';
                                    $dia++;
                                }
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
