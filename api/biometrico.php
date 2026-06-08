<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    $action = '';
    $data = [];

    if (stripos($contentType, 'application/json') !== false) {
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);
        if (is_array($data)) {
            $action = isset($data['action']) ? trim($data['action']) : '';
        }
    } else {
        $action = isset($_POST['action']) ? trim($_POST['action']) : '';
        $data = $_POST;
    }

    if ($action === 'register') {
        if (!isset($_SESSION['id_usuario'])) {
            $response['message'] = 'Debes iniciar sesión para configurar la biometría.';
            echo json_encode($response);
            exit;
        }

        $userId = $_SESSION['id_usuario'];
        $enable = isset($data['enable']) ? (bool)$data['enable'] : false;

        if ($enable) {
            $token = bin2hex(random_bytes(32));

            $sql = "UPDATE usuarios SET biometrico = 1, token_biometrico = ? WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $token, $userId);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['token'] = $token;
                    $response['message'] = 'Biometría activada correctamente.';
                } else {
                    $response['message'] = 'Error al actualizar la base de datos.';
                }
                $stmt->close();
            } else {
                $response['message'] = 'Error al preparar la consulta.';
            }
        } else {
            $sql = "UPDATE usuarios SET biometrico = 0, token_biometrico = NULL WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Biometría desactivada correctamente.';
                } else {
                    $response['message'] = 'Error al actualizar la base de datos.';
                }
                $stmt->close();
            } else {
                $response['message'] = 'Error al preparar la consulta.';
            }
        }
    } elseif ($action === 'login') {
        $correo = isset($data['correo']) ? trim($data['correo']) : '';
        $token = isset($data['token']) ? trim($data['token']) : '';

        if (empty($correo) || empty($token)) {
            $response['message'] = 'Correo y token biométrico requeridos.';
            echo json_encode($response);
            exit;
        }

        $sql = "SELECT * FROM usuarios WHERE correo = ? AND biometrico = 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();
                
                if (hash_equals($usuario['token_biometrico'], $token)) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['foto'] = $usuario['foto_perfil'];

                    $response['success'] = true;
                    $response['message'] = 'Inicio de sesión biométrico exitoso.';
                } else {
                    $response['message'] = 'Verificación biométrica fallida.';
                }
            } else {
                $response['message'] = 'La biometría no está habilitada para esta cuenta o el correo es incorrecto.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Error de conexión de base de datos.';
        }
    } else {
        $response['message'] = 'Acción inválida.';
    }
} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>
