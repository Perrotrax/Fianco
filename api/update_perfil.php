<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado']);
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
        api_json(['success' => false, 'message' => 'Nombre y correo requeridos']);
    }
    // Check email uniqueness
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?");
    $stmt->bind_param("si", $correo, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        api_json(['success' => false, 'message' => 'El correo ya está en uso']);
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=? WHERE id_usuario=?");
    $stmt->bind_param("ssi", $nombre, $correo, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    api_json(['success' => $ok, 'message' => $ok ? 'Datos actualizados' : 'Error al actualizar']);

} elseif ($action === 'update_password') {
    $raw = json_decode(file_get_contents('php://input'), true);
    $actual    = isset($raw['actual']) ? $raw['actual'] : '';
    $nueva     = isset($raw['nueva']) ? $raw['nueva'] : '';
    $confirmar = isset($raw['confirmar']) ? $raw['confirmar'] : '';

    if (!$actual || !$nueva || !$confirmar) {
        api_json(['success' => false, 'message' => 'Todos los campos son requeridos']);
    }
    if ($nueva !== $confirmar) {
        api_json(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    }
    if (strlen($nueva) < 6) {
        api_json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    }

    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($actual, $row['password'])) {
        api_json(['success' => false, 'message' => 'Contraseña actual incorrecta']);
    }

    $hashed = password_hash($nueva, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET password=? WHERE id_usuario=?");
    $stmt->bind_param("si", $hashed, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    api_json(['success' => $ok, 'message' => $ok ? 'Contraseña actualizada' : 'Error al actualizar']);

} elseif ($action === 'update_foto') {
    // Handle file upload from multipart form
    $errorMessage = 'Error al recibir la imagen';
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        if (isset($_FILES['foto']['error'])) {
            switch ($_FILES['foto']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMessage = 'El archivo excede el tamaño máximo permitido.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMessage = 'La imagen se cargó parcialmente.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMessage = 'No se seleccionó ninguna imagen.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMessage = 'Falta el directorio temporal en el servidor.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMessage = 'Error escribiendo la imagen en el servidor.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMessage = 'La subida de la imagen fue detenida por una extensión.';
                    break;
            }
        }
        api_json(['success' => false, 'message' => $errorMessage]);
    }

    if (!is_uploaded_file($_FILES['foto']['tmp_name'])) {
        api_json(['success' => false, 'message' => 'El archivo no es un upload válido.']);
    }

    $maxSize = 3 * 1024 * 1024; // 3MB
    if ($_FILES['foto']['size'] > $maxSize) {
        api_json(['success' => false, 'message' => 'La imagen es muy grande (max 3MB)']);
    }

    $imageData = file_get_contents($_FILES['foto']['tmp_name']);
    if ($imageData === false) {
        api_json(['success' => false, 'message' => 'No se pudo leer la imagen.']);
    }

    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['foto']['tmp_name']);
    }
    if (!$mime && function_exists('mime_content_type')) {
        $mime = mime_content_type($_FILES['foto']['tmp_name']);
    }
    if (!$mime) {
        $imgInfo = getimagesize($_FILES['foto']['tmp_name']);
        $mime = $imgInfo['mime'] ?? '';
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowed)) {
        $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensionMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
        if (isset($extensionMap[$extension])) {
            $mime = $extensionMap[$extension];
        }
    }
    if (!in_array($mime, $allowed)) {
        api_json(['success' => false, 'message' => 'Formato no permitido']);
    }

    $maxPacket = 0;
    $packetResult = $conn->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    if ($packetResult) {
        $packetRow = $packetResult->fetch_assoc();
        $maxPacket = isset($packetRow['Value']) ? intval($packetRow['Value']) : 0;
        $packetResult->free();
    }

    if ($maxPacket > 0 && strlen($imageData) > $maxPacket) {
        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            $srcImage = imagecreatefromstring($imageData);
            if ($srcImage !== false) {
                $width = imagesx($srcImage);
                $height = imagesy($srcImage);
                $maxDim = 1000;
                $ratio = min(1, $maxDim / max($width, $height));
                $newWidth = max(1, intval($width * $ratio));
                $newHeight = max(1, intval($height * $ratio));
                $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                ob_start();
                if ($mime === 'image/png') {
                    imagepng($dstImage, null, 6);
                } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
                    imagewebp($dstImage, null, 80);
                } elseif ($mime === 'image/gif') {
                    imagegif($dstImage);
                } else {
                    imagejpeg($dstImage, null, 80);
                    $mime = 'image/jpeg';
                }
                $optimizedData = ob_get_clean();
                imagedestroy($srcImage);
                imagedestroy($dstImage);
                if ($optimizedData !== false && strlen($optimizedData) > 0 && strlen($optimizedData) < strlen($imageData)) {
                    $imageData = $optimizedData;
                }
            }
        }
    }

    if ($maxPacket > 0 && strlen($imageData) > $maxPacket) {
        api_json(['success' => false, 'message' => 'La imagen sigue siendo demasiado grande para la configuración del servidor. Intenta con una foto más pequeña o ajusta max_allowed_packet en MySQL.', 'detail' => 'max_allowed_packet=' . $maxPacket]);
    }

    $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil=? WHERE id_usuario=?");
    if (!$stmt) {
        api_json(['success' => false, 'message' => 'Error en la base de datos al preparar la foto.', 'detail' => $conn->error]);
    }
    $stmt->bind_param("si", $imageData, $userId);
    $ok = $stmt->execute();
    if (!$ok) {
        $response = ['success' => false, 'message' => 'Error al subir foto', 'detail' => $stmt->error];
    } else {
        $_SESSION['foto'] = $imageData;
        $response = [
            'success' => true,
            'message' => 'Foto actualizada',
            'foto_base64' => 'data:' . $mime . ';base64,' . base64_encode($imageData)
        ];
    }
    $stmt->close();

    api_json($response);

} else {
    api_json(['success' => false, 'message' => 'Acción no reconocida: ' . $action]);
}
?>
