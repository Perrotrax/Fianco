<?php

function ticket_parse_price(string $raw): ?float
{
    $clean = preg_replace('/[^\d.,]/', '', trim($raw));
    if ($clean === '') {
        return null;
    }

    $hadDecimal = (strpos($clean, '.') !== false || strpos($clean, ',') !== false);

    if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
        if (strpos($clean, ',') < strpos($clean, '.')) {
            $clean = str_replace(',', '', $clean);
        } else {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }
    } elseif (strpos($clean, ',') !== false) {
        $parts = explode(',', $clean);
        if (isset($parts[1]) && strlen($parts[1]) <= 2) {
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }
    }

    $num = floatval($clean);
    if (!is_finite($num) || $num <= 0 || $num >= 200000) {
        return null;
    }

    if (!$hadDecimal && $num > 3000) {
        return null;
    }

    return round($num, 2);
}

function ticket_prices_from_line(string $line): array
{
    $normalized = preg_replace('/(\d)[oO](\d)/', '$10$2', $line);
    $normalized = preg_replace('/(\d)[oO]/', '$10', $normalized);
    $normalized = preg_replace('/[oO](\d)/', '0$1', $normalized);

    preg_match_all('/(?:[\$€]\s*)?(\d{1,6}(?:[.,]\d{1,2})?)(?!\d)/', $normalized, $matches);
    if (empty($matches[1])) {
        return [];
    }

    $prices = [];
    foreach ($matches[1] as $match) {
        $price = ticket_parse_price($match);
        if ($price !== null) {
            $prices[] = $price;
        }
    }

    return $prices;
}

function ticket_should_skip_line(string $lineLower): bool
{
    $skipKeywords = [
        'rfc', 'cfdi', 'uuid', 'folio', 'ticket', 'factura', 'cajero', 'caja',
        'fecha', 'hora', 'sucursal', 'direccion', 'colonia', 'telefono', 'tel',
        'www.', '.com', 'gracias', 'atendio', 'atendió', 'copia', 'cliente',
        'autorizacion', 'autorización', 'aprobacion', 'aprobación', 'terminal',
        'referencia', 'operacion', 'operación', 'transaccion', 'transacción',
        'banco', 'cuenta', 'clabe', 'regimen', 'régimen', 'codigo', 'código',
        'articulos', 'artículos', 'piezas', 'pzas', 'descripcion', 'descripción',
        'cant', 'importe', 'precio unit', 'precio u', 'ticket no', 'no. ticket'
    ];

    foreach ($skipKeywords as $keyword) {
        if (strpos($lineLower, $keyword) !== false) {
            return true;
        }
    }

    if (preg_match('/^\d{2,4}[-\/\.]\d{1,2}[-\/\.]\d{2,4}/', $lineLower)) {
        return true;
    }

    if (preg_match('/^[a-z0-9]{8}-[a-z0-9]{4}-/i', trim($lineLower))) {
        return true;
    }

    return false;
}

function ticket_is_total_line(string $lineLower): bool
{
    $keywords = [
        'total a pagar', 'monto total', 'importe total', 'pago total', 'gran total',
        'total mxn', 'total usd', 'total eur', 'total:'
    ];

    foreach ($keywords as $keyword) {
        if (strpos($lineLower, $keyword) !== false) {
            return true;
        }
    }

    return preg_match('/\btotal\b/u', $lineLower) === 1
        && strpos($lineLower, 'subtotal') === false
        && strpos($lineLower, 'sub total') === false;
}

