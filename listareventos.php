<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Devolver JSON
header('Content-Type: application/json; charset=utf-8');

// Conexión a la base de datos
$conexion = new mysqli(
    "srv1788.hstgr.io",
    "u119832370_rifas",   // sin barra invertida
    "+s>e6|Sd1H",
    "u119832370_rifas"    // sin barra invertida
);

if ($conexion->connect_error) {
    echo json_encode(["status" => "error", "msg" => "Error de conexión: " . $conexion->connect_error]);
    exit;
}

$conexion->set_charset("utf8mb4");

$resultado = $conexion->query("SELECT id, titulo, stock, precio FROM events ORDER BY id DESC");

$eventos = [];
while ($fila = $resultado->fetch_assoc()) {
    $eventos[] = $fila;
}

echo json_encode($eventos, JSON_UNESCAPED_UNICODE);

$conexion->close();
