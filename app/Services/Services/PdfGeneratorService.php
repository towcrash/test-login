<?php

namespace App\Services\Services;

class PdfGeneratorService
{
    public const PW           = 612;
    public const PH           = 935;
    public const MARGIN_LEFT  = 45;
    public const MARGIN_RIGHT = 567;
    public const MARGIN_TOP   = 890;
    public const MARGIN_BOT   = 55;
    public const FONT_SIZE    = 10;
    public const CHAR_WIDTH   = 5.5;

    private array  $pageStreams = [];
    private int    $currentPage = -1;
    private float  $cursorY     = self::MARGIN_TOP;
    private ?array $watermark   = null;
    private ?array $headerLogo  = null;
    private array  $footerLogos = []; // hasta 5 logos para el footer

    // ── API pública ───────────────────────────────────────────────────────

    public function reset(): void
    {
        $this->pageStreams  = [];
        $this->currentPage = -1;
        $this->cursorY     = self::MARGIN_TOP;
        $this->watermark   = null;
        $this->headerLogo  = null;
        $this->footerLogos = [];
    }

    public function getCursorY(): float { return $this->cursorY; }

    /**
     * Lee dimensiones de un PNG verdadero desde su header IHDR.
     * Devuelve [w, h] o [0, 0] si no es un PNG válido.
     */
    private function readPngDimensions(string $path): array
    {
        $hdr = file_get_contents($path, false, null, 0, 24);
        if (!$hdr || strlen($hdr) < 24 || substr($hdr, 0, 8) !== "\x89PNG\r\n\x1a\n") return [0, 0];
        $w = unpack('N', substr($hdr, 16, 4))[1];
        $h = unpack('N', substr($hdr, 20, 4))[1];
        return ($w > 0 && $h > 0) ? [$w, $h] : [0, 0];
    }

    public function setWatermark(string $pngPath, float $opacity = 0.12, float $scale = 0.18): void
    {
        if (!file_exists($pngPath)) return;
        [$w, $h] = $this->readPngDimensions($pngPath);
        if ($w <= 0 || $h <= 0) return;
        $this->watermark = [
            'path'    => $pngPath, 'width' => $w, 'height' => $h,
            'opacity' => max(0.0, min(1.0, $opacity)),
            'scale'   => max(0.1, min(1.0, $scale)),
        ];
    }

    public function setHeaderLogo(string $pngPath, float $maxW = 70.0): void
    {
        if (!file_exists($pngPath)) return;
        [$w, $h] = $this->readPngDimensions($pngPath);
        if ($w <= 0 || $h <= 0) return;
        $scale = min(1.0, $maxW / $w);
        $this->headerLogo = [
            'path'  => $pngPath, 'width' => $w, 'height' => $h,
            'dispW' => round($w * $scale, 2),
            'dispH' => round($h * $scale, 2),
        ];
    }

    /**
     * Agrega un PNG al footer (hasta 5). Llamar una vez por logo.
     * Si el archivo no existe o no es PNG válido se ignora silenciosamente.
     * Para omitir un logo basta con comentar su línea en el llamador.
     */
    public function addFooterLogo(string $pngPath, float $maxH = 22.0): void
    {
        if (count($this->footerLogos) >= 5) return;
        if (!file_exists($pngPath)) return;
        [$w, $h] = $this->readPngDimensions($pngPath);
        if ($w <= 0 || $h <= 0) return;
        $scale = min(1.0, $maxH / $h);
        $this->footerLogos[] = [
            'path'  => $pngPath, 'width' => $w, 'height' => $h,
            'dispW' => round($w * $scale, 2),
            'dispH' => round($h * $scale, 2),
        ];
    }


    public function addPage(): void
    {
        $this->currentPage++;
        $this->pageStreams[$this->currentPage] = '';
        $this->cursorY = self::MARGIN_TOP;
        if ($this->watermark !== null) $this->drawWatermark();
        if ($this->currentPage === 0 && $this->headerLogo !== null) {
            $this->drawHeaderLogo();
        }
        if (!empty($this->footerLogos)) $this->drawFooter();
    }

    public function text(float $x, float $y, string $txt, float $size = 0, bool $bold = false): void
    {
        $size = $size ?: self::FONT_SIZE;
        $font = $bold ? 'F2' : 'F1';
        $safe = $this->escape($txt);
        $this->pageStreams[$this->currentPage] .=
            "BT /{$font} {$size} Tf {$x} {$y} Td ({$safe}) Tj ET\n";
    }