function ticket_detect_vendor(string $text): array
{
    $textLower = mb_strtolower($text, 'UTF-8');

    $vendorDictionary = [
        ['keywords' => ['oxxo', '0xx0', 'oxx0'], 'vendor' => 'OXXO', 'cat' => 'Comida'],
        ['keywords' => ['7-eleven', 'seven eleven', '7 eleven', '7-11'], 'vendor' => '7-Eleven', 'cat' => 'Comida'],
        ['keywords' => ['walmart', 'wal-mart'], 'vendor' => 'Walmart', 'cat' => 'Comida'],
        ['keywords' => ['bodega aurrera', 'aurrera'], 'vendor' => 'Bodega Aurrerá', 'cat' => 'Comida'],
        ['keywords' => ['soriana'], 'vendor' => 'Soriana', 'cat' => 'Comida'],
        ['keywords' => ['chedraui'], 'vendor' => 'Chedraui', 'cat' => 'Comida'],
        ['keywords' => ['costco'], 'vendor' => 'Costco', 'cat' => 'Comida'],
        ['keywords' => ["sam's club", 'sams club', 'sams'], 'vendor' => "Sam's Club", 'cat' => 'Comida'],
        ['keywords' => ['pemex'], 'vendor' => 'Pemex', 'cat' => 'Transporte'],
        ['keywords' => ['shell'], 'vendor' => 'Shell', 'cat' => 'Transporte'],
        ['keywords' => ['mcdonald', 'mc donald'], 'vendor' => "McDonald's", 'cat' => 'Comida'],
        ['keywords' => ['starbucks'], 'vendor' => 'Starbucks', 'cat' => 'Comida'],
        ['keywords' => ['burger king'], 'vendor' => 'Burger King', 'cat' => 'Comida'],
        ['keywords' => ['farmacia guadalajara'], 'vendor' => 'Farmacias Guadalajara', 'cat' => 'Servicios'],
        ['keywords' => ['farmacia del ahorro'], 'vendor' => 'Farmacias del Ahorro', 'cat' => 'Servicios'],
        ['keywords' => ['uber'], 'vendor' => 'Uber', 'cat' => 'Transporte'],
        ['keywords' => ['didi'], 'vendor' => 'DiDi', 'cat' => 'Transporte'],
        ['keywords' => ['liverpool'], 'vendor' => 'Liverpool', 'cat' => 'Otros'],
        ['keywords' => ['zara'], 'vendor' => 'ZARA', 'cat' => 'Otros'],
    ];

    foreach ($vendorDictionary as $item) {
        foreach ($item['keywords'] as $keyword) {
            if (strpos($textLower, $keyword) !== false) {
                return ['vendor' => $item['vendor'], 'cat' => $item['cat']];
            }
        }
    }

    $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $text)), static function ($line) {
        return mb_strlen($line, 'UTF-8') > 2;
    }));

    foreach ($lines as $line) {
        $lower = mb_strtolower($line, 'UTF-8');
        if (strpos($lower, 'rfc:') !== false || strpos($lower, 'tel:') !== false || strpos($lower, 'fecha:') !== false) {
            continue;
        }
        if (preg_match('/^\d+([.,]\d{2})?$/', $line) || preg_match('/^\$?\s*\d+([.,]\d{2})?$/', $line)) {
            continue;
        }
        if (mb_strlen($line, 'UTF-8') >= 3 && mb_strlen($line, 'UTF-8') <= 45) {
            $candidate = trim(preg_replace('/[^a-zA-Z0-9&áéíóúÁÉÍÓÚñÑ\s\.\-]/u', '', $line));
            if (mb_strlen($candidate, 'UTF-8') >= 3) {
                return ['vendor' => $candidate, 'cat' => 'Comida'];
            }
        }
    }

    return ['vendor' => 'Ticket Escaneado', 'cat' => 'Comida'];
}

function ticket_detect_payment_method(string $text): ?string
{
    $textLower = mb_strtolower($text, 'UTF-8');

    if (preg_match('/tarjeta|credit|crédito|debito|débito|visa|mastercard|amex/u', $textLower)) {
        return 'Tarjeta';
    }
    if (preg_match('/efectivo|cash|contado/u', $textLower)) {
        return 'Efectivo';
    }
    if (preg_match('/transferencia|spei|clabe/u', $textLower)) {
        return 'Transferencia';
    }

    return null;
}

