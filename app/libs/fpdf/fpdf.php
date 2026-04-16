<?php
// FPDF compatível (implementação mínima) para este projeto.
// Suporta os métodos necessários aos relatórios atuais.
class FPDF {
    private $k = 72 / 25.4; // Conversão mm -> pt
    private $pageWidthMm = 210.0;
    private $pageHeightMm = 297.0;

    private $lMargin = 10.0;
    private $rMargin = 10.0;
    private $tMargin = 10.0;

    private $x = 10.0;
    private $y = 10.0;
    private $lastCellHeight = 5.0;

    private $fontFamily = 'helvetica';
    private $fontStyle = '';
    private $fontSizePt = 11.0;
    private $fontKey = 'F1';

    private $drawColor = [0, 0, 0];
    private $fillColor = [255, 255, 255];
    private $textColor = [0, 0, 0];
    private $lineWidthMm = 0.2;

    private $pages = [];
    private $currentPage = -1;

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        $unit = strtolower((string)$unit);
        if ($unit !== 'mm') {
            // Esta implementação usa mm na API pública.
            $this->k = 72 / 25.4;
        }

        $sizes = [
            'A4' => [210.0, 297.0],
            'A5' => [148.0, 210.0],
        ];

        $sizeKey = strtoupper((string)$size);
        if (isset($sizes[$sizeKey])) {
            $this->pageWidthMm = $sizes[$sizeKey][0];
            $this->pageHeightMm = $sizes[$sizeKey][1];
        }