    public function line(string $txt, float $size = 0, bool $bold = false): void
    {
        if ($this->cursorY < self::MARGIN_BOT + 20) $this->addPage();
        $this->text(self::MARGIN_LEFT, $this->cursorY, $txt, $size, $bold);
        $this->cursorY -= ($size ?: self::FONT_SIZE) + 4;
    }

    /**
     * Dibuja una línea con label y valor tabulados verticalmente.
     * El ":" y el valor siempre arrancan en la misma columna X.
     *
     * @param string $label  Texto del label (ej: "Contratista")
     * @param string $value  Texto del valor
     * @param float  $tabX   X absoluta donde se dibuja el ":"  (relativa a MARGIN_LEFT)
     * @param float  $size   Tamaño de fuente
     * @param bool   $bold   Si el valor va en negrita
     */
    public function tabbedLine(string $label, string $value, float $tabX, float $size = 0, bool $boldValue = false): void
    {
        if ($this->cursorY < self::MARGIN_BOT + 20) $this->addPage();
        $size = $size ?: self::FONT_SIZE;
        $x    = self::MARGIN_LEFT;
        $y    = $this->cursorY;

        $this->text($x, $y, $label, $size, false);
        $this->text($x + $tabX, $y, ': ' . $value, $size, $boldValue);
        $this->cursorY -= $size + 4;
    }

    public function spacer(float $pts = 10): void
    {
        $this->cursorY -= $pts;
        if ($this->cursorY < self::MARGIN_BOT + 20) $this->addPage();
    }

    public function hLine(): void
    {
        $y = $this->cursorY;
        $this->pageStreams[$this->currentPage] .=
            "0.6 0.6 0.6 RG 0.3 w " .
            self::MARGIN_LEFT . " {$y} m " . self::MARGIN_RIGHT . " {$y} l S\n" .
            "0 0 0 RG\n";
        $this->cursorY -= 6;
    }

