<?php
session_start();
$_SESSION['id_usuario'] = 1;
$_SESSION['nombre'] = 'diego';
$_SESSION['correo'] = 'diego.izahel@gmail.com';
echo json_encode(['success' => true, 'message' => 'Sesión de prueba iniciada']);
?>
