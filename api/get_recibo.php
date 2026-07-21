<?php
require_once __DIR__ . '/api_common.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    api_json(['success' => false, 'message' => 'No autorizado.']);
}

require_once __DIR__ . '/conexion.php';

$userId = $_SESSION['id_usuario'];
$gastoId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($gastoId <= 0) {
    http_response_code(400);
    api_json(['success' => false, 'message' => 'ID de gasto no válido.']);
}

$stmt = $conn->prepare("SELECT foto_recibo FROM gastos WHERE id_gasto = ? AND id_usuario = ?");
if ($stmt) {
    $stmt->bind_param("ii", $gastoId, $userId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($foto);
        $stmt->fetch();
        
        if (!empty($foto)) {
            $mime = 'image/jpeg';
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detected = $finfo->buffer($foto);
                if ($detected) {
                    $mime = $detected;
                }
            } else {
                // Fallback basado en bytes mágicos si finfo no está disponible
                if (substr($foto, 0, 4) === "\x89PNG") {
                    $mime = 'image/png';
                } elseif (substr($foto, 0, 3) === "GIF") {
                    $mime = 'image/gif';
                } elseif (substr($foto, 0, 2) === "\xff\xd8") {
                    $mime = 'image/jpeg';
                } elseif (substr($foto, 8, 4) === "WEBP") {
                    $mime = 'image/webp';
                }
            }
            
            header("Content-Type: " . $mime);
            echo $foto;
        } else {
            http_response_code(404);
            api_json(['success' => false, 'message' => 'No hay imagen asociada a este gasto.']);
        }
    } else {
        http_response_code(404);
        api_json(['success' => false, 'message' => 'Gasto no encontrado.']);
    }
    $stmt->close();
} else {
    http_response_code(500);
    api_json(['success' => false, 'message' => 'Error de base de datos.']);
}
?>