    /**
     * Dibuja una tarjeta de pregunta:
     *
     *  ┌─────────────────────────────────────────── (caja gris muy suave)
     *  │ Pregunta: {texto pregunta}
     *  └───────────────────────────────────────────
     *  ┌────────────────────┐ ┌────────────────────┐ ┌──────────┐
     *  │ Resp. Esperada     │ │ Resp. Colaborador  │ │  ✓ / ✗  │
     *  │ {esperada}         │ │ {dada}             │ │          │
     *  └────────────────────┘ └────────────────────┘ └──────────┘
     */
    public function questionCard(string $pregunta, string $esperada, string $dada, bool $correcta, bool $permanent = false): void
    {
        $availW = self::MARGIN_RIGHT - self::MARGIN_LEFT; // 522
        $pad    = 6;
        $lineH  = self::FONT_SIZE + 3;
        $labelW = 58; // "Pregunta: " en bold ~58pt

        // Anchos cajas respuesta
        $gap = 6;
        if ($permanent) {
            // Solo caja colaborador a ancho completo
            $wEsp = 0;
            $wCol = $availW;
            $wRes = 0;
        } else {
            // 45% esperada / 45% colaborador / 10% resultado
            $innerW = $availW - ($gap * 2);
            $wEsp   = (int) round($innerW * 0.45);
            $wCol   = (int) round($innerW * 0.45);
            $wRes   = $innerW - $wEsp - $wCol;
        }

        // Calcular líneas de la pregunta (sin el label)
        $qMaxChars = (int) floor(($availW - $pad * 2) / self::CHAR_WIDTH);
        $qLines    = $this->wrapText($pregunta, $qMaxChars);
        // Si cabe en 1 línea junto al label, usar ese ancho reducido
        $qInline   = count($qLines) === 1 &&
                     mb_strlen($pregunta) <= (int) floor(($availW - $pad * 2 - $labelW) / self::CHAR_WIDTH);

        $qBoxH = ($qInline ? 1 : count($qLines) + 1) * $lineH + $pad * 2;

        // Calcular líneas de respuestas (sin label, solo texto)
        $espLines = $permanent ? [] : $this->wrapText($esperada, (int) floor(($wEsp - $pad * 2) / self::CHAR_WIDTH));
        $colLines = $this->wrapText($dada,     (int) floor(($wCol - $pad * 2) / self::CHAR_WIDTH));
        $respBoxH = max(count($espLines), count($colLines), 1) * $lineH + $pad * 2;

        $totalH = $qBoxH + $respBoxH + $gap + 10;

        if ($this->cursorY - $totalH < self::MARGIN_BOT + 10) $this->addPage();

        $xL   = self::MARGIN_LEFT;
        $yTop = $this->cursorY;

        // ── Caja pregunta (invisible — sin relleno ni borde) ─────────────
        $qBotY = $yTop - $qBoxH;
        // Sin filledBox — la caja es completamente transparente

        // Label "Pregunta:" bold + texto
        $yLabelQ = $yTop - $pad - self::FONT_SIZE;
        $this->text($xL + $pad, $yLabelQ, 'Pregunta:', self::FONT_SIZE, true);

        if ($qInline) {
            $this->text($xL + $pad + $labelW, $yLabelQ, $pregunta, self::FONT_SIZE, false);
        } else {
            // Multilínea: primera línea al lado del label, resto debajo
            $firstMaxChars = (int) floor(($availW - $pad * 2 - $labelW) / self::CHAR_WIDTH);
            $firstLine     = mb_strimwidth($pregunta, 0, $firstMaxChars);
            $this->text($xL + $pad + $labelW, $yLabelQ, $firstLine, self::FONT_SIZE, false);

            // Líneas siguientes sin label
            $remaining = mb_substr($pregunta, mb_strlen($firstLine));
            $restLines = $this->wrapText(trim($remaining), $qMaxChars);
            $yLine     = $yLabelQ - $lineH;
            foreach ($restLines as $rl) {
                $this->pageStreams[$this->currentPage] .=
                    "q {$xL} {$qBotY} {$availW} {$qBoxH} re W n\n";
                $this->text($xL + $pad, $yLine, $rl, self::FONT_SIZE, false);
                $this->pageStreams[$this->currentPage] .= "Q\n";
                $yLine -= $lineH;
            }
        }

        // ── Cajas de respuesta ────────────────────────────────────────────
        $rTop = $qBotY - $gap;
        $rBot = $rTop - $respBoxH;

        if (!$permanent) {
            // -- Amarilla: Resp. Esperada
            $xEsp = $xL;
            $this->filledBox($xEsp, $rBot, $wEsp, $respBoxH,
                fill:   [1.00, 0.98, 0.80],
                stroke: [0.85, 0.70, 0.10]
            );
            $yLine = $rTop - $pad - self::FONT_SIZE;
            foreach ($espLines as $el) {
                $this->pageStreams[$this->currentPage] .=
                    "q\n" . $this->roundedRectPath($xEsp, $rBot, $wEsp, $respBoxH) . "W n\n";
                $this->text($xEsp + $pad, $yLine, $el, self::FONT_SIZE, false);
                $this->pageStreams[$this->currentPage] .= "Q\n";
                $yLine -= $lineH;
            }
        }

        // -- Celeste: Resp. Colaborador
        $xCol = $permanent ? $xL : ($xL + $wEsp + $gap);
        $this->filledBox($xCol, $rBot, $wCol, $respBoxH,
            fill:   [0.88, 0.95, 1.00],
            stroke: [0.25, 0.55, 0.85]
        );
        $yLine = $rTop - $pad - self::FONT_SIZE;
        foreach ($colLines as $cl) {
            $this->pageStreams[$this->currentPage] .=
                "q\n" . $this->roundedRectPath($xCol, $rBot, $wCol, $respBoxH) . "W n\n";
            $this->text($xCol + $pad, $yLine, $cl, self::FONT_SIZE, false);
            $this->pageStreams[$this->currentPage] .= "Q\n";
            $yLine -= $lineH;
        }

        if (!$permanent) {
            // -- Resultado (✓/✗)
            $xRes = $xCol + $wCol + $gap;
            if ($correcta) {
                $this->filledBox($xRes, $rBot, $wRes, $respBoxH,
                    fill: [0.90, 1.00, 0.90], stroke: [0.28, 0.72, 0.28]);
            } else {
                $this->filledBox($xRes, $rBot, $wRes, $respBoxH,
                    fill: [1.00, 0.92, 0.92], stroke: [0.82, 0.28, 0.28]);
            }
            $cx = $xRes + $wRes / 2;
            $cy = $rBot  + $respBoxH / 2;
            $this->drawSymbol($cx, $cy, $correcta);
        }

        $this->cursorY = $rBot - 8;
    }

