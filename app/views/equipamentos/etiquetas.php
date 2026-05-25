<?php
$modoEscolhaPosicao = $modoEscolhaPosicao ?? false;
$modoUnico          = $modoUnico ?? false;
function _etiNome(string $s): string {
    return htmlspecialchars($s !== '' ? $s : '-', ENT_QUOTES, 'UTF-8');
}
$baseUrl = 'index.php?controler=equipamento&acao=etiquetas';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas de Equipamentos</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f5f5f5; color: #111; }
        @page { size: A4 portrait; margin: 8mm; }
        .toolbar {
            position: sticky; top: 0; z-index: 5;
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 14px; background: #fff; border-bottom: 1px solid #d9d9d9;
            gap: 8px; flex-wrap: wrap;
        }
        .toolbar__title { font-weight: 700; font-size: 15px; }
        .toolbar__sub   { font-size: 12px; color: #666; margin-top: 1px; }
        .toolbar button {
            padding: 6px 14px; border: 1px solid #bbb; border-radius: 4px;
            background: #fff; cursor: pointer; font-size: 13px;
        }
        .toolbar button.btn-primary { background: #0d6efd; border-color: #0d6efd; color: #fff; }
        .toolbar button:hover { background: #f0f0f0; }
        .toolbar button.btn-primary:hover { background: #0b5ed7; }
        /* Folha */
        .sheet {
            width: 194mm; min-height: 281mm; margin: 10px auto;
            background: #fff; display: grid;
            grid-template-columns: repeat(4, 1fr);
            border: 1px dashed #d0d0d0;
        }
        .sheet--qr      { grid-template-rows: repeat(6,  1fr); }
        .sheet--simples { grid-template-rows: repeat(12, 1fr); }
        /* Etiqueta QR */
        .etiqueta { padding: 2.2mm; overflow: hidden; }
        .etiqueta__placa {
            height: 100%; width: 100%; border-radius: 3.8mm;
            border: 0.35mm solid #b6b6b6; background: #fff; padding: 2.1mm;
            display: grid; grid-template-rows: auto 1fr auto; gap: 1.4mm;
        }
        .etiqueta__topo { font-size: 8px; font-weight: 800; letter-spacing: 0.3px; text-transform: uppercase; line-height: 1.15; }
        .etiqueta__meio { display: grid; grid-template-columns: 23.5mm 1fr; gap: 1.8mm; align-items: center; min-height: 0; }
        .etiqueta__qr {
            width: 22.5mm; height: 22.5mm; display: flex; align-items: center; justify-content: center;
            background: #fff; border: 0.35mm solid #2d2d2d; border-radius: 1.4mm; padding: 0.6mm;
        }
        .etiqueta__qr img, .etiqueta__qr canvas { width: 100% !important; height: 100% !important; }
        .etiqueta__conteudo { min-width: 0; }
        .etiqueta__codigo { font-size: 5.8mm; line-height: 1; font-weight: 500; letter-spacing: 0.12mm; word-break: break-word; }
        .etiqueta__rodape { border-top: 0.25mm solid #d2d2d2; padding-top: 1mm; }
        .etiqueta__linha { font-size: 6.9px; color: #1d1d1d; line-height: 1.2; word-break: break-word; }
        .etiqueta__linha strong { font-weight: 700; }
        /* Etiqueta simples */
        .etiqueta__placa--simples { display: grid; grid-template-rows: auto 1fr; padding: 2.6mm; gap: 1mm; }
        .etiqueta__topo--simples { font-size: 7px; font-weight: 800; letter-spacing: 0.3px; text-transform: uppercase; line-height: 1.15; text-align: center; }
        .etiqueta__codigo--simples { font-size: 6.4mm; line-height: 1; font-weight: 700; letter-spacing: 0.18mm; text-align: center; word-break: break-word; }
        /* Vazia */
        .etiqueta--vazia { border: 1px dashed #c9c9c9; background: #fafafa; border-radius: 3.8mm; margin: 2.2mm; }
        /* Seletor posicao */
        .pos-wrap { max-width: 620px; margin: 24px auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px 24px; }
        .pos-wrap h2 { margin: 0 0 4px; font-size: 18px; }
        .pos-wrap .sub { font-size: 13px; color: #555; margin-bottom: 18px; }
        .pos-equip-info { display: flex; gap: 14px; align-items: flex-start; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; }
        .pos-equip-info strong { display: block; font-size: 15px; margin-bottom: 2px; }
        .pos-sheet-label { font-size: 12px; font-weight: 600; color: #444; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        .pos-badge { display: inline-block; background: #0d6efd; color: #fff; font-size: 11px; padding: 1px 7px; border-radius: 10px; }
        .pos-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; border: 2px solid #aaa; border-radius: 4px; padding: 6px; background: #f0f0f0; width: 100%; max-width: 400px; }
        .pos-grid--qr      { grid-template-rows: repeat(6,  36px); }
        .pos-grid--simples { grid-template-rows: repeat(12, 20px); }
        .pos-cell { background: #fff; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; font-size: 9px; color: #888; display: flex; align-items: center; justify-content: center; transition: background .12s, border-color .12s; user-select: none; }
        .pos-cell:hover { background: #dbeafe; border-color: #3b82f6; color: #1d4ed8; }
        .pos-cell--selected { background: #bfdbfe; border-color: #2563eb; color: #1e40af; font-weight: 700; }
        .pos-hint { font-size: 11px; color: #777; margin-top: 10px; }
        .pos-actions { margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap; }
        .pos-actions button { padding: 8px 18px; border-radius: 4px; border: 1px solid; cursor: pointer; font-size: 14px; }
        .pos-actions .btn-confirm { background: #0d6efd; border-color: #0d6efd; color: #fff; }
        .pos-actions .btn-confirm:disabled { background: #93c5fd; border-color: #93c5fd; cursor: not-allowed; }
        .pos-actions .btn-cancel { background: #fff; border-color: #bbb; color: #333; }
        @media print {
            body { background: #fff; }
            .toolbar, .pos-wrap { display: none; }
            .sheet { margin: 0; border: 0; page-break-after: always; }
            .sheet:last-of-type { page-break-after: auto; }
        }
    </style>
</head>
<body>

<?php if ($modoEscolhaPosicao): ?>
<?php
    $equip       = $equipamento;
    $eId         = (int)($equip['id'] ?? 0);
    $eNr         = trim((string)($equip['numero_registo'] ?? ''));
    $eLoc        = trim((string)($equip['localizacao'] ?? ''));
    $eTipo       = trim((string)($equip['tipo_nome'] ?? 'Equipamento'));
    $eIsExtintor = (bool)($isExtintor ?? false);
    $eCelulas    = (int)($totalCelulas ?? ($eIsExtintor ? 24 : 48));
    $eCols       = 4;
    $eLinhas     = (int)($eCelulas / $eCols);
    $gridClass   = $eIsExtintor ? 'pos-grid--qr' : 'pos-grid--simples';
    $tipoLabel   = $eIsExtintor ? 'Etiqueta com QR (extintor)' : 'Etiqueta com numeracao';
?>
    <div class="toolbar">
        <div>
            <div class="toolbar__title">Imprimir Etiqueta</div>
            <div class="toolbar__sub"><?php echo htmlspecialchars($eTipo, ENT_QUOTES, 'UTF-8'); ?> &mdash; <?php echo htmlspecialchars($eNr !== '' ? $eNr : '(sem n&#186;)', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div>
            <button type="button" onclick="history.back()">&#8592; Voltar</button>
        </div>
    </div>

    <div class="pos-wrap">
        <h2>Escolher posi&#231;&#227;o na folha</h2>
        <p class="sub">Clique na c&#233;lula onde pretende imprimir a etiqueta. As restantes posi&#231;&#245;es ficam em branco.</p>

        <div class="pos-equip-info">
            <div>
                <strong><?php echo htmlspecialchars($eTipo, ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php if ($eNr !== ''): ?>
                    N&#186; Registo: <strong><?php echo htmlspecialchars($eNr, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                <?php endif; ?>
                <?php if ($eLoc !== ''): ?>
                    Localiza&#231;&#227;o: <?php echo htmlspecialchars($eLoc, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="pos-sheet-label">
            Folha A4 &mdash; <?php echo $eCelulas; ?> posi&#231;&#245;es
            <span class="pos-badge"><?php echo htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="pos-grid <?php echo $gridClass; ?>" id="posGrid">
            <?php for ($i = 0; $i < $eCelulas; $i++): ?>
                <div class="pos-cell" data-pos="<?php echo $i; ?>" title="Posi&#231;&#227;o <?php echo ($i + 1); ?>">
                    <?php echo ($i + 1); ?>
                </div>
            <?php endfor; ?>
        </div>

        <p class="pos-hint">Linha <?php echo $eLinhas; ?> &#215; Coluna 4 &mdash; posi&#231;&#227;o 1 = canto superior esquerdo</p>

        <div class="pos-actions">
            <button type="button" class="btn-confirm" id="btnConfirmar" disabled
                onclick="confirmarPosicao(<?php echo $eId; ?>)">
                Imprimir nesta posi&#231;&#227;o
            </button>
            <button type="button" class="btn-cancel" onclick="history.back()">Cancelar</button>
        </div>
    </div>

    <script>
    (function () {
        var selected = null;
        document.getElementById('posGrid').addEventListener('click', function (e) {
            var cell = e.target.closest('.pos-cell');
            if (!cell) return;
            document.querySelectorAll('.pos-cell--selected').forEach(function (c) {
                c.classList.remove('pos-cell--selected');
            });
            cell.classList.add('pos-cell--selected');
            selected = parseInt(cell.getAttribute('data-pos'), 10);
            document.getElementById('btnConfirmar').disabled = false;
        });
    })();
    function confirmarPosicao(id) {
        var cells = document.querySelectorAll('.pos-cell--selected');
        if (cells.length === 0) return;
        var pos = parseInt(cells[0].getAttribute('data-pos'), 10);
        window.location.href = '<?php echo $baseUrl; ?>&id=' + id + '&posicao=' + pos;
    }
    </script>

<?php elseif ($modoUnico): ?>
<?php
    $eq          = $etiqueta;
    $eIsExtintor = (bool)($isExtintor ?? false);
    $ePosicao    = (int)($posicao ?? 0);
    $eCelulas    = (int)($totalCelulas ?? ($eIsExtintor ? 24 : 48));
    $sheetClass  = $eIsExtintor ? 'sheet--qr' : 'sheet--simples';
    $eNr         = trim((string)($eq['numero_registo'] ?? ''));
    $eLoc        = trim((string)($eq['localizacao'] ?? ''));
    $eId         = (int)($eq['id'] ?? 0);
?>
    <div class="toolbar">
        <div>
            <div class="toolbar__title">
                <strong>1</strong> etiqueta
                <?php if ($eIsExtintor): ?>(extintor &mdash; com QR)<?php else: ?>(numerac&#771;a&#771;o)<?php endif; ?>
                &mdash; posi&#231;&#227;o <?php echo ($ePosicao + 1); ?>/<?php echo $eCelulas; ?>
            </div>
        </div>
        <div style="display:flex;gap:6px;">
            <button type="button"
                onclick="window.location.href='<?php echo $baseUrl; ?>&id=<?php echo $eId; ?>'">
                &#8592; Alterar posi&#231;&#227;o
            </button>
            <button type="button" class="btn-primary" onclick="window.print()">Imprimir</button>
            <button type="button" onclick="window.close()">Fechar</button>
        </div>
    </div>

    <section class="sheet <?php echo $sheetClass; ?>">
        <?php for ($i = 0; $i < $eCelulas; $i++): ?>
            <?php if ($i === $ePosicao): ?>
                <article class="etiqueta">
                    <?php if ($eIsExtintor): ?>
                        <div class="etiqueta__placa">
                            <div class="etiqueta__topo">Sistema de Autoprote&#231;&#227;o</div>
                            <div class="etiqueta__meio">
                                <div class="etiqueta__qr js-etiqueta-qr"
                                     data-qr="NR=<?php echo htmlspecialchars($eNr, ENT_QUOTES, 'UTF-8'); ?>;LOC=<?php echo htmlspecialchars($eLoc, ENT_QUOTES, 'UTF-8'); ?>"></div>
                                <div class="etiqueta__conteudo">
                                    <div class="etiqueta__codigo"><?php echo _etiNome($eNr); ?></div>
                                </div>
                            </div>
                            <div class="etiqueta__rodape">
                                <div class="etiqueta__linha"><strong>LOCALIZA&#199;&#195;O:</strong> <?php echo _etiNome($eLoc); ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="etiqueta__placa etiqueta__placa--simples">
                            <div class="etiqueta__topo etiqueta__topo--simples">Sistema de Autoprote&#231;&#227;o</div>
                            <div class="etiqueta__codigo etiqueta__codigo--simples"
                                 style="display:flex;align-items:center;justify-content:center;">
                                <?php echo _etiNome($eNr); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </article>
            <?php else: ?>
                <article class="etiqueta etiqueta--vazia"></article>
            <?php endif; ?>
        <?php endfor; ?>
    </section>

    <?php if ($eIsExtintor): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function normQr(t) {
            return String(t||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^\x20-\x7E]/g,'').trim();
        }
        document.querySelectorAll('.js-etiqueta-qr').forEach(function (el) {
            new QRCode(el, { text: normQr(el.getAttribute('data-qr')||''), width: 88, height: 88, colorDark: '#000', colorLight: '#fff', correctLevel: QRCode.CorrectLevel.M });
        });
    });
    </script>
    <?php endif; ?>

<?php else: ?>
<?php
    $paginasQr      = $paginasQr ?? [];
    $paginasSimples = $paginasSimples ?? [];
    $totalEtiquetas = (int)($totalEtiquetas ?? 0);
    $nSimples = (int)array_sum(array_map('count', $paginasSimples));
    $nQr      = (int)array_sum(array_map('count', $paginasQr));
?>
    <div class="toolbar">
        <div>
            <div class="toolbar__title"><strong><?php echo $totalEtiquetas; ?></strong> etiqueta(s)</div>
            <div class="toolbar__sub">
                <?php if ($nQr > 0): ?><?php echo $nQr; ?> extintor(es) com QR<?php endif; ?>
                <?php if ($nQr > 0 && $nSimples > 0): ?> &nbsp;|&nbsp; <?php endif; ?>
                <?php if ($nSimples > 0): ?><?php echo $nSimples; ?> equipamento(s) com numera&#231;&#227;o<?php endif; ?>
            </div>
        </div>
        <div style="display:flex;gap:6px;">
            <?php if ($totalEtiquetas > 0): ?>
                <button type="button" class="btn-primary" onclick="window.print()">Imprimir</button>
            <?php endif; ?>
            <button type="button" onclick="window.close()">Fechar</button>
        </div>
    </div>

    <?php if ($totalEtiquetas === 0): ?>
        <div class="sheet sheet--qr" style="display:flex;align-items:center;justify-content:center;min-height:160px;">
            <p>Nenhum equipamento para imprimir.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($paginasQr as $pagina): ?>
        <section class="sheet sheet--qr">
            <?php foreach ($pagina as $eq): ?>
                <?php
                $nr  = trim((string)($eq['numero_registo'] ?? ''));
                $loc = trim((string)($eq['localizacao'] ?? ''));
                ?>
                <article class="etiqueta">
                    <div class="etiqueta__placa">
                        <div class="etiqueta__topo">Sistema de Autoprote&#231;&#227;o</div>
                        <div class="etiqueta__meio">
                            <div class="etiqueta__qr js-etiqueta-qr"
                                 data-qr="NR=<?php echo htmlspecialchars($nr, ENT_QUOTES, 'UTF-8'); ?>;LOC=<?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?>"></div>
                            <div class="etiqueta__conteudo">
                                <div class="etiqueta__codigo"><?php echo _etiNome($nr); ?></div>
                            </div>
                        </div>
                        <div class="etiqueta__rodape">
                            <div class="etiqueta__linha"><strong>LOCALIZA&#199;&#195;O:</strong> <?php echo _etiNome($loc); ?></div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php for ($v = count($pagina); $v < 24; $v++): ?>
                <article class="etiqueta etiqueta--vazia"></article>
            <?php endfor; ?>
        </section>
    <?php endforeach; ?>

    <?php foreach ($paginasSimples as $pagina): ?>
        <section class="sheet sheet--simples">
            <?php foreach ($pagina as $eq): ?>
                <?php $nr = trim((string)($eq['numero_registo'] ?? '')); ?>
                <article class="etiqueta">
                    <div class="etiqueta__placa etiqueta__placa--simples">
                        <div class="etiqueta__topo etiqueta__topo--simples">Sistema de Autoprote&#231;&#227;o</div>
                        <div class="etiqueta__codigo etiqueta__codigo--simples"
                             style="display:flex;align-items:center;justify-content:center;">
                            <?php echo _etiNome($nr); ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php for ($v = count($pagina); $v < 48; $v++): ?>
                <article class="etiqueta etiqueta--vazia"></article>
            <?php endfor; ?>
        </section>
    <?php endforeach; ?>

    <?php if (!empty($paginasQr)): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function normQr(t) {
            return String(t||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^\x20-\x7E]/g,'').trim();
        }
        document.querySelectorAll('.js-etiqueta-qr').forEach(function (el) {
            new QRCode(el, { text: normQr(el.getAttribute('data-qr')||''), width: 88, height: 88, colorDark: '#000', colorLight: '#fff', correctLevel: QRCode.CorrectLevel.M });
        });
    });
    </script>
    <?php endif; ?>

<?php endif; ?>

</body>
</html>