function ticket_detect_from_filename(string $filename): array
{
    $detectedVendor = 'Ticket Escaneado';
    $detectedMonto = null;
    $detectedCategoria = 'Comida';
    $fnLower = mb_strtolower($filename, 'UTF-8');

    $vendors = [
        'oxxo' => ['OXXO', 'Comida'],
        'walmart' => ['Walmart', 'Comida'],
        'soriana' => ['Soriana', 'Comida'],
        'chedraui' => ['Chedraui', 'Comida'],
        'pemex' => ['Pemex', 'Transporte'],
        'starbucks' => ['Starbucks', 'Comida'],
        'uber' => ['Uber', 'Transporte'],
    ];

    foreach ($vendors as $key => $data) {
        if (strpos($fnLower, $key) !== false) {
            $detectedVendor = $data[0];
            $detectedCategoria = $data[1];
            break;
        }
    }

    if (!preg_match('/^(?:img|dsc|pxl|win|screenshot|foto|capture|cam|recibo|ticket)[_\-\s]*\d+/i', $fnLower)) {
        if (preg_match('/(?:monto|total|price|gasto|_|\$)?(\d+(?:[\._]\d{2}))(?:\.jpg|\.png|\.jpeg|\.webp|\.pdf|$)/i', $fnLower, $matches)) {
            $val = floatval(str_replace('_', '.', $matches[1]));
            if ($val > 0 && $val < 100000 && !in_array((int) $val, [2024, 2025, 2026], true)) {
                $detectedMonto = round($val, 2);
            }
        }
    }

    return [
        'vendor' => $detectedVendor,
        'monto' => $detectedMonto,
        'categoria' => $detectedCategoria,
    ];
}

