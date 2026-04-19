<?php
$tipoNome = htmlspecialchars((string)($tipo['nome'] ?? 'Tipo de Equipamento'), ENT_QUOTES, 'UTF-8');
$totalEquipamentos = count($equipamentos ?? []);

$formatarData = function ($data) {
    $data = trim((string)$data);
    if ($data === '' || $data === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($data);
    if ($timestamp === false) {
        return '-';
    }

    return date('d/m/Y', $timestamp);
};
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Equipamentos por Tipo</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f5f7;
            color: #18202a;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #fff;
            border-bottom: 1px solid #d6dde6;
        }

        .documento {
            width: 100%;
            max-width: 950px;
            margin: 18px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .documento__header {
            padding: 22px 24px 16px;
            border-bottom: 1px solid #e3e8ee;
        }

        .documento__eyebrow {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #5f7288;
            margin-bottom: 8px;
        }

        .documento__titulo {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
        }

        .documento__meta {
            margin-top: 10px;
            color: #566575;
            font-size: 14px;
        }

        .tabela-wrap {
            padding: 18px 24px 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #e8edf3;
            color: #213244;
            font-size: 13px;
            text-align: left;
            padding: 10px 12px;
            border: 1px solid #d5dde7;
        }

        tbody td {
            padding: 10px 12px;
            border: 1px solid #e0e6ee;
            font-size: 13px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) td {
            background: #f9fbfc;
        }

        .estado {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .estado--ok {
            background: #e7f7ec;
            color: #1d7c3b;
        }

        .estado--warn {
            background: #fdeaea;
            color: #b23434;
        }

        .vazio {
            padding: 40px 24px;
            text-align: center;
            color: #66788a;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .documento {
                max-width: none;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div><strong><?php echo $totalEquipamentos; ?></strong> equipamento(s)</div>
        <div>
            <button type="button" onclick="window.print()">Imprimir</button>
            <button type="button" onclick="window.close()">Fechar</button>
        </div>
    </div>

    <article class="documento">
        <header class="documento__header">
            <div class="documento__eyebrow">Lista Imprimível</div>
            <h1 class="documento__titulo"><?php echo $tipoNome; ?></h1>
            <div class="documento__meta">
                Total de equipamentos ativos: <?php echo $totalEquipamentos; ?> | Gerado em <?php echo date('d/m/Y H:i'); ?>
            </div>
        </header>

        <?php if (empty($equipamentos)): ?>
            <div class="vazio">Não existem equipamentos ativos para este tipo.</div>
        <?php else: ?>
            <div class="tabela-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Localização</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Estado</th>
                            <th>Próxima Vistoria</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipamentos as $equipamento): ?>
                            <?php $estado = (string)($equipamento['estado'] ?? '-'); ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)($equipamento['numero_serie'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)($equipamento['localizacao'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)($equipamento['marca'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)($equipamento['modelo'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="estado <?php echo $estado === 'operacional' ? 'estado--ok' : 'estado--warn'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($estado), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($formatarData($equipamento['data_proxima_manutencao'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</body>
</html>
