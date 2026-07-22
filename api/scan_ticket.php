<?php
require_once __DIR__ . '/api_common.php';
require_once __DIR__ . '/ticket_parser.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado.']);
}

$input = json_decode(file_get_contents('php://input'), true);
$filename = isset($input['filename']) ? trim((string) $input['filename']) : '';
$ocrText = isset($input['ocr_text']) ? trim((string) $input['ocr_text']) : '';

$filenameData = ticket_detect_from_filename($filename);

if ($ocrText !== '') {
    $ocrData = ticket_parse_ocr_text($ocrText);
    $result = ticket_merge_scan_results($filenameData, $ocrData);
} else {
    $result = [
        'vendor' => $filenameData['vendor'],
        'categoria' => $filenameData['categoria'],
        'monto' => $filenameData['monto'],
        'subtotal' => null,
        'iva' => null,
        'metodo_pago' => null,
        'descripcion' => 'Consumo en ' . $filenameData['vendor'],
        'items' => [],
        'items_sum' => 0,
    ];
}

api_json([
    'success' => true,
    'vendor' => $result['vendor'],
    'monto' => $result['monto'],
    'subtotal' => $result['subtotal'],
    'iva' => $result['iva'],
    'categoria' => $result['categoria'],
    'metodo_pago' => $result['metodo_pago'],
    'descripcion' => $result['descripcion'],
    'items' => $result['items'],
    'items_sum' => $result['items_sum'],
]);
