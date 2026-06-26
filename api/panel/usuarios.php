<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/../conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $users = [];
    $result = $conn->query(
        'SELECT id_usuario, nombre, correo, biometrico, fecha_registro
         FROM usuarios
         ORDER BY fecha_registro DESC'
    );

    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => (string) $row['id_usuario'],
            'name' => $row['nombre'],
            'email' => $row['correo'],
            'biometric' => (bool) $row['biometrico'],
            'joined' => date('j M Y', strtotime($row['fecha_registro'])),
        ];
    }

    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

$contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
$data = [];

if (stripos($contentType, 'application/json') !== false) {
    $decoded = json_decode(trim(file_get_contents('php://input')), true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
} else {
    $data = $_POST;
}

$action = isset($data['action']) ? trim($data['action']) : '';

if ($action === 'create') {
    $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
    $correo = isset($data['correo']) ? trim($data['correo']) : '';
    $password = isset($data['password']) ? trim($data['password']) : '';

    if ($nombre === '' || $correo === '' || $password === '') {
        $response['message'] = 'Nombre, correo y contraseña son obligatorios.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Correo electrónico no válido.';
        echo json_encode($response);
        exit;
    }

    $check = $conn->prepare('SELECT id_usuario FROM usuarios WHERE correo = ?');
    $check->bind_param('s', $correo);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $response['message'] = 'El correo ya está registrado.';
        $check->close();
        echo json_encode($response);
        exit;
    }
    $check->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $nombre, $correo, $hash);

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
        $response['message'] = 'Usuario creado correctamente.';
        $response['id'] = (string) $newUserId;
    } else {
        $response['message'] = 'No se pudo crear el usuario.';
    }
    $stmt->close();
} elseif ($action === 'update') {
    $id = isset($data['id']) ? (int) $data['id'] : 0;
    $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
    $correo = isset($data['correo']) ? trim($data['correo']) : '';

    if ($id <= 0 || $nombre === '' || $correo === '') {
        $response['message'] = 'Datos incompletos.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Correo electrónico no válido.';
        echo json_encode($response);
        exit;
    }

    $check = $conn->prepare('SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?');
    $check->bind_param('si', $correo, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $response['message'] = 'El correo ya está en uso.';
        $check->close();
        echo json_encode($response);
        exit;
    }
    $check->close();

    $stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, correo = ? WHERE id_usuario = ?');
    $stmt->bind_param('ssi', $nombre, $correo, $id);

    if ($stmt->execute()) {
        if ($id === (int) $_SESSION['id_usuario']) {
            $_SESSION['nombre'] = $nombre;
        }
        $response['success'] = true;
        $response['message'] = 'Usuario actualizado.';
    } else {
        $response['message'] = 'No se pudo actualizar el usuario.';
    }
    $stmt->close();
} elseif ($action === 'delete') {
    $id = isset($data['id']) ? (int) $data['id'] : 0;

    if ($id <= 0) {
        $response['message'] = 'Usuario no válido.';
        echo json_encode($response);
        exit;
    }

    if ($id === (int) $_SESSION['id_usuario']) {
        $response['message'] = 'No puedes eliminar tu propia cuenta desde el panel.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Usuario eliminado.';
    } else {
        $response['message'] = 'No se pudo eliminar el usuario.';
    }
    $stmt->close();
} elseif ($action === 'reset_biometric') {
    $id = isset($data['id']) ? (int) $data['id'] : 0;

    if ($id <= 0) {
        $response['message'] = 'Usuario no válido.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare('UPDATE usuarios SET biometrico = 0, token_biometrico = NULL WHERE id_usuario = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Biometría restablecida para el usuario.';
    } else {
        $response['message'] = 'No se pudo restablecer la biometría.';
    }
    $stmt->close();
} else {
    $response['message'] = 'Acción no válida.';
}

echo json_encode($response);
