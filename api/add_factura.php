<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado']);
}

$userId = $_SESSION['id_usuario'];
$data = json_decode(file_get_contents('php://input'), true);

$folio = isset($data['folio']) ? trim($data['folio']) : '';
$emisor = isset($data['emisor']) ? trim($data['emisor']) : '';
$receptor = isset($data['receptor']) ? trim($data['receptor']) : '';
$monto = isset($data['monto']) ? floatval($data['monto']) : 0.00;
$iva = isset($data['iva']) ? floatval($data['iva']) : 0.00;
$fecha_emision = isset($data['fecha_emision']) ? trim($data['fecha_emision']) : null;
$id_gasto = isset($data['id_gasto']) && $data['id_gasto'] !== '' ? intval($data['id_gasto']) : null;

if (!$folio || !$emisor || !$receptor || $monto <= 0) {
    api_json(['success' => false, 'message' => 'Campos obligatorios inválidos o incompletos']);
}

$stmt = $conn->prepare("INSERT INTO facturas (id_usuario, id_gasto, folio, emisor, receptor, monto, iva, fecha_emision) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iisssdds", $userId, $id_gasto, $folio, $emisor, $receptor, $monto, $iva, $fecha_emision);
    if ($stmt->execute()) {
        // Si hay un gasto vinculado, actualizar su campo xml_invoice para indicar que tiene factura
        if ($id_gasto) {
            $update = $conn->prepare("UPDATE gastos SET xml_invoice = ? WHERE id_gasto = ? AND id_usuario = ?");
            if ($update) {
                $status_msg = "Factura vinculada folio: $folio";
                $update->bind_param("sii", $status_msg, $id_gasto, $userId);
                $update->execute();
                $update->close();
            }
        }
        api_json(['success' => true, 'message' => 'Factura registrada con éxito']);
    } else {
        api_json(['success' => false, 'message' => 'Error al guardar factura: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    api_json(['success' => false, 'message' => 'Error de base de datos']);
}
?>
