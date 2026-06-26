<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    $correo = '';
    $password = '';
    
    if (stripos($contentType, 'application/json') !== false) {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $correo = isset($decoded['correo']) ? trim($decoded['correo']) : '';
            $password = isset($decoded['password']) ? trim($decoded['password']) : '';
        }
    } else {
        $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    }

    if (empty($correo) || empty($password)) {
        $response['message'] = 'Todos los campos son obligatorios.';
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión directamente — la biometría es opcional,
                // no bloquea el acceso con contraseña.
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['nombre'];
                $_SESSION['foto']       = $usuario['foto_perfil'];

                $response['success']            = true;
                $response['biometrics_required'] = false;
                $response['message']            = 'Inicio de sesion exitoso.';

            } else {
                $response['message'] = 'Contraseña incorrecta.';
            }
        } else {
            $response['message'] = 'El correo no está registrado.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Error al preparar la consulta en la base de datos.';
    }
} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>