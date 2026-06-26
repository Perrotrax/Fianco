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
    $foto = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($extension), $allowed)) {
            $foto = file_get_contents($_FILES['foto']['tmp_name']);
            if ($foto === false) {
                $response['message'] = 'No se pudo leer la foto de perfil.';
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
        $null = NULL;
        $stmt->bind_param("sssb", $nombre, $correo, $password_hashed, $null);
        if ($foto !== null) {
            $stmt->send_long_data(3, $foto);
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