<?php
$totalEtiquetas = count($etiquetas ?? []);
$paginasSimples = array_chunk($etiquetas ?? [], 48);
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
            font-family: "Segoe UI", Arial, sans-serif;
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

        .sheet--simples {
            grid-template-rows: repeat(12, 1fr);
        }

        .etiqueta {
            padding: 2.2mm;
            overflow: hidden;
        }

        .etiqueta__placa {
            height: 100%;
            width: 100%;
            border-radius: 3.8mm;
            border: 0.35mm solid #b6b6b6;
            background: #ffffff;
            padding: 2.1mm;
            display: grid;
            grid-template-rows: auto 1fr auto;
            gap: 1.4mm;
        }

        .etiqueta__topo {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.15;
            text-align: left;
        }

        .etiqueta__meio {
            display: grid;
            grid-template-columns: 23.5mm 1fr;
            gap: 1.8mm;
            align-items: center;
            min-height: 0;
        }

        .etiqueta__qr {
            width: 22.5mm;
            height: 22.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 0.35mm solid #2d2d2d;
            border-radius: 1.4mm;
            padding: 0.6mm;
        }

        .etiqueta__qr img,
        .etiqueta__qr canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .etiqueta__conteudo {
            min-width: 0;
        }

        .etiqueta__codigo {
            font-size: 5.8mm;
            line-height: 1;
            font-weight: 500;
            letter-spacing: 0.12mm;
            white-space: normal;
            overflow: visible;
            word-break: break-word;
        }

        .etiqueta__rodape {
            border-top: 0.25mm solid #d2d2d2;
            padding-top: 1mm;
        }

        .etiqueta__placa--simples {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.6mm;
        }

        .etiqueta__codigo--simples {
            font-size: 6.4mm;
            line-height: 1;
            font-weight: 700;
            letter-spacing: 0.18mm;
            text-align: center;
            word-break: break-word;
        }

        .etiqueta__linha {
            font-size: 6.9px;
            color: #1d1d1d;
            white-space: normal;
            overflow: visible;
            line-height: 1.2;
            word-break: break-word;
        }

        .etiqueta__linha strong {
            font-weight: 700;
        }

        .etiqueta--vazia {
            border: 1px dashed #c9c9c9;
            background: #fafafa;
            border-radius: 3.8mm;
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
                        <div class="etiqueta__placa">
                            <div class="etiqueta__topo">Sistema Autoprotecao</div>
                            <div class="etiqueta__meio">
                                <div
                                    class="etiqueta__qr js-etiqueta-qr"
                                    data-qr="NR=<?php echo htmlspecialchars($numeroSerie, ENT_QUOTES, 'UTF-8'); ?>;LOC=<?php echo htmlspecialchars($localizacao, ENT_QUOTES, 'UTF-8'); ?>"
                                ></div>
                                <div class="etiqueta__conteudo">
                                    <div class="etiqueta__codigo"><?php echo htmlspecialchars($numeroSerie !== '' ? $numeroSerie : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                            <div class="etiqueta__rodape">
                                <div class="etiqueta__linha"><strong>LOCALIZACAO:</strong> <?php echo htmlspecialchars($localizacao !== '' ? $localizacao : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php for ($vazio = count($pagina); $vazio < 24; $vazio++): ?>
                    <article class="etiqueta etiqueta--vazia"></article>
                <?php endfor; ?>
            </section>
        <?php endforeach; ?>

        <?php foreach ($paginasSimples as $paginaSimples): ?>
            <section class="sheet sheet--simples">
                <?php foreach ($paginaSimples as $equipamento): ?>
                    <?php $numeroSerie = trim((string)($equipamento['numero_serie'] ?? '')); ?>
                    <article class="etiqueta">
                        <div class="etiqueta__placa etiqueta__placa--simples">
                            <div class="etiqueta__codigo etiqueta__codigo--simples"><?php echo htmlspecialchars($numeroSerie !== '' ? $numeroSerie : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php for ($vazio = count($paginaSimples); $vazio < 48; $vazio++): ?>
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