    /**
     * Dibuja la leyenda hacia abajo desde $yTop (coordenada PDF, borde superior).
     * En modo $soloColaborador muestra solo el ítem azul (1 fila).
     */
    public function legend(float $xLeft, float $yTop, bool $soloColaborador = false): void
    {
        $fs     = 8;
        $bW     = 10; $bH = 10;
        $gapI   = 4;
        $gapC   = 10;
        $gapR   = 6;
        $padX   = 8; $padY = 6;
        $lineH  = $bH + $gapR;
        $titleH = $fs + 4;

        $items = $soloColaborador
            ? [['fill' => [0.88, 0.95, 1.00], 'stroke' => [0.25, 0.55, 0.85], 'label' => 'Resp. Colaborador']]
            : [
                ['fill' => [1.00, 0.98, 0.80], 'stroke' => [0.85, 0.70, 0.10], 'label' => 'Resp. Esperada'],
                ['fill' => [0.88, 0.95, 1.00], 'stroke' => [0.25, 0.55, 0.85], 'label' => 'Resp. Colaborador'],
                ['fill' => [0.90, 1.00, 0.90], 'stroke' => [0.28, 0.72, 0.28], 'label' => 'Correcto'],
                ['fill' => [1.00, 0.92, 0.92], 'stroke' => [0.82, 0.28, 0.28], 'label' => 'Incorrecto'],
              ];

        $cols    = 2;
        $numRows = (int) ceil(count($items) / $cols);
        $colW    = $bW + $gapI + 90;

        // Título "Leyenda:" — dibujado desde yTop hacia abajo
        $this->text($xLeft + $padX, $yTop - $padY - $fs, 'Leyenda:', $fs, true);

        foreach ($items as $idx => $item) {
            $col = $idx % $cols;
            $row = (int)($idx / $cols);
            $ix  = $xLeft + $padX + $col * ($colW + $gapC);
            // Cada fila baja: título + padding + fila*lineH
            $iy  = $yTop - $padY - $titleH - ($row * $lineH) - $bH;
            $this->filledBox($ix, $iy, $bW, $bH, $item['fill'], $item['stroke']);
            $this->text($ix + $bW + $gapI, $iy + 1, $item['label'], $fs, false);
        }
    }

