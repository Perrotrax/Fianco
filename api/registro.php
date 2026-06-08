<?php
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($nombre) || empty($correo) || empty($password)) {
        $response['message'] = 'Todos los campos son obligatorios.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'El formato del correo electrónico no es válido.';
        echo json_encode($response);
        exit;
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
            echo json_encode($response);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();
    } else {
        $response['message'] = 'Error de base de datos.';
        echo json_encode($response);
        exit;
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    $foto = "default.png";

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $targetDir = "../uploads/perfiles/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($extension), $allowed)) {
            $nombreFoto = time() . "_" . uniqid() . "." . $extension;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . $nombreFoto)) {
                $foto = $nombreFoto;
            } else {
                $response['message'] = 'No se pudo subir la foto de perfil.';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = 'Formato de imagen no permitido.';
            echo json_encode($response);
            exit;
        }
    }

    $sql = "INSERT INTO usuarios (nombre, correo, password, foto_perfil) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $nombre, $correo, $password_hashed, $foto);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Usuario registrado correctamente.';
        } else {
            $response['message'] = 'Error al registrar al usuario en la base de datos.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Error al preparar la consulta.';
    }
} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>