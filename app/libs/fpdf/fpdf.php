<?php
// FPDF compatível (implementação mínima para este projeto).
// Suporta os métodos usados na aplicação: AddPage, SetFont, Cell, Ln, MultiCell, Output.
class FPDF {
    private $lines = [];
    private $fontFamily = 'Helvetica';
    private $fontStyle = '';
    private $fontSize = 12;
    private $pageStarted = false;
    private $pageWidth = 595;
    private $pageHeight = 842;
    private $leftMargin = 28;
    private $topMargin = 28;
    private $rightMargin = 28;
    private $lineHeight = 16;

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        $sizes = [
            'A4' => [595, 842],
            'A5' => [420, 595],
        ];

        $sizeKey = strtoupper((string)$size);
        if (isset($sizes[$sizeKey])) {
            [$w, $h] = $sizes[$sizeKey];
            $this->pageWidth = $w;
            $this->pageHeight = $h;
        }

        $orientation = strtoupper((string)$orientation);
        if ($orientation === 'L') {
            $tmp = $this->pageWidth;
            $this->pageWidth = $this->pageHeight;
            $this->pageHeight = $tmp;
        }
    }

    public function AddPage() {
        if ($this->pageStarted) {
            $this->lines[] = '';
            $this->lines[] = '---';
            $this->lines[] = '';
        }
        $this->pageStarted = true;
    }

    public function SetFont($family, $style = '', $size = 12) {
        $family = strtolower((string)$family);
        $map = [
            'arial' => 'Helvetica',
            'helvetica' => 'Helvetica',
            'times' => 'Times-Roman',
            'courier' => 'Courier',
        ];

        $this->fontFamily = $map[$family] ?? 'Helvetica';
        $this->fontStyle = (string)$style;
        $this->fontSize = max(6, (int)$size);
        $this->lineHeight = max(10, (int)round($this->fontSize * 1.35));
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $text = $this->sanitizeText($txt);
        $text = $this->alignText($text, (string)$align);

        if ($ln > 0) {
            $this->lines[] = $text;
            return;
        }

        if (empty($this->lines)) {
            $this->lines[] = $text;
            return;
        }

        $lastIndex = count($this->lines) - 1;
        $separator = $this->lines[$lastIndex] === '' ? '' : ' ';
        $this->lines[$lastIndex] .= $separator . $text;
    }

    public function Ln($h = null) {
        $this->lines[] = '';
    }

    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false) {
        $text = $this->sanitizeText($txt);
        $parts = preg_split('/\r\n|\r|\n/', $text);

        foreach ($parts as $part) {
            $part = trim((string)$part);
            if ($part === '') {
                $this->lines[] = '';
                continue;
            }

            foreach ($this->wrapText($part, $this->getLineMaxChars()) as $wrapped) {
                $this->lines[] = $wrapped;
            }
        }
    }

    public function Output($dest = 'I', $name = 'documento.pdf') {
        if (!$this->pageStarted) {
            $this->AddPage();
        }

        if (empty($this->lines)) {
            $this->lines[] = 'Documento sem conteudo.';
        }

        $content = $this->buildContentStream($this->lines);
        $pdfData = $this->buildPdf($content);

        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $this->sanitizeFilename($name) . '"');
            header('Content-Length: ' . strlen($pdfData));
        }

        echo $pdfData;
    }

    private function sanitizeText($txt) {
        $text = (string)$txt;
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Se vier em UTF-8, converte para Windows-1252 (WinAnsi), compatível com fontes Type1.
        if ($this->isUtf8($text)) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        // Remove apenas control chars inválidos para stream PDF, preservando acentos (0x80-0xFF).
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', ' ', $text);
        return trim((string)$text);
    }

    private function wrapText($text, $maxLen) {
        if (strlen($text) <= $maxLen) {
            return [$text];
        }

        $wrapped = wordwrap($text, (int)$maxLen, "\n", true);
        return explode("\n", $wrapped);
    }

    private function escapePdfText($text) {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            (string)$text
        );
    }

    private function buildContentStream(array $lines) {
        $commands = [];
        $commands[] = 'BT';
        $commands[] = '/F1 ' . (int)$this->fontSize . ' Tf';
        $commands[] = (int)$this->leftMargin . ' ' . (int)($this->pageHeight - $this->topMargin) . ' Td';

        $lineHeight = $this->lineHeight;
        $first = true;

        foreach ($lines as $line) {
            if (!$first) {
                $commands[] = '0 -' . $lineHeight . ' Td';
            }
            $first = false;

            $escaped = $this->escapePdfText((string)$line);
            $commands[] = '(' . $escaped . ') Tj';
        }

        $commands[] = 'ET';
        return implode("\n", $commands);
    }

    private function buildPdf($contentStream) {
        $objects = [];

        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>\nendobj\n";

        $streamLength = strlen($contentStream);
        $objects[] = "4 0 obj\n<< /Length {$streamLength} >>\nstream\n{$contentStream}\nendstream\nendobj\n";

        $fontName = $this->fontFamily ?: 'Helvetica';
        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /{$fontName} >>\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj;
        }

        $xrefPos = strlen($pdf);
        $count = count($objects) + 1;

        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }

    private function sanitizeFilename($name) {
        $name = trim((string)$name);
        if ($name === '') {
            return 'documento.pdf';
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

    private function getLineMaxChars() {
        $usableWidth = max(120, $this->pageWidth - $this->leftMargin - $this->rightMargin);
        $avgCharWidth = max(4.5, $this->fontSize * 0.52);
        return max(20, (int)floor($usableWidth / $avgCharWidth));
    }

    private function alignText($text, $align) {
        $align = strtoupper(trim((string)$align));
        if ($align !== 'C' && $align !== 'R') {
            return $text;
        }

        $maxChars = $this->getLineMaxChars();
        $len = strlen((string)$text);
        if ($len >= $maxChars) {
            return $text;
        }

        if ($align === 'R') {
            return str_repeat(' ', $maxChars - $len) . $text;
        }

        $leftPad = (int)floor(($maxChars - $len) / 2);
        return str_repeat(' ', $leftPad) . $text;
    }
}
