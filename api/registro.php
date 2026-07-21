<?php
require_once __DIR__ . '/api_common.php';
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

function optimizeImageForMySQL($data, $maxPacket) {
    if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
        return $data;
    }

    $src = @imagecreatefromstring($data);
    if ($src === false) {
        return $data;
    }

    $width = imagesx($src);
    $height = imagesy($src);
    $maxDim = 1000;
    if (max($width, $height) > $maxDim) {
        if ($width > $height) {
            $height = intval(($maxDim / $width) * $height);
            $width = $maxDim;
        } else {
            $width = intval(($maxDim / $height) * $width);
            $height = $maxDim;
        }
    }

    $dst = imagecreatetruecolor(max(1, $width), max(1, $height));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));

    $quality = 85;
    $compressed = false;
    while ($quality >= 40) {
        ob_start();
        imagejpeg($dst, null, $quality);
        $compressed = ob_get_clean();
        if ($compressed !== false && strlen($compressed) <= $maxPacket) {
            break;
        }
        $quality -= 5;
    }

    imagedestroy($src);
    imagedestroy($dst);

    if ($compressed !== false && strlen($compressed) < strlen($data)) {
        return $compressed;
    }
    return $data;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($nombre) || empty($correo) || empty($password)) {
        $response['message'] = 'Todos los campos son obligatorios.';
        api_json($response);
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'El formato del correo electrónico no es válido.';
        api_json($response);
    }

    // Check if email already exists
    $check_sql = "SELECT id_usuario FROM usuarios WHERE correo = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt) {
        $check_stmt->bind_param("s", $correo);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $response['message'] = 'El correo ya está registrado.';
            api_json($response);
            $check_stmt->close();
        }
        $check_stmt->close();
    } else {
        $response['message'] = 'Error de base de datos.';
        api_json($response);
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    $foto = null;
    $mime = '';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($extension), $allowed)) {
            $foto = file_get_contents($_FILES['foto']['tmp_name']);
            if ($foto === false) {
                $response['message'] = 'No se pudo leer la foto de perfil.';
                api_json($response);
            }
        } else {
            $response['message'] = 'Formato de imagen no permitido.';
            api_json($response);
        }
    }

    $maxPacket = 0;
    $packetResult = $conn->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    if ($packetResult) {
        $packetRow = $packetResult->fetch_assoc();
        $maxPacket = isset($packetRow['Value']) ? intval($packetRow['Value']) : 0;
        $packetResult->free();
    }

    if ($foto !== null && $maxPacket > 0 && strlen($foto) > $maxPacket) {
        $foto = optimizeImageForMySQL($foto, $maxPacket);
    }

    $sql = "INSERT INTO usuarios (nombre, correo, password, foto_perfil) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if ($foto !== null) {
            $null = null;
            $stmt->bind_param("sssb", $nombre, $correo, $password_hashed, $null);
            $stmt->send_long_data(3, $foto);
        } else {
            $stmt->bind_param("sssb", $nombre, $correo, $password_hashed, $null);
        }
        if ($stmt->execute()) {
            $newUserId = $conn->insert_id;
            
            // Inicializar categorías custom por defecto
            $default_cats = ['Comida', 'Transporte', 'Entretenimiento', 'Servicios', 'Hogar', 'Otros'];
            foreach ($default_cats as $cat) {
                $c_stmt = $conn->prepare("INSERT IGNORE INTO categorias_custom (id_usuario, nombre, limite_mensual) VALUES (?, ?, 0.00)");
                if ($c_stmt) {
                    $c_stmt->bind_param("is", $newUserId, $cat);
                    $c_stmt->execute();
                    $c_stmt->close();
                }
            }

            $response['success'] = true;
            $response['message'] = 'Usuario registrado correctamente.';
        } else {
            $response['message'] = 'Error al registrar al usuario en la base de datos.';
            $response['detail'] = $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Error al preparar la consulta.';
    }
} else {
    $response['message'] = 'Método no permitido.';
}

api_json($response);
?>