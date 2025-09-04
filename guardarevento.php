<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Conexión a la base de datos
$conexion = new mysqli(
    "srv1788.hstgr.io",
    "u119832370_rifas",
    "+s>e6|Sd1H",
    "u119832370_rifas"
);

if ($conexion->connect_error) {
    echo json_encode(["status" => "error", "msg" => "Error de conexión: " . $conexion->connect_error]);
    exit;
}

$conexion->set_charset("utf8mb4");

$titulo = $_POST['titulo'] ?? '';
$stock  = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
$precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;

if ($titulo && $stock > 0 && $precio > 0) {
    $stmt = $conexion->prepare("INSERT INTO events (titulo, stock, precio) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $titulo, $stock, $precio);

    if ($stmt->execute()) {
        echo json_encode(["status" => "ok"]);
    } else {
        echo json_encode(["status" => "error", "msg" => $conexion->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "msg" => "Datos incompletos"]);
}

$conexion->close();
