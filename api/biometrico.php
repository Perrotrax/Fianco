<?php
require_once __DIR__ . '/api_common.php';
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
            $response['message'] = 'Debes iniciar sesion para configurar la biometria.';
            api_json($response);
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
                    $response['message'] = 'Biometria activada correctamente.';
                } else {
                    $response['message'] = 'Error al actualizar la base de datos.';
                }
                $stmt->close();
            } else {
                $response['message'] = 'Error al preparar la consulta.';
            }
        } else {
            $conn->begin_transaction();
            $success = true;

            $sql = "UPDATE usuarios SET biometrico = 0, token_biometrico = NULL, webauthn_enabled = 0 WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                if (!$stmt->execute()) {
                    $success = false;
                }
                $stmt->close();
            } else {
                $success = false;
            }

            $sql = "DELETE FROM webauthn_credentials WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                if (!$stmt->execute()) {
                    $success = false;
                }
                $stmt->close();
            } else {
                $success = false;
            }

            if ($success) {
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Biometria desactivada y credenciales WebAuthn eliminadas.';
            } else {
                $conn->rollback();
                $response['message'] = 'Error al desactivar la biometría.';
            }
        }

    } elseif ($action === 'login') {
        $correo = isset($data['correo']) ? trim($data['correo']) : '';
        $token  = isset($data['token'])  ? trim($data['token'])  : '';

        if (empty($correo) || empty($token)) {
            $response['message'] = 'Correo y token biometrico requeridos.';
            api_json($response);
        }

        $sql = "SELECT * FROM usuarios WHERE correo = ? AND biometrico = 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();
                if (!empty($usuario['token_biometrico']) && hash_equals($usuario['token_biometrico'], $token)) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre']     = $usuario['nombre'];
                    $_SESSION['foto']       = $usuario['foto_perfil'];

                    unset($_SESSION['temp_id_usuario'], $_SESSION['temp_nombre'],
                          $_SESSION['temp_foto'],       $_SESSION['temp_correo']);

                    $response['success'] = true;
                    $response['message'] = 'Inicio de sesion biometrico exitoso.';
                } else {
                    $response['message'] = 'Verificacion biometrica fallida.';
                }
            } else {
                $response['message'] = 'La biometria no esta habilitada para esta cuenta.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Error de conexion de base de datos.';
        }

    } elseif ($action === 'login_temp_session') {
        // ---------------------------------------------------------------
        // Fallback seguro: completa el login usando la sesion temporal
        // establecida despues de verificar la contrasena en login.php.
        // Permite acceder cuando WebAuthn falla (localhost/HTTP) o cuando
        // no hay token biometrico guardado en localStorage del dispositivo.
        // ---------------------------------------------------------------
        $correo = isset($data['correo']) ? trim($data['correo']) : '';

        if (
            isset($_SESSION['temp_id_usuario']) &&
            isset($_SESSION['temp_correo']) &&
            strtolower($_SESSION['temp_correo']) === strtolower($correo)
        ) {
            $_SESSION['id_usuario'] = $_SESSION['temp_id_usuario'];
            $_SESSION['nombre']     = $_SESSION['temp_nombre'] ?? '';
            $_SESSION['foto']       = $_SESSION['temp_foto']   ?? null;

            unset($_SESSION['temp_id_usuario'], $_SESSION['temp_nombre'],
                  $_SESSION['temp_foto'],       $_SESSION['temp_correo']);

            $response['success'] = true;
            $response['message'] = 'Sesion iniciada correctamente.';
        } else {
            $response['message'] = 'Sesion temporal expirada. Por favor vuelve a ingresar tu contrasena.';
        }

    } else {
        $response['message'] = 'Accion invalida.';
    }
} else {
    $response['message'] = 'Metodo no permitido.';
}

api_json($response);
?>