function ticket_parse_ocr_text(string $text): array
{
    $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $text)), static function ($line) {
        return $line !== '';
    }));

    $items = [];
    $subtotal = null;
    $tax = null;
    $total = null;
    $totalCandidates = [];

    foreach ($lines as $index => $rawLine) {
        $lineLower = mb_strtolower($rawLine, 'UTF-8');
        $prices = ticket_prices_from_line($rawLine);
        if (empty($prices)) {
            continue;
        }

        if (strpos($lineLower, 'subtotal') !== false || strpos($lineLower, 'sub total') !== false) {
            $subtotal = end($prices);
            continue;
        }

        if (preg_match('/\biva\b|\bieps\b/u', $lineLower)) {
            $tax = ($tax ?? 0) + end($prices);
            continue;
        }

        if (ticket_is_total_line($lineLower)) {
            $price = end($prices);
            $priority = 10;
            if (strpos($lineLower, 'total a pagar') !== false || strpos($lineLower, 'gran total') !== false) {
                $priority = 20;
            }
            $totalCandidates[] = ['val' => $price, 'priority' => $priority + ($index / max(count($lines), 1))];
            continue;
        }

        if (ticket_should_skip_line($lineLower)) {
            continue;
        }

        if (preg_match('/^(\d+)\s*[xX\*×]\s*(.+?)\s+([\$€]?\s*\d+(?:[.,]\d{1,2})?)\s*$/u', $rawLine, $match)) {
            $qty = max(1, (int) $match[1]);
            $price = ticket_parse_price($match[3]);
            $name = trim($match[2]);
            if ($price !== null && mb_strlen($name, 'UTF-8') >= 2) {
                $items[] = [
                    'nombre' => $name,
                    'cantidad' => $qty,
                    'precio_unitario' => round($price / $qty, 2),
                    'precio' => $price,
                ];
            }
            continue;
        }

        if (preg_match('/^(.+?)\s+(\d+)\s*[@xX]\s*([\$€]?\s*\d+(?:[.,]\d{1,2})?)\s+([\$€]?\s*\d+(?:[.,]\d{1,2})?)\s*$/u', $rawLine, $match)) {
            $qty = max(1, (int) $match[2]);
            $price = ticket_parse_price($match[4]);
            $name = trim($match[1]);
            if ($price !== null && mb_strlen($name, 'UTF-8') >= 2) {
                $items[] = [
                    'nombre' => $name,
                    'cantidad' => $qty,
                    'precio_unitario' => ticket_parse_price($match[3]) ?? round($price / $qty, 2),
                    'precio' => $price,
                ];
            }
            continue;
        }

        if (preg_match('/^(.+?)\s+([\$€]?\s*\d+(?:[.,]\d{1,2})?)\s*$/u', $rawLine, $match)) {
            $name = trim($match[1]);
            $price = ticket_parse_price($match[2]);
            if ($price === null || mb_strlen($name, 'UTF-8') < 2) {
                continue;
            }
            if (preg_match('/^\d+([.,]\d{2})?$/', $name)) {
                continue;
            }
            if (preg_match('/^(total|iva|ieps|subtotal|descuento|cambio|efectivo|tarjeta)$/iu', $name)) {
                continue;
            }

            $items[] = [
                'nombre' => $name,
                'cantidad' => 1,
                'precio_unitario' => $price,
                'precio' => $price,
            ];
        }
    }

    if (!empty($totalCandidates)) {
        usort($totalCandidates, static function ($a, $b) {
            return $b['priority'] <=> $a['priority'] ?: $b['val'] <=> $a['val'];
        });
        $total = $totalCandidates[0]['val'];
    }

    if ($total === null) {
        foreach ($lines as $rawLine) {
            $lineLower = mb_strtolower($rawLine, 'UTF-8');
            if (ticket_should_skip_line($lineLower) || ticket_is_total_line($lineLower)) {
                continue;
            }
            $prices = ticket_prices_from_line($rawLine);
            foreach ($prices as $price) {
                $totalCandidates[] = ['val' => $price, 'priority' => 1];
            }
        }
        if (!empty($totalCandidates)) {
            usort($totalCandidates, static function ($a, $b) {
                return $b['val'] <=> $a['val'];
            });
            $total = $totalCandidates[0]['val'];
        }
    }

    $itemsSum = 0.0;
    foreach ($items as $item) {
        $itemsSum += $item['precio'];
    }
    $itemsSum = round($itemsSum, 2);

    if ($total === null && $itemsSum > 0) {
        $total = $itemsSum;
    } elseif ($total !== null && $itemsSum > 0 && abs($total - $itemsSum) <= max(2.0, $total * 0.05)) {
        // Prefer explicit total when close to line sum
    } elseif ($total === null && $subtotal !== null) {
        $total = round($subtotal + ($tax ?? 0), 2);
    }

    $vendorInfo = ticket_detect_vendor($text);
    $payment = ticket_detect_payment_method($text);

    $descripcion = $vendorInfo['vendor'] !== 'Ticket Escaneado'
        ? 'Consumo en ' . $vendorInfo['vendor']
        : 'Gasto con comprobante';

    if (count($items) > 0) {
        $names = array_slice(array_map(static function ($item) {
            return $item['nombre'];
        }, $items), 0, 3);
        $descripcion = count($items) . ' producto(s): ' . implode(', ', $names);
        if (count($items) > 3) {
            $descripcion .= '...';
        }
    }

    return [
        'vendor' => $vendorInfo['vendor'],
        'categoria' => $vendorInfo['cat'],
        'monto' => $total,
        'subtotal' => $subtotal,
        'iva' => $tax,
        'metodo_pago' => $payment,
        'descripcion' => $descripcion,
        'items' => $items,
        'items_sum' => $itemsSum,
    ];
}

function ticket_merge_scan_results(array $filenameData, array $ocrData): array
{
    $merged = $ocrData;

    if (($merged['vendor'] === 'Ticket Escaneado' || $merged['vendor'] === '') && $filenameData['vendor'] !== 'Ticket Escaneado') {
        $merged['vendor'] = $filenameData['vendor'];
        $merged['categoria'] = $filenameData['categoria'];
    }

    if (($merged['monto'] === null || $merged['monto'] <= 0) && !empty($filenameData['monto'])) {
        $merged['monto'] = $filenameData['monto'];
    }

    if ($merged['vendor'] !== 'Ticket Escaneado' && strpos($merged['descripcion'], 'producto') === false) {
        $merged['descripcion'] = 'Consumo en ' . $merged['vendor'];
    }

    return $merged;
}
