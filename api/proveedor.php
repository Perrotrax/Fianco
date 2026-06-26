<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['id_usuario'];
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

if ($action === 'add') {
    $nombre   = isset($data['nombre']) ? trim($data['nombre']) : '';
    $rfc      = isset($data['rfc']) ? trim($data['rfc']) : null;
    $categoria = isset($data['categoria']) ? trim($data['categoria']) : null;
    $contacto = isset($data['contacto']) ? trim($data['contacto']) : null;
    $notas    = isset($data['notas']) ? trim($data['notas']) : null;

    if (!$nombre) { echo json_encode(['success' => false, 'message' => 'Nombre requerido']); exit; }

    $stmt = $conn->prepare("INSERT INTO proveedores (id_usuario, nombre, rfc, categoria, contacto, notas) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("isssss", $userId, $nombre, $rfc, $categoria, $contacto, $notas);
    $ok = $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    echo json_encode(['success' => $ok, 'id' => $id, 'message' => $ok ? 'Proveedor guardado' : 'Error']);

} elseif ($action === 'delete') {
    $id = isset($data['id']) ? intval($data['id']) : 0;
    if (!$id) { echo json_encode(['success' => false, 'message' => 'ID requerido']); exit; }
    $stmt = $conn->prepare("DELETE FROM proveedores WHERE id_proveedor=? AND id_usuario=?");
    $stmt->bind_param("ii", $id, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Proveedor eliminado' : 'Error']);

} elseif ($action === 'list') {
    $res = $conn->query("SELECT * FROM proveedores WHERE id_usuario = $userId ORDER BY nombre ASC");
    $proveedores = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $proveedores[] = [
                'id_proveedor' => intval($row['id_proveedor']),
                'nombre'       => $row['nombre'],
                'rfc'          => $row['rfc'],
                'categoria'    => $row['categoria'],
                'contacto'     => $row['contacto'],
                'notas'        => $row['notas'],
            ];
        }
    }
    echo json_encode(['success' => true, 'proveedores' => $proveedores]);

} else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
?>
