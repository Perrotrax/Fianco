<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['id_usuario'];
$response = ['success' => false, 'message' => ''];

// Determine action from POST fields
$action = '';
if (isset($_POST['action'])) $action = $_POST['action'];
elseif (isset($_GET['action'])) $action = $_GET['action'];
else {
    $raw = json_decode(file_get_contents('php://input'), true);
    $action = isset($raw['action']) ? $raw['action'] : '';
}

if ($action === 'update_datos') {
    // Update name and email
    $raw = json_decode(file_get_contents('php://input'), true);
    $nombre = isset($raw['nombre']) ? trim($raw['nombre']) : '';
    $correo = isset($raw['correo']) ? trim($raw['correo']) : '';

    if (!$nombre || !$correo) {
        echo json_encode(['success' => false, 'message' => 'Nombre y correo requeridos']);
        exit;
    }
    // Check email uniqueness
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?");
    $stmt->bind_param("si", $correo, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El correo ya está en uso']);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=? WHERE id_usuario=?");
    $stmt->bind_param("ssi", $nombre, $correo, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Datos actualizados' : 'Error al actualizar']);

} elseif ($action === 'update_password') {
    $raw = json_decode(file_get_contents('php://input'), true);
    $actual    = isset($raw['actual']) ? $raw['actual'] : '';
    $nueva     = isset($raw['nueva']) ? $raw['nueva'] : '';
    $confirmar = isset($raw['confirmar']) ? $raw['confirmar'] : '';

    if (!$actual || !$nueva || !$confirmar) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']); exit;
    }
    if ($nueva !== $confirmar) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']); exit;
    }
    if (strlen($nueva) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']); exit;
    }

    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($actual, $row['password'])) {
        echo json_encode(['success' => false, 'message' => 'Contraseña actual incorrecta']); exit;
    }

    $hashed = password_hash($nueva, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET password=? WHERE id_usuario=?");
    $stmt->bind_param("si", $hashed, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Contraseña actualizada' : 'Error al actualizar']);

} elseif ($action === 'update_foto') {
    // Handle file upload from multipart form
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al recibir la imagen']); exit;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['foto']['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Formato no permitido']); exit;
    }
    $maxSize = 3 * 1024 * 1024; // 3MB
    if ($_FILES['foto']['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'La imagen es muy grande (max 3MB)']); exit;
    }
    $imageData = file_get_contents($_FILES['foto']['tmp_name']);
    $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil=? WHERE id_usuario=?");
    $stmt->bind_param("si", $imageData, $userId);
    // Need to use send_long_data for blobs
    $stmt->close();
    // Use direct query for BLOB
    $escaped = $conn->real_escape_string($imageData);
    $ok = $conn->query("UPDATE usuarios SET foto_perfil='" . $escaped . "' WHERE id_usuario=" . $userId);
    echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Foto actualizada' : 'Error al subir foto']);

} else {
    echo json_encode(['success' => false, 'message' => 'Acción no reconocida: ' . $action]);
}
?>
