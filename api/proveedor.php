<?php
require_once __DIR__ . '/api_common.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado']);
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

    if (!$nombre) { api_json(['success' => false, 'message' => 'Nombre requerido']); }

    $stmt = $conn->prepare("INSERT INTO proveedores (id_usuario, nombre, rfc, categoria, contacto, notas) VALUES (?,?,?,?,?,?)");
    if (!$stmt) { http_response_code(500); api_json(['success'=>false,'message'=>'Error en la base de datos (prepare)', 'detail'=>$conn->error]); }
    $stmt->bind_param("isssss", $userId, $nombre, $rfc, $categoria, $contacto, $notas);
    $ok = $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    api_json(['success' => $ok, 'id' => $id, 'message' => $ok ? 'Proveedor guardado' : 'Error']);

} elseif ($action === 'delete') {
    $id = isset($data['id']) ? intval($data['id']) : 0;
    if (!$id) { api_json(['success' => false, 'message' => 'ID requerido']); }
    $stmt = $conn->prepare("DELETE FROM proveedores WHERE id_proveedor=? AND id_usuario=?");
    if (!$stmt) { http_response_code(500); api_json(['success'=>false,'message'=>'Error en la base de datos (prepare)', 'detail'=>$conn->error]); }
    $stmt->bind_param("ii", $id, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    api_json(['success' => $ok, 'message' => $ok ? 'Proveedor eliminado' : 'Error']);

} elseif ($action === 'list') {
    $res = $conn->query("SELECT * FROM proveedores WHERE id_usuario = $userId ORDER BY nombre ASC");
    if ($res === false) { http_response_code(500); api_json(['success'=>false,'message'=>'Error en la base de datos (query)', 'detail'=>$conn->error]); }
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
    api_json(['success' => true, 'proveedores' => $proveedores]);

} else {
    api_json(['success' => false, 'message' => 'Acción no válida']);
}
?>
