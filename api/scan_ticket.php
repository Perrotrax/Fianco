<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado.']);
}

$input = json_decode(file_get_contents('php://input'), true);
$filename = isset($input['filename']) ? trim($input['filename']) : '';
$image_base64 = isset($input['image']) ? trim($input['image']) : '';

$detectedVendor = 'Ticket Escaneado';
$detectedMonto = null;
$detectedCategoria = 'Comida';

// 1. Análisis por Nombre de Archivo
if (!empty($filename)) {
    $fnLower = strtolower($filename);

    // Detección de Vendedor por nombre de archivo
    $vendors = [
        'zara' => ['ZARA', 'Otros'],
        'pull' => ['ZARA / Pull&Bear', 'Otros'],
        'bershka' => ['Bershka', 'Otros'],
        'stradivarius' => ['Stradivarius', 'Otros'],
        'mango' => ['Mango', 'Otros'],
        'hm' => ['H&M', 'Otros'],
        'liverpool' => ['Liverpool', 'Otros'],
        'sears' => ['Sears', 'Otros'],
        'suburbia' => ['Suburbia', 'Otros'],
        'coppel' => ['Coppel', 'Otros'],
        'oxxo' => ['OXXO', 'Comida'],
        '7eleven' => ['7-Eleven', 'Comida'],
        'seven' => ['7-Eleven', 'Comida'],
        'walmart' => ['Walmart', 'Comida'],
        'aurrera' => ['Bodega Aurrerá', 'Comida'],
        'soriana' => ['Soriana', 'Comida'],
        'chedraui' => ['Chedraui', 'Comida'],
        'costco' => ['Costco', 'Comida'],
        'sams' => ['Sam\'s Club', 'Comida'],
        'pemex' => ['Pemex', 'Transporte'],
        'shell' => ['Shell', 'Transporte'],
        'bp' => ['BP Gasolinera', 'Transporte'],
        'g500' => ['G500', 'Transporte'],
        'mobil' => ['Mobil', 'Transporte'],
        'gasolina' => ['Gasolinera', 'Transporte'],
        'gas' => ['Gasolinera', 'Transporte'],
        'uber' => ['Uber', 'Transporte'],
        'didi' => ['DiDi', 'Transporte'],
        'mcdonald' => ['McDonald\'s', 'Comida'],
        'burger' => ['Burger King', 'Comida'],
        'starbucks' => ['Starbucks', 'Comida'],
        'domino' => ['Domino\'s Pizza', 'Comida'],
        'subway' => ['Subway', 'Comida'],
        'kfc' => ['KFC', 'Comida'],
        'tacos' => ['Taquería / Comida', 'Comida'],
        'restaurante' => ['Restaurante', 'Comida'],
        'cafe' => ['Cafetería', 'Comida'],
        'farmacia' => ['Farmacia', 'Servicios'],
        'telmex' => ['Telmex', 'Servicios'],
        'cfe' => ['CFE', 'Servicios'],
        'hotel' => ['Hotel', 'Hogar']
    ];

    foreach ($vendors as $key => $data) {
        if (strpos($fnLower, $key) !== false) {
            $detectedVendor = $data[0];
            $detectedCategoria = $data[1];
            break;
        }
    }

    // Detección de Monto en Nombre de Archivo (ej. gasto_150.50.jpg, total_500.png)
    // Ignorar patrones numéricos estándar de cámara (IMG_2500, DSC_1234, PXL_5678, Screenshot)
    if (!preg_match('/^(?:img|dsc|pxl|win|screenshot|foto|capture|cam|recibo|ticket)[_\-\s]*\d+/i', $fnLower)) {
        if (preg_match('/(?:monto|total|price|gasto|_|\$)?(\d+(?:[\._]\d{2}))(?:\.jpg|\.png|\.jpeg|\.webp|\.pdf|$)/i', $fnLower, $matches)) {
            $val = floatval(str_replace('_', '.', $matches[1]));
            if ($val > 0 && $val < 100000 && $val != 2026 && $val != 2025 && $val != 2024) {
                $detectedMonto = round($val, 2);
            }
        }
    }
}

api_json([
    'success' => true,
    'vendor' => $detectedVendor,
    'monto' => $detectedMonto,
    'categoria' => $detectedCategoria,
    'descripcion' => "Consumo en $detectedVendor"
]);
?>
