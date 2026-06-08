<?php

$host = "localhost";
$user = "root";
$password = "";
$db = "gestor_gastos";

$conn = new mysqli($host,$user,$password,$db);

if($conn->connect_error){
    die("Error de conexión: ".$conn->connect_error);
}

?>