    public function output(): string
    {
        $nextId    = 1;
        $catalogId = $nextId++;
        $pagesId   = $nextId++;
        $fontRegId = $nextId++;
        $fontBldId = $nextId++;
        $gsBoxId   = $nextId++; // ExtGState: relleno semitransparente de cajas (fill opacity 0.18)

        $imageXObjId  = null;
        $extGStateId  = null;
        $smaskObjId   = null;
        $imageObjData = null;

        if ($this->watermark !== null) {
            $imageXObjId  = $nextId++;
            $extGStateId  = $nextId++;
            $imageObjData = $this->buildImageXObject($imageXObjId, $this->watermark);
            if ($imageObjData['smask_stream'] !== null) {
                $smaskObjId = $nextId++;
                $imageObjData['header'] = str_replace('__SMASK_ID__', (string) $smaskObjId, $imageObjData['header']);
            }
        }

        // Logo de cabecera (esquina superior derecha, primera página)
        $hLogoXObjId  = null;
        $hLogoSmaskId = null;
        $hLogoObjData = null;

        if ($this->headerLogo !== null) {
            $hLogoXObjId  = $nextId++;
            $hLogoObjData = $this->buildImageXObject($hLogoXObjId, $this->headerLogo);
            if ($hLogoObjData['smask_stream'] !== null) {
                $hLogoSmaskId = $nextId++;
                $hLogoObjData['header'] = str_replace('__SMASK_ID__', (string) $hLogoSmaskId, $hLogoObjData['header']);
            }
        }

        // Logos de footer (hasta 5, en todas las páginas)
        $fLogoData = []; // [['xobjId', 'smaskId', 'objData'], ...]
        foreach ($this->footerLogos as $idx => $fLogo) {
            $xobjId  = $nextId++;
            $objData = $this->buildImageXObject($xobjId, $fLogo);
            $smaskId = null;
            if ($objData['smask_stream'] !== null) {
                $smaskId = $nextId++;
                $objData['header'] = str_replace('__SMASK_ID__', (string) $smaskId, $objData['header']);
            }
            $fLogoData[] = ['xobjId' => $xobjId, 'smaskId' => $smaskId, 'objData' => $objData];
        }

        $pageStreamIds = [];
        $pageObjIds    = [];
        foreach ($this->pageStreams as $i => $_) {
            $pageStreamIds[$i] = $nextId++;
            $pageObjIds[$i]    = $nextId++;
        }

        $extGStateDict = "/GsBox {$gsBoxId} 0 R";
        if ($imageXObjId !== null) {
            $extGStateDict .= " /GSwm {$extGStateId} 0 R";
        }

        $xobjDict = '';
        if ($imageXObjId !== null)  $xobjDict .= "/Wm1 {$imageXObjId} 0 R ";
        if ($hLogoXObjId !== null)  $xobjDict .= "/HLogo {$hLogoXObjId} 0 R ";
        foreach ($fLogoData as $idx => $fl) {
            $xobjDict .= "/FLogo{$idx} {$fl['xobjId']} 0 R ";
        }

        $resources  = "/Font << /F1 {$fontRegId} 0 R /F2 {$fontBldId} 0 R >>";
        $resources .= " /ExtGState << {$extGStateDict} >>";
        if ($xobjDict !== '') {
            $resources .= " /XObject << {$xobjDict}>>";
        }

        $pageRefs = implode(' ', array_map(fn($id) => "{$id} 0 R", $pageObjIds));
        $objects  = [];

        $objects[$catalogId] = ['dict' => "<< /Type /Catalog /Pages {$pagesId} 0 R >>",         'stream' => null];
        $objects[$pagesId]   = ['dict' => "<< /Type /Pages /Kids [{$pageRefs}] /Count " . count($pageObjIds) . " >>", 'stream' => null];
        $objects[$fontRegId] = ['dict' => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>",      'stream' => null];
        $objects[$fontBldId] = ['dict' => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>", 'stream' => null];

        // GsBox: fill opacity 0.05, stroke opacidad 1.0 (CA=1)
        $objects[$gsBoxId] = ['dict' => "<< /Type /ExtGState /ca 0.05 /CA 0.4 /BM /Normal >>", 'stream' => null];

        if ($imageXObjId !== null && $imageObjData !== null) {
            $opacity = $this->watermark['opacity'];
            $objects[$extGStateId] = ['dict' => "<< /Type /ExtGState /ca {$opacity} /CA {$opacity} /BM /Normal >>", 'stream' => null];
            $objects[$imageXObjId] = ['dict' => $imageObjData['header'], 'stream' => $imageObjData['stream']];
            if ($smaskObjId !== null && $imageObjData['smask_stream'] !== null) {
                $sw = $imageObjData['w']; $sh = $imageObjData['h'];
                $objects[$smaskObjId] = [
                    'dict' =>
                        "<< /Type /XObject /Subtype /Image\n" .
                        "   /Width {$sw} /Height {$sh}\n" .
                        "   /ColorSpace /DeviceGray /BitsPerComponent 8\n" .
                        "   /Filter /FlateDecode /Length " . strlen($imageObjData['smask_stream']) . "\n>>",
                    'stream' => $imageObjData['smask_stream'],
                ];
            }
        }

        if ($hLogoXObjId !== null && $hLogoObjData !== null) {
            $objects[$hLogoXObjId] = ['dict' => $hLogoObjData['header'], 'stream' => $hLogoObjData['stream']];
            if ($hLogoSmaskId !== null && $hLogoObjData['smask_stream'] !== null) {
                $sw = $hLogoObjData['w']; $sh = $hLogoObjData['h'];
                $objects[$hLogoSmaskId] = [
                    'dict' =>
                        "<< /Type /XObject /Subtype /Image\n" .
                        "   /Width {$sw} /Height {$sh}\n" .
                        "   /ColorSpace /DeviceGray /BitsPerComponent 8\n" .
                        "   /Filter /FlateDecode /Length " . strlen($hLogoObjData['smask_stream']) . "\n>>",
                    'stream' => $hLogoObjData['smask_stream'],
                ];
            }
        }

        foreach ($fLogoData as $fl) {
            $objects[$fl['xobjId']] = ['dict' => $fl['objData']['header'], 'stream' => $fl['objData']['stream']];
            if ($fl['smaskId'] !== null && $fl['objData']['smask_stream'] !== null) {
                $sw = $fl['objData']['w']; $sh = $fl['objData']['h'];
                $objects[$fl['smaskId']] = [
                    'dict' =>
                        "<< /Type /XObject /Subtype /Image\n" .
                        "   /Width {$sw} /Height {$sh}\n" .
                        "   /ColorSpace /DeviceGray /BitsPerComponent 8\n" .
                        "   /Filter /FlateDecode /Length " . strlen($fl['objData']['smask_stream']) . "\n>>",
                    'stream' => $fl['objData']['smask_stream'],
                ];
            }
        }

        foreach ($this->pageStreams as $i => $content) {
            $sid = $pageStreamIds[$i];
            $pid = $pageObjIds[$i];
            $objects[$sid] = ['dict' => "<< /Length " . strlen($content) . " >>", 'stream' => $content];
            $objects[$pid] = [
                'dict' =>
                    "<< /Type /Page /Parent {$pagesId} 0 R\n" .
                    "   /MediaBox [0 0 " . self::PW . " " . self::PH . "]\n" .
                    "   /Contents {$sid} 0 R\n" .
                    "   /Resources << {$resources} >> >>",
                'stream' => null,
            ];
        }

        ksort($objects);
        $pdf  = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
        $xref = [];
        foreach ($objects as $id => $obj) {
            $xref[$id] = strlen($pdf);
            if ($obj['stream'] !== null) {
                // Concatenación explícita para preservar datos binarios intactos
                $pdf .= $id . " 0 obj\n" . $obj['dict'] . "\nstream\n";
                $pdf .= $obj['stream'];
                $pdf .= "\nendstream\nendobj\n";
            } else {
                $pdf .= $id . " 0 obj\n" . $obj['dict'] . "\nendobj\n";
            }
        }

        $xrefOffset = strlen($pdf);
        $maxId      = max(array_keys($xref));
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            $pdf .= isset($xref[$i])
                ? str_pad($xref[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n"
                : "0000000000 65535 f \n";
        }
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root {$catalogId} 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";
        return $pdf;
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    /**
     * Construye el path PDF de un rectángulo con esquinas redondeadas.
     * r = radio de la esquina en puntos.
     * Usa curvas de Bézier cúbicas: k ≈ 0.5523 para aproximar un cuarto de círculo.
     */
    private function roundedRectPath(float $x, float $y, float $w, float $h, float $r = 5.0): string
    {
        $r  = min($r, $w / 2, $h / 2);
        $k  = 0.5523 * $r; // factor de control Bézier

        // Esquinas: BL=bottom-left, BR=bottom-right, TR=top-right, TL=top-left
        return
            // Empezar en borde inferior, lado derecho del arco BL
            ($x + $r) . " {$y} m\n" .
            // Borde inferior → BR
            ($x + $w - $r) . " {$y} l\n" .
            // Arco BR
            ($x + $w - $r + $k) . " {$y} " . ($x + $w) . " " . ($y + $r - $k) . " " . ($x + $w) . " " . ($y + $r) . " c\n" .
            // Borde derecho → TR
            ($x + $w) . " " . ($y + $h - $r) . " l\n" .
            // Arco TR
            ($x + $w) . " " . ($y + $h - $r + $k) . " " . ($x + $w - $r + $k) . " " . ($y + $h) . " " . ($x + $w - $r) . " " . ($y + $h) . " c\n" .
            // Borde superior → TL
            ($x + $r) . " " . ($y + $h) . " l\n" .
            // Arco TL
            ($x + $r - $k) . " " . ($y + $h) . " {$x} " . ($y + $h - $r + $k) . " {$x} " . ($y + $h - $r) . " c\n" .
            // Borde izquierdo → BL
            "{$x} " . ($y + $r) . " l\n" .
            // Arco BL
            "{$x} " . ($y + $r - $k) . " " . ($x + $r - $k) . " {$y} " . ($x + $r) . " {$y} c\n";
    }

    private function filledBox(float $x, float $y, float $w, float $h, array $fill, array $stroke, float $r = 5.0): void
    {
        [$fr, $fg, $fb] = $fill;
        [$sr, $sg, $sb] = $stroke;
        $path = $this->roundedRectPath($x, $y, $w, $h, $r);

        $this->pageStreams[$this->currentPage] .=
            // Relleno semitransparente
            "q\n/GsBox gs\n" .
            "{$fr} {$fg} {$fb} rg\n" .
            $path . "f\n" .
            "Q\n" .
            // Borde sólido
            "{$sr} {$sg} {$sb} RG 0.7 w\n" .
            $path . "S\n" .
            "0 0 0 rg 0 0 0 RG\n";
    }

    private function drawSymbol(float $cx, float $cy, bool $correct): void
    {
        $s = 7;
        if ($correct) {
            $this->pageStreams[$this->currentPage] .=
                "0.18 0.62 0.22 RG 1.3 w 1 J 1 j\n" .
                ($cx - $s * 0.55) . " " . ($cy + $s * 0.05) . " m\n" .
                ($cx - $s * 0.08) . " " . ($cy - $s * 0.58) . " l\n" .
                ($cx + $s * 0.78) . " " . ($cy + $s * 0.72) . " l S\n" .
                "0 0 0 RG 0.5 w 0 J 0 j\n";
        } else {
            $this->pageStreams[$this->currentPage] .=
                "0.72 0.18 0.18 RG 1.3 w 1 J 1 j\n" .
                ($cx - $s * 0.58) . " " . ($cy + $s * 0.58) . " m\n" .
                ($cx + $s * 0.58) . " " . ($cy - $s * 0.58) . " l S\n" .
                ($cx + $s * 0.58) . " " . ($cy + $s * 0.58) . " m\n" .
                ($cx - $s * 0.58) . " " . ($cy - $s * 0.58) . " l S\n" .
                "0 0 0 RG 0.5 w 0 J 0 j\n";
        }
    }

    /**
     * Dibuja el logo en la esquina superior izquierda de la página actual.
     */
    private function drawHeaderLogo(): void
    {
        $logo = $this->headerLogo;
        $w    = $logo['dispW'];
        $h    = $logo['dispH'];
        $x    = self::MARGIN_RIGHT - $w;
        $y    = self::MARGIN_TOP - $h;

        $this->pageStreams[$this->currentPage] .=
            "q\n{$w} 0 0 {$h} {$x} {$y} cm\n/HLogo Do\nQ\n";
    }

    /**
     * Dibuja el footer en la parte inferior de la página actual.
     * Estructura: línea separadora | "Certificado por:" | logos en fila
     */
    private function drawFooter(): void
    {
        $footerH  = 28;
        $gapLogos = 8;
        $fs       = 7.5;
        $labelTxt = 'Certificado por:';
        $labelW   = 72;

        // Y base del footer (fondo de los logos centrado verticalmente en footerH)
        $yBase   = self::MARGIN_BOT - 5;
        $yCenter = $yBase + $footerH / 2;

        // Línea separadora
        $this->pageStreams[$this->currentPage] .=
            "0.75 0.75 0.75 RG 0.3 w " .
            self::MARGIN_LEFT . " " . ($yBase + $footerH) . " m " .
            self::MARGIN_RIGHT . " " . ($yBase + $footerH) . " l S\n" .
            "0 0 0 RG\n";

        // Texto "Certificado por:"
        $this->text(
            self::MARGIN_LEFT,
            $yCenter - $fs / 2,
            $labelTxt,
            $fs,
            true
        );

        // Logos: cada uno ya tiene dispW/dispH calculados en setFooterLogos
        $x = self::MARGIN_LEFT + $labelW + $gapLogos;
        foreach ($this->footerLogos as $idx => $logo) {
            $dispW = $logo['dispW'];
            $dispH = $logo['dispH'];
            $yImg  = round($yCenter - $dispH / 2, 2);
            $name  = '/FLogo' . $idx;

            $this->pageStreams[$this->currentPage] .=
                "q\n{$dispW} 0 0 {$dispH} {$x} {$yImg} cm\n{$name} Do\nQ\n";
            $x += $dispW + $gapLogos;
        }
    }

    private function drawWatermark(): void
    {
        $wm   = $this->watermark;
        $imgW = round(self::PW * $wm['scale'], 4);
        $imgH = round($imgW * ($wm['height'] / $wm['width']), 4);
        $x    = round(self::PW - $imgW - 10, 4);
        $y    = round(self::MARGIN_BOT - 10, 4);

        $this->pageStreams[$this->currentPage] .=
            "q\n/GSwm gs\n{$imgW} 0 0 {$imgH} {$x} {$y} cm\n/Wm1 Do\nQ\n";
    }

    private function buildImageXObject(int $objId, array $wm): array
    {
        $fb  = ['header' => "<< /Type /XObject /Subtype /Image /Width 1 /Height 1 /ColorSpace /DeviceRGB /BitsPerComponent 8 /Length 3 >>", 'stream' => "\x00\x00\x00", 'smask_stream' => null, 'w' => 1, 'h' => 1];
        $raw = file_get_contents($wm['path']);
        if (!$raw) return $fb;
        if (substr($raw, 0, 8) !== "\x89PNG\r\n\x1a\n") return $fb;

        $w = $wm['width']; $h = $wm['height'];
        $colorType   = ord($raw[25]);
        $isRGB       = in_array($colorType, [2, 6], true);
        $hasAlpha    = in_array($colorType, [4, 6], true);
        $channels    = $isRGB ? ($hasAlpha ? 4 : 3) : ($hasAlpha ? 2 : 1);
        $colorSpace  = $isRGB ? '/DeviceRGB' : '/DeviceGray';
        $outChannels = $isRGB ? 3 : 1;

        $idat = ''; $offset = 8; $len = strlen($raw);
        while ($offset + 12 <= $len) {
            $cLen  = unpack('N', substr($raw, $offset, 4))[1];
            $cType = substr($raw, $offset + 4, 4);
            if ($cType === 'IDAT') $idat .= substr($raw, $offset + 8, $cLen);
            elseif ($cType === 'IEND') break;
            $offset += 12 + $cLen;
        }
        if (!$idat) return $fb;
        $dec = @\zlib_decode($idat);
        if (!$dec) return $fb;

        $rowBytes = $w * $channels; $rgbData = ''; $alphaData = '';
        $prevRow  = str_repeat("\x00", $rowBytes);
        for ($row = 0; $row < $h; $row++) {
            $rs      = $row * ($rowBytes + 1);
            $filter  = ord($dec[$rs]);
            $rd      = substr($dec, $rs + 1, $rowBytes);
            $rd      = $this->applyPngFilter($filter, $rd, $prevRow, $channels);
            $prevRow = $rd;
            for ($col = 0; $col < $w; $col++) {
                $px = $col * $channels;
                $rgbData .= substr($rd, $px, $outChannels);
                if ($hasAlpha) $alphaData .= $rd[$px + $outChannels];
            }
        }

        $rgbC = @\zlib_encode($rgbData, ZLIB_ENCODING_DEFLATE, 6);
        if (!$rgbC) return $fb;
        $smc = ($hasAlpha && $alphaData) ? (@\zlib_encode($alphaData, ZLIB_ENCODING_DEFLATE, 6) ?: null) : null;
        $sl  = $smc ? "   /SMask __SMASK_ID__ 0 R\n" : '';
        $hdr = "<< /Type /XObject /Subtype /Image\n" .
               "   /Width {$w} /Height {$h}\n" .
               "   /ColorSpace {$colorSpace}\n" .
               "   /BitsPerComponent 8\n" .
               "   /Filter /FlateDecode\n" .
               "   /Length " . strlen($rgbC) . "\n{$sl}>>";
        return ['header' => $hdr, 'stream' => $rgbC, 'smask_stream' => $smc, 'w' => $w, 'h' => $h];
    }

    private function applyPngFilter(int $filter, string $row, string $prev, int $bpp): string
    {
        if ($filter === 0) return $row;
        $len = strlen($row); $out = '';
        for ($i = 0; $i < $len; $i++) {
            $x = ord($row[$i]);
            $a = $i >= $bpp ? ord($out[$i - $bpp]) : 0;
            $b = ord($prev[$i]);
            $c = $i >= $bpp ? ord($prev[$i - $bpp]) : 0;
            $out .= chr(match ($filter) {
                1 => ($x + $a) & 0xFF,
                2 => ($x + $b) & 0xFF,
                3 => ($x + (int)(($a + $b) / 2)) & 0xFF,
                4 => ($x + $this->paethPredictor($a, $b, $c)) & 0xFF,
                default => $x,
            });
        }
        return $out;
    }

    private function paethPredictor(int $a, int $b, int $c): int
    {
        $p = $a + $b - $c;
        $pa = abs($p - $a); $pb = abs($p - $b); $pc = abs($p - $c);
        if ($pa <= $pb && $pa <= $pc) return $a;
        return $pb <= $pc ? $b : $c;
    }

    private function wrapText(string $text, int $maxChars): array
    {
        if ($text === '' || $maxChars <= 0) return [$text];
        $words = explode(' ', $text); $lines = []; $current = '';
        foreach ($words as $word) {
            if (mb_strlen($word) > $maxChars) {
                if ($current !== '') { $lines[] = $current; $current = ''; }
                while (mb_strlen($word) > $maxChars) {
                    $lines[] = mb_substr($word, 0, $maxChars - 1) . '-';
                    $word    = mb_substr($word, $maxChars - 1);
                }
                $current = $word; continue;
            }
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (mb_strlen($candidate) <= $maxChars) { $current = $candidate; }
            else { $lines[] = $current; $current = $word; }
        }
        if ($current !== '') $lines[] = $current;
        return $lines ?: [''];
    }

    private function escape(string $text): string
    {
        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}