        if (strtoupper((string)$orientation) === 'L') {
            $tmp = $this->pageWidthMm;
            $this->pageWidthMm = $this->pageHeightMm;
            $this->pageHeightMm = $tmp;
        }

        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
    }

    public function AddPage() {
        $this->pages[] = [];
        $this->currentPage = count($this->pages) - 1;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->lastCellHeight = 5.0;
    }

    public function SetFont($family, $style = '', $size = 11) {
        $this->fontFamily = strtolower((string)$family);
        $this->fontStyle = strtoupper((string)$style);
        $this->fontSizePt = max(6.0, (float)$size);
        $this->fontKey = $this->resolveFontKey($this->fontFamily, $this->fontStyle);
    }

    public function SetDrawColor($r, $g = null, $b = null) {
        if ($g === null || $b === null) {
            $g = $r;
            $b = $r;
        }
        $this->drawColor = [(int)$r, (int)$g, (int)$b];
    }

    public function SetFillColor($r, $g = null, $b = null) {
        if ($g === null || $b === null) { $g = $r; $b = $r; }
        $this->fillColor = [(int)$r, (int)$g, (int)$b];
    }

    public function SetTextColor($r, $g = null, $b = null) {
        if ($g === null || $b === null) { $g = $r; $b = $r; }
        $this->textColor = [(int)$r, (int)$g, (int)$b];
    }

    public function SetLineWidth($width) {
        $this->lineWidthMm = max(0.1, (float)$width);
    }

    public function SetXY($x, $y) {
        $this->x = (float)$x;
        $this->y = (float)$y;
    }

    public function SetX($x) {
        $this->x = (float)$x;
    }

    public function SetY($y) {
        $this->y = (float)$y;
    }

    public function GetY() {
        return $this->y;
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $this->assertPage();

        $w = (float)$w;
        $h = $h > 0 ? (float)$h : 5.0;
        if ($w <= 0) {
            $w = max(10.0, $this->pageWidthMm - $this->rMargin - $this->x);
        }

        if ((int)$border === 1) {
            $this->Rect($this->x, $this->y, $w, $h);
        }

        $text = $this->sanitizeText($txt);
        if ($text !== '') {
            $textX = $this->x + 1.2;
            $maxChars = $this->estimateCharsForWidth($w - 2.4);
            $len = strlen($text);
            $align = strtoupper((string)$align);

            if ($align === 'C' && $len < $maxChars) {
                $pad = (int)floor(($maxChars - $len) / 2);
                $text = str_repeat(' ', max(0, $pad)) . $text;
            } elseif ($align === 'R' && $len < $maxChars) {
                $pad = $maxChars - $len;
                $text = str_repeat(' ', max(0, $pad)) . $text;
            }

            $textY = $this->y + ($h * 0.72);
            $this->addText($textX, $textY, $text);
        }

        $this->lastCellHeight = $h;

        if ($ln > 0) {
            $this->x = $this->lMargin;
            $this->y += $h;
        } else {
            $this->x += $w;
        }
    }

    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false) {
        $w = (float)$w;
        $h = $h > 0 ? (float)$h : 5.0;
        if ($w <= 0) {
            $w = max(10.0, $this->pageWidthMm - $this->rMargin - $this->x);
        }

        $text = $this->sanitizeText($txt);
        $parts = preg_split('/\r\n|\r|\n/', $text);

        foreach ($parts as $part) {
            $part = trim((string)$part);
            if ($part === '') {
                $this->Cell($w, $h, '', 0, 1);
                continue;
            }

            foreach ($this->wrapTextToWidth($part, $w - 2.4) as $line) {
                $this->Cell($w, $h, $line, 0, 1, $align, $fill);
            }
        }

        if ((int)$border === 1) {
            // Não implementado: borda composta de MultiCell.
        }
    }

    public function Ln($h = null) {
        $delta = $h !== null ? (float)$h : $this->lastCellHeight;
        $delta = $delta > 0 ? $delta : 5.0;
        $this->x = $this->lMargin;
        $this->y += $delta;
    }

    public function Line($x1, $y1, $x2, $y2) {
        $this->assertPage();
        $this->addRaw(sprintf(
            '%.3F %.3F %.3F RG %.3F w %.3F %.3F m %.3F %.3F l S',
            $this->drawColor[0] / 255,
            $this->drawColor[1] / 255,
            $this->drawColor[2] / 255,
            $this->lineWidthMm * $this->k,
            $x1 * $this->k,
            ($this->pageHeightMm - $y1) * $this->k,
            $x2 * $this->k,
            ($this->pageHeightMm - $y2) * $this->k
        ));
    }

    public function Rect($x, $y, $w, $h, $style = 'S') {
        $this->assertPage();
        $style = strtoupper((string)$style);
        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        $this->addRaw(sprintf(
            '%.3F %.3F %.3F rg %.3F %.3F %.3F RG %.3F w %.3F %.3F %.3F %.3F re %s',
            $this->fillColor[0] / 255,
            $this->fillColor[1] / 255,
            $this->fillColor[2] / 255,
            $this->drawColor[0] / 255,
            $this->drawColor[1] / 255,
            $this->drawColor[2] / 255,
            $this->lineWidthMm * $this->k,
            $x * $this->k,
            ($this->pageHeightMm - $y - $h) * $this->k,
            $w * $this->k,
            $h * $this->k,
            $op
        ));
    }

    public function Output($dest = 'I', $name = 'documento.pdf') {
        $this->assertPage();
        $pdf = $this->buildPdf();

        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $this->sanitizeFilename($name) . '"');
            header('Content-Length: ' . strlen($pdf));
        }

        echo $pdf;
    }

    private function addText($xMm, $yMm, $text) {
        $escaped = $this->escapePdfText($text);
        $xPt = $xMm * $this->k;
        $yPt = ($this->pageHeightMm - $yMm) * $this->k;

        $this->addRaw(sprintf(
            '%.3F %.3F %.3F rg BT /%s %.3F Tf 1 0 0 1 %.3F %.3F Tm (%s) Tj ET 0 0 0 rg',
            $this->textColor[0] / 255,
            $this->textColor[1] / 255,
            $this->textColor[2] / 255,
            $this->fontKey,
            $this->fontSizePt,
            $xPt,
            $yPt,
            $escaped
        ));
    }

    private function addRaw($cmd) {
        $this->assertPage();
        $this->pages[$this->currentPage][] = (string)$cmd;
    }

    private function assertPage() {
        if ($this->currentPage < 0) {
            $this->AddPage();
        }
    }

    private function sanitizeText($txt) {
        $text = (string)$txt;
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        if ($this->isUtf8($text)) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', ' ', $text);
        return trim((string)$text);
    }

    private function wrapTextToWidth($text, $widthMm) {
        $maxChars = $this->estimateCharsForWidth($widthMm);
        if ($maxChars < 8) {
            $maxChars = 8;
        }
        if (strlen($text) <= $maxChars) {
            return [$text];
        }
        return explode("\n", wordwrap($text, $maxChars, "\n", true));
    }

    private function estimateCharsForWidth($widthMm) {
        $charWidthMm = max(1.2, ($this->fontSizePt * 0.23));
        return max(1, (int)floor($widthMm / $charWidthMm));
    }

    private function escapePdfText($text) {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string)$text);
    }

    private function sanitizeFilename($name) {
        $name = trim((string)$name);
        if ($name === '') {
            $name = 'documento.pdf';
        }
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
        if (strtolower((string)substr($name, -4)) !== '.pdf') {
            $name .= '.pdf';
        }
        return $name;
    }

    private function isUtf8($text) {
        return preg_match('//u', (string)$text) === 1;
    }

    private function resolveFontKey($family, $style) {
        $family = strtolower((string)$family);
        $bold = strpos($style, 'B') !== false;

        if ($family === 'times') {
            return $bold ? 'F4' : 'F3';
        }
        if ($family === 'courier') {
            return $bold ? 'F6' : 'F5';
        }
        return $bold ? 'F2' : 'F1';
    }

    private function buildPdf() {
        $objects = [];

        $fontMap = [
            'F1' => 'Helvetica',
            'F2' => 'Helvetica-Bold',
            'F3' => 'Times-Roman',
            'F4' => 'Times-Bold',
            'F5' => 'Courier',
            'F6' => 'Courier-Bold',
        ];

        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $kids = [];
        $contentObjectIds = [];
        $pageStartId = 10;
        $contentStartId = 100;

        for ($i = 0; $i < count($this->pages); $i++) {
            $pageId = $pageStartId + $i;
            $contentId = $contentStartId + $i;
            $kids[] = $pageId . ' 0 R';
            $contentObjectIds[] = [$pageId, $contentId];
        }

        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($kids) . " >>\nendobj\n";

        foreach ($contentObjectIds as [$pageId, $contentId]) {
            $resources = '/Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R /F4 6 0 R /F5 7 0 R /F6 8 0 R >>';
            $objects[] = $pageId . " 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " .
                sprintf('%.3F %.3F', $this->pageWidthMm * $this->k, $this->pageHeightMm * $this->k) .
                "] /Resources << {$resources} >> /Contents {$contentId} 0 R >>\nendobj\n";
        }

        $fontObjectId = 3;
        foreach ($fontMap as $fontName) {
            $objects[] = $fontObjectId . " 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /{$fontName} /Encoding /WinAnsiEncoding >>\nendobj\n";
            $fontObjectId++;
        }

        foreach ($contentObjectIds as $index => [$pageId, $contentId]) {
            $stream = implode("\n", $this->pages[$index]);
            $len = strlen($stream);
            $objects[] = $contentId . " 0 obj\n<< /Length {$len} >>\nstream\n{$stream}\nendstream\nendobj\n";
        }

        usort($objects, function ($a, $b) {
            $idA = (int)strtok($a, ' ');
            $idB = (int)strtok($b, ' ');
            return $idA <=> $idB;
        });

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];
        $maxId = 0;

        foreach ($objects as $obj) {
            $id = (int)strtok($obj, ' ');
            $maxId = max($maxId, $id);
            $offsets[$id] = strlen($pdf);
            $pdf .= $obj;
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxId; $i++) {
            $off = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }

        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }
}
