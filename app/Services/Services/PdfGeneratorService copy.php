<?php

namespace App\Services\Services;

/**
 * Genera un PDF desde cero usando el formato PDF 1.4.
 * Tamaño de página: Oficio — 216 × 330 mm → 612 × 935 pt
 */
class PdfGeneratorService2
{
    private const PW = 612;
    private const PH = 935;

    private const MARGIN_LEFT   = 45;
    private const MARGIN_RIGHT  = 567;
    private const MARGIN_TOP    = 890;
    private const MARGIN_BOTTOM = 45;
    private const FONT_SIZE     = 10;
    private const CHAR_WIDTH    = 5.5;

    private array $pageStreams  = [];
    private int   $currentPage = -1;
    private float $cursorY     = self::MARGIN_TOP;
    private float $fontSize    = self::FONT_SIZE;

    
    private ?array $watermark  = null;

    // ── API pública ───────────────────────────────────────────────────────

    public function reset(): void
    {
        $this->pageStreams  = [];
        $this->currentPage = -1;
        $this->cursorY     = self::MARGIN_TOP;
        $this->fontSize    = self::FONT_SIZE;
        $this->watermark   = null;
    }

    /**
     * Registra una imagen PNG como marca de agua.
     * Se dibujará centrada y semitransparente en cada página.
     *
     * @param string $pngPath Ruta absoluta al archivo PNG
     * @param float  $opacity 0.0 (invisible) – 1.0 (sólido), recomendado 0.10–0.15
     * @param float  $scale   Fracción del ancho de página que ocupará la imagen (0.0–1.0)
     */
    public function setWatermark(string $pngPath, float $opacity = 0.12, float $scale = 0.70): void
    {
        if (!file_exists($pngPath)) {
            return;
        }

        // Leer dimensiones del PNG directamente desde el IHDR chunk (sin GD)
        $header = file_get_contents($pngPath, false, null, 0, 24);
        if ($header === false || strlen($header) < 24) {
            return;
        }

        if (substr($header, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            return; // No es un PNG válido
        }

        $width  = unpack('N', substr($header, 16, 4))[1];
        $height = unpack('N', substr($header, 20, 4))[1];

        if ($width <= 0 || $height <= 0) {
            return;
        }

        $this->watermark = [
            'path'    => $pngPath,
            'width'   => $width,
            'height'  => $height,
            'opacity' => max(0.0, min(1.0, $opacity)),
            'scale'   => max(0.1, min(1.0, $scale)),
        ];
    }

    public function addPage(): void
    {
        $this->currentPage++;
        $this->pageStreams[$this->currentPage] = '';
        $this->cursorY = self::MARGIN_TOP;

        // Dibujar marca de agua al inicio de cada página (queda debajo del contenido)
        if ($this->watermark !== null) {
            $this->drawWatermark();
        }
    }

    public function text(float $x, float $y, string $text, float $size = 0, bool $bold = false): void
    {
        $size = $size ?: $this->fontSize;
        $font = $bold ? 'F2' : 'F1';
        $safe = $this->escape($text);
        $this->pageStreams[$this->currentPage] .=
            "BT /{$font} {$size} Tf {$x} {$y} Td ({$safe}) Tj ET\n";
    }

    public function line(string $text, float $size = 0, bool $bold = false): void
    {
        if ($this->cursorY < self::MARGIN_BOTTOM + 20) {
            $this->addPage();
        }
        $this->text(self::MARGIN_LEFT, $this->cursorY, $text, $size, $bold);
        $this->cursorY -= ($size ?: $this->fontSize) + 4;
    }

    public function spacer(float $pts = 10): void
    {
        $this->cursorY -= $pts;
        if ($this->cursorY < self::MARGIN_BOTTOM + 20) {
            $this->addPage();
        }
    }

    public function hLine(): void
    {
        $y = $this->cursorY;
        $this->pageStreams[$this->currentPage] .=
            self::MARGIN_LEFT . " {$y} m " . self::MARGIN_RIGHT . " {$y} l S\n";
        $this->cursorY -= 6;
    }

    public function tableHeaderRow(array $cols): void
    {
        if ($this->cursorY < self::MARGIN_BOTTOM + 20) {
            $this->addPage();
        }

        $padding = 4;
        $rowH    = $this->fontSize + 8;
        $x       = self::MARGIN_LEFT;
        $yTop    = $this->cursorY + ($this->fontSize + 2);
        $yText   = $this->cursorY;
        $totalW  = array_sum(array_column($cols, 'width'));

        $this->pageStreams[$this->currentPage] .=
            "0.85 0.85 0.85 rg\n" .
            "{$x} " . ($yTop - $rowH) . " {$totalW} {$rowH} re f\n" .
            "0 0 0 rg\n";

        foreach ($cols as $col) {
            $w        = $col['width'] ?? 100;
            $maxChars = max(1, (int) floor(($w - $padding * 2) / self::CHAR_WIDTH));
            $txt      = mb_strimwidth($col['text'] ?? '', 0, $maxChars, '');
            $this->text($x + $padding, $yText, $txt, 0, true);
            $this->rect($x, $yTop - $rowH, $w, $rowH);
            $x += $w;
        }

        $this->cursorY -= $rowH;
    }

    public function tableDataRow(array $cols): float
    {
        $lineH   = $this->fontSize + 3;
        $padding = 4;

        $celdasLineas = [];
        foreach ($cols as $col) {
            $w              = max(1, ($col['width'] ?? 100) - $padding * 2);
            $maxChars       = max(1, (int) floor($w / self::CHAR_WIDTH));
            $celdasLineas[] = $this->wrapText($col['text'] ?? '', $maxChars);
        }

        $maxLineas = max(array_map('count', $celdasLineas));
        $rowH      = ($maxLineas * $lineH) + ($padding * 2);

        if ($this->cursorY - $rowH < self::MARGIN_BOTTOM + 20) {
            $this->addPage();
        }

        $x    = self::MARGIN_LEFT;
        $yTop = $this->cursorY + ($this->fontSize + 2);

        foreach ($cols as $i => $col) {
            $w     = $col['width'] ?? 100;
            $bold  = $col['bold']  ?? false;
            $lines = $celdasLineas[$i];

            $this->rect($x, $yTop - $rowH, $w, $rowH);

            $this->pageStreams[$this->currentPage] .=
                "q {$x} " . ($yTop - $rowH) . " {$w} {$rowH} re W n\n";

            $yLine = $yTop - $padding - $this->fontSize;
            foreach ($lines as $lineTxt) {
                $this->text($x + $padding, $yLine, $lineTxt, 0, $bold);
                $yLine -= $lineH;
            }

            $this->pageStreams[$this->currentPage] .= "Q\n";
            $x += $w;
        }

        $yCentro       = $yTop - ($rowH / 2);
        $this->cursorY -= $rowH;

        return $yCentro;
    }

    public function checkMarkInCell(float $cellX, float $cellY, float $cellW, float $cellH, bool $correct): void
    {
        $cx   = $cellX + ($cellW / 2);
        $cy   = $cellY + ($cellH / 2);
        $size = min(5, min($cellW, $cellH) * 0.35);

        $this->pageStreams[$this->currentPage] .=
            "q {$cellX} {$cellY} {$cellW} {$cellH} re W n\n";

        if ($correct) {
            $this->pageStreams[$this->currentPage] .=
                "0 0.55 0.27 RG 1.5 w\n" .
                ($cx - $size * 0.5) . " " . ($cy - $size * 0.2) . " m " .
                ($cx - $size * 0.1) . " " . ($cy - $size * 0.8) . " l " .
                ($cx + $size * 0.9) . " " . ($cy + $size * 0.8) . " l S\n" .
                "0 0 0 RG 0.5 w\n";
        } else {
            $this->pageStreams[$this->currentPage] .=
                "0.75 0.1 0.1 RG 1.5 w\n" .
                ($cx - $size * 0.7) . " " . ($cy - $size * 0.7) . " m " .
                ($cx + $size * 0.7) . " " . ($cy + $size * 0.7) . " l S\n" .
                ($cx + $size * 0.7) . " " . ($cy - $size * 0.7) . " m " .
                ($cx - $size * 0.7) . " " . ($cy + $size * 0.7) . " l S\n" .
                "0 0 0 RG 0.5 w\n";
        }

        $this->pageStreams[$this->currentPage] .= "Q\n";
    }

    public function getCursorY(): float
    {
        return $this->cursorY;
    }

    public function output(): string
    {
        // ── 1. Asignar IDs a todos los objetos ────────────────────────────
        $nextId    = 1;
        $catalogId = $nextId++;   // 1
        $pagesId   = $nextId++;   // 2
        $fontRegId = $nextId++;   // 3
        $fontBldId = $nextId++;   // 4

        $imageXObjId  = null;
        $extGStateId  = null;
        $smaskObjId   = null;
        $imageObjData = null;

        if ($this->watermark !== null) {
            $imageXObjId  = $nextId++;   // 5
            $extGStateId  = $nextId++;   // 6
            $imageObjData = $this->buildImageXObject($imageXObjId, $this->watermark);

            if ($imageObjData['smask_stream'] !== null) {
                $smaskObjId = $nextId++;  // 7  (solo si hay alpha)
                // Reemplazar placeholder en el header de la imagen con el ID real
                $imageObjData['header'] = str_replace(
                    '__SMASK_ID__',
                    (string) $smaskObjId,
                    $imageObjData['header']
                );
            }
        }

        // Asignar IDs de streams de página y objetos de página
        $pageObjIds   = [];
        $pageStreamIds = [];
        foreach ($this->pageStreams as $i => $_) {
            $pageStreamIds[$i] = $nextId++;
            $pageObjIds[$i]    = $nextId++;
        }

        // ── 2. Construir diccionarios de página ───────────────────────────
        $resources = "/Font << /F1 {$fontRegId} 0 R /F2 {$fontBldId} 0 R >>";
        if ($imageXObjId !== null) {
            $resources .= " /XObject << /Wm1 {$imageXObjId} 0 R >>";
            $resources .= " /ExtGState << /GSwm {$extGStateId} 0 R >>";
        }

        $pageRefs = implode(' ', array_map(fn($id) => "{$id} 0 R", $pageObjIds));

        // ── 3. Colección ordenada de todos los objetos ────────────────────
        // Cada entrada: [id => ['dict' => string, 'stream' => string|null]]
        $objects = [];

        $objects[$catalogId] = ['dict' => "<< /Type /Catalog /Pages {$pagesId} 0 R >>",         'stream' => null];
        $objects[$pagesId]   = ['dict' => "<< /Type /Pages /Kids [{$pageRefs}] /Count " . count($pageObjIds) . " >>", 'stream' => null];
        $objects[$fontRegId] = ['dict' => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>",      'stream' => null];
        $objects[$fontBldId] = ['dict' => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>", 'stream' => null];

        if ($imageXObjId !== null && $imageObjData !== null) {
            $opacity = $this->watermark['opacity'];
            $objects[$extGStateId]  = ['dict' => "<< /Type /ExtGState /ca {$opacity} /CA {$opacity} /BM /Normal >>", 'stream' => null];
            $objects[$imageXObjId]  = ['dict' => $imageObjData['header'], 'stream' => $imageObjData['stream']];

            // SMask: objeto de imagen en escala de grises con el canal alpha
            if ($smaskObjId !== null && $imageObjData['smask_stream'] !== null) {
                $sw = $imageObjData['w'];
                $sh = $imageObjData['h'];
                $smaskHeader =
                    "<< /Type /XObject /Subtype /Image\n" .
                    "   /Width {$sw} /Height {$sh}\n" .
                    "   /ColorSpace /DeviceGray\n" .
                    "   /BitsPerComponent 8\n" .
                    "   /Filter /FlateDecode\n" .
                    "   /Length " . strlen($imageObjData['smask_stream']) . "\n" .
                    ">>";
                $objects[$smaskObjId] = ['dict' => $smaskHeader, 'stream' => $imageObjData['smask_stream']];
            }
        }

        foreach ($this->pageStreams as $i => $content) {
            $sid = $pageStreamIds[$i];
            $pid = $pageObjIds[$i];

            $objects[$sid] = [
                'dict'   => "<< /Length " . strlen($content) . " >>",
                'stream' => $content,
            ];
            $objects[$pid] = [
                'dict' =>
                    "<< /Type /Page\n" .
                    "   /Parent {$pagesId} 0 R\n" .
                    "   /MediaBox [0 0 " . self::PW . " " . self::PH . "]\n" .
                    "   /Contents {$sid} 0 R\n" .
                    "   /Resources << {$resources} >>\n" .
                    ">>",
                'stream' => null,
            ];
        }

        // ── 4. Serializar objetos y construir xref ────────────────────────
        $pdf  = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
        $xref = [];

        // Ordenar por ID para xref secuencial
        ksort($objects);

        foreach ($objects as $id => $obj) {
            $xref[$id] = strlen($pdf);

            if ($obj['stream'] !== null) {
                // Stream binario: separar con \n exacto, sin espacios extra
                $pdf .= "{$id} 0 obj\n"
                      . $obj['dict'] . "\n"
                      . "stream\n"
                      . $obj['stream']
                      . "\nendstream\nendobj\n";
            } else {
                $pdf .= "{$id} 0 obj\n" . $obj['dict'] . "\nendobj\n";
            }
        }

        // ── 5. Tabla xref ─────────────────────────────────────────────────
        $xrefOffset = strlen($pdf);
        $maxId      = max(array_keys($xref));

        $pdf .= "xref\n0 " . ($maxId + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            if (isset($xref[$i])) {
                $pdf .= str_pad($xref[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
            } else {
                $pdf .= "0000000000 65535 f \n"; // objeto libre si hay hueco
            }
        }

        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root {$catalogId} 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    /**
     * Dibuja la marca de agua centrada en la página actual usando el XObject.
     * Se llama al inicio de addPage() para que quede debajo del contenido.
     */
    /**
     * Dibuja la marca de agua en cuadrícula repetida por toda la página.
     */
    private function drawWatermark(): void
    {
        $wm   = $this->watermark;
        $imgW = round(self::PW * $wm['scale'], 4);
        $imgH = round($imgW * ($wm['height'] / $wm['width']), 4);
        $gapX = round($imgW * 0.4, 4);
        $gapY = round($imgH * 0.4, 4);

        $stream = '';

        // Iterar desde Y=0 (abajo) hacia arriba cubriendo toda la página
        $y = 0;
        while ($y < self::PH) {
            $x = 0;
            while ($x < self::PW) {
                // cm: escala imgW×imgH y traslada a (x, y)
                $stream .=
                    "q\n" .
                    "/GSwm gs\n" .
                    "{$imgW} 0 0 {$imgH} {$x} {$y} cm\n" .
                    "/Wm1 Do\n" .
                    "Q\n";
                $x = round($x + $imgW + $gapX, 4);
            }
            $y = round($y + $imgH + $gapY, 4);
        }

        $this->pageStreams[$this->currentPage] .= $stream;
    }

    /**
     * Parsea el PNG, separa canal RGB y canal alpha.
     * El alpha se devuelve como smask_stream para embeberse como SMask.
     *
     * @return array{header: string, stream: string, smask_stream: string|null, w: int, h: int}
     */
    private function buildImageXObject(int $objId, array $wm): array
    {
        $fallback = [
            'header'       => "<< /Type /XObject /Subtype /Image /Width 1 /Height 1 /ColorSpace /DeviceRGB /BitsPerComponent 8 /Length 3 >>",
            'stream'       => "\x00\x00\x00",
            'smask_stream' => null,
            'w'            => 1,
            'h'            => 1,
        ];

        $raw = file_get_contents($wm['path']);
        if ($raw === false) return $fallback;

        $w = $wm['width'];
        $h = $wm['height'];

        // ColorType del IHDR byte 25: 0=Gray 2=RGB 3=Indexed 4=Gray+A 6=RGBA
        $colorType   = ord(substr($raw, 25, 1));
        $isRGB       = in_array($colorType, [2, 6], true);
        $hasAlpha    = in_array($colorType, [4, 6], true);
        $channels    = $isRGB ? ($hasAlpha ? 4 : 3) : ($hasAlpha ? 2 : 1);
        $colorSpace  = $isRGB ? '/DeviceRGB' : '/DeviceGray';
        $outChannels = $isRGB ? 3 : 1;

        // Extraer IDAT
        $idat   = '';
        $offset = 8;
        $len    = strlen($raw);
        while ($offset + 12 <= $len) {
            $chunkLen  = unpack('N', substr($raw, $offset, 4))[1];
            $chunkType = substr($raw, $offset + 4, 4);
            if ($chunkType === 'IDAT') {
                $idat .= substr($raw, $offset + 8, $chunkLen);
            } elseif ($chunkType === 'IEND') {
                break;
            }
            $offset += 12 + $chunkLen;
        }

        if ($idat === '') return $fallback;

        $decompressed = @\zlib_decode($idat);
        if ($decompressed === false) return $fallback;

        // Reconstruir píxeles separando RGB y alpha
        $rowBytes  = $w * $channels;
        $rgbData   = '';
        $alphaData = '';
        $prevRow   = str_repeat("\x00", $rowBytes);

        for ($row = 0; $row < $h; $row++) {
            $rowStart   = $row * ($rowBytes + 1);
            $filterByte = ord(substr($decompressed, $rowStart, 1));
            $rowData    = substr($decompressed, $rowStart + 1, $rowBytes);
            $rowData    = $this->applyPngFilter($filterByte, $rowData, $prevRow, $channels);
            $prevRow    = $rowData;

            for ($col = 0; $col < $w; $col++) {
                $px = $col * $channels;
                $rgbData .= substr($rowData, $px, $outChannels);
                if ($hasAlpha) {
                    $alphaData .= $rowData[$px + $outChannels];
                }
            }
        }

        $rgbCompressed = @\zlib_encode($rgbData, ZLIB_ENCODING_DEFLATE, 6);
        if ($rgbCompressed === false) return $fallback;

        $smaskCompressed = null;
        if ($hasAlpha && $alphaData !== '') {
            $smaskCompressed = @\zlib_encode($alphaData, ZLIB_ENCODING_DEFLATE, 6);
            if ($smaskCompressed === false) $smaskCompressed = null;
        }

        // Placeholder para el ID del SMask — output() lo reemplazará
        $smaskLine = $smaskCompressed !== null ? "   /SMask __SMASK_ID__ 0 R\n" : '';

        $header =
            "<< /Type /XObject /Subtype /Image\n" .
            "   /Width {$w} /Height {$h}\n" .
            "   /ColorSpace {$colorSpace}\n" .
            "   /BitsPerComponent 8\n" .
            "   /Filter /FlateDecode\n" .
            "   /Length " . strlen($rgbCompressed) . "\n" .
            $smaskLine .
            ">>";

        return [
            'header'       => $header,
            'stream'       => $rgbCompressed,
            'smask_stream' => $smaskCompressed,
            'w'            => $w,
            'h'            => $h,
        ];
    }

    /**
     * Aplica el filtro inverso PNG a una fila de datos.
     * https://www.w3.org/TR/PNG/#9Filters
     */
    private function applyPngFilter(int $filter, string $row, string $prev, int $bpp): string
    {
        $len = strlen($row);

        if ($filter === 0) {
            // None: sin cambios
            return $row;
        }

        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $x  = ord($row[$i]);
            $a  = $i >= $bpp ? ord($out[$i - $bpp]) : 0; // byte izquierdo
            $b  = ord($prev[$i]);                          // byte arriba
            $c  = $i >= $bpp ? ord($prev[$i - $bpp]) : 0; // byte arriba-izquierda

            $byte = match ($filter) {
                1 => ($x + $a) & 0xFF,                          // Sub
                2 => ($x + $b) & 0xFF,                          // Up
                3 => ($x + (int)(($a + $b) / 2)) & 0xFF,        // Average
                4 => ($x + $this->paethPredictor($a, $b, $c)) & 0xFF, // Paeth
                default => $x,
            };

            $out .= chr($byte);
        }

        return $out;
    }

    /** Predictor de Paeth para filtro PNG tipo 4 */
    private function paethPredictor(int $a, int $b, int $c): int
    {
        $p  = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);

        if ($pa <= $pb && $pa <= $pc) return $a;
        if ($pb <= $pc) return $b;
        return $c;
    }

    private function wrapText(string $text, int $maxChars): array
    {
        if ($text === '' || $maxChars <= 0) {
            return [$text];
        }

        $words   = explode(' ', $text);
        $lines   = [];
        $current = '';

        foreach ($words as $word) {
            if (mb_strlen($word) > $maxChars) {
                if ($current !== '') {
                    $lines[]  = $current;
                    $current  = '';
                }
                while (mb_strlen($word) > $maxChars) {
                    $lines[] = mb_substr($word, 0, $maxChars - 1) . '-';
                    $word    = mb_substr($word, $maxChars - 1);
                }
                $current = $word;
                continue;
            }

            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (mb_strlen($candidate) <= $maxChars) {
                $current = $candidate;
            } else {
                $lines[]  = $current;
                $current  = $word;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines ?: [''];
    }

    private function rect(float $x, float $y, float $w, float $h): void
    {
        $this->pageStreams[$this->currentPage] .=
            "0.4 0.4 0.4 RG 0.3 w {$x} {$y} {$w} {$h} re S 0 0 0 RG\n";
    }

    private function escape(string $text): string
    {
        $text = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}