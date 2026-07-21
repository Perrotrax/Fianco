<?php
require_once __DIR__ . '/api_common.php';
session_start();
$_SESSION['id_usuario'] = 1;
$_SESSION['nombre'] = 'diego';
$_SESSION['correo'] = 'diego.izahel@gmail.com';
api_json(['success' => true, 'message' => 'Sesión de prueba iniciada']);
?>
