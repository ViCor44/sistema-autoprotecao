<?php
$totalEtiquetas = count($etiquetas ?? []);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas de Equipamentos</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #111;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: #ffffff;
            border-bottom: 1px solid #d9d9d9;
        }

        .sheet {
            width: 194mm;
            min-height: 281mm;
            margin: 10px auto;
            background: #fff;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(6, 1fr);
            border: 1px dashed #d0d0d0;
        }

        .etiqueta {
            border: 1px solid #222;
            padding: 3mm;
            display: grid;
            grid-template-columns: 26mm 1fr;
            gap: 2mm;
            align-items: center;
            overflow: hidden;
        }

        .etiqueta__qr {
            width: 24mm;
            height: 24mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .etiqueta__conteudo {
            min-width: 0;
        }

        .etiqueta__tipo {
            font-size: 9px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 1.5mm;
        }

        .etiqueta__linha {
            font-size: 8px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .etiqueta__linha strong {
            font-size: 8px;
        }

        .etiqueta--vazia {
            border: 1px dashed #bbb;
            background: #fafafa;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                margin: 0;
                border: 0;
                page-break-after: always;
            }

            .sheet:last-of-type {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div><strong><?php echo (int)$totalEtiquetas; ?></strong> etiqueta(s) gerada(s)</div>
        <div>
            <button type="button" onclick="window.print()">Imprimir</button>
            <button type="button" onclick="window.close()">Fechar</button>
        </div>
    </div>

    <?php if (empty($paginas)): ?>
        <div class="sheet" style="display:flex;align-items:center;justify-content:center;">
            <p>Nenhum equipamento para imprimir.</p>
        </div>
    <?php else: ?>
        <?php foreach ($paginas as $pagina): ?>
            <section class="sheet">
                <?php foreach ($pagina as $index => $equipamento): ?>
                    <?php
                    $numeroSerie = trim((string)($equipamento['numero_serie'] ?? ''));
                    $localizacao = trim((string)($equipamento['localizacao'] ?? ''));
                    $tipoNome = trim((string)($equipamento['tipo_nome'] ?? 'Equipamento'));
                    ?>
                    <article class="etiqueta">
                        <div
                            class="etiqueta__qr js-etiqueta-qr"
                            data-qr="NR=<?php echo htmlspecialchars($numeroSerie, ENT_QUOTES, 'UTF-8'); ?>;LOC=<?php echo htmlspecialchars($localizacao, ENT_QUOTES, 'UTF-8'); ?>"
                        ></div>
                        <div class="etiqueta__conteudo">
                            <div class="etiqueta__tipo"><?php echo htmlspecialchars($tipoNome, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="etiqueta__linha"><strong>NR:</strong> <?php echo htmlspecialchars($numeroSerie !== '' ? $numeroSerie : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="etiqueta__linha"><strong>Local:</strong> <?php echo htmlspecialchars($localizacao !== '' ? $localizacao : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php for ($vazio = count($pagina); $vazio < 24; $vazio++): ?>
                    <article class="etiqueta etiqueta--vazia"></article>
                <?php endfor; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function normalizarTextoQr(texto) {
            return String(texto || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^\x20-\x7E]/g, '')
                .trim();
        }

        const itensQr = document.querySelectorAll('.js-etiqueta-qr');
        itensQr.forEach(function (item) {
            const bruto = item.getAttribute('data-qr') || '';
            const payload = normalizarTextoQr(bruto);

            new QRCode(item, {
                text: payload,
                width: 88,
                height: 88,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    });
    </script>
</body>
</html>
