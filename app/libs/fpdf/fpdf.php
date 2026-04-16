<?php
// FPDF compatível (implementação mínima para este projeto).
// Suporta os métodos usados na aplicação: AddPage, SetFont, Cell, Ln, MultiCell, Output.
class FPDF {
    private $lines = [];
    private $fontFamily = 'Helvetica';
    private $fontStyle = '';
    private $fontSize = 12;
    private $pageStarted = false;

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
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $text = $this->sanitizeText($txt);

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

            foreach ($this->wrapText($part, 100) as $wrapped) {
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
        $text = preg_replace('/[^\x09\x0A\x20-\x7E]/', '?', $text);
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
        $commands[] = '40 800 Td';

        $lineHeight = max(12, (int)round($this->fontSize * 1.35));
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
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>\nendobj\n";

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
}
