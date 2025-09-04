<?php
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("srv1788.hstgr.io", "u119832370_rifas", "+s>e6|Sd1H", "u119832370_rifas");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'msg' => "Error de conexión: " . $conn->connect_error]);
    exit;
}

// Recoger datos enviados por POST (asegurar escape para inyección)
$nombre   = $conn->real_escape_string($_POST['nombre'] ?? '');
$apellido = $conn->real_escape_string($_POST['apellido'] ?? '');
$usuario  = $conn->real_escape_string($_POST['usuario'] ?? '');
$correo   = $conn->real_escape_string($_POST['correo'] ?? '');
$pass     = $conn->real_escape_string($_POST['password'] ?? '');
$cedula   = $conn->real_escape_string($_POST['cedula'] ?? '');

// Imagen opcional (BLOB)
$imagenBin = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $imagenBin = file_get_contents($_FILES['imagen']['tmp_name']);
}

// Validar campos obligatorios
if (!$nombre || !$apellido || !$usuario || !$correo || !$pass || !$cedula) {
    echo json_encode(['success' => false, 'msg' => 'Faltan campos obligatorios']);
    exit;
}

// Comprobar si usuario o correo ya existen
$check = $conn->query("SELECT id FROM users WHERE usuario='$usuario' OR correo='$correo'");
if ($check && $check->num_rows > 0) {
    echo json_encode(['success' => false, 'msg' => 'Usuario o correo ya registrados']);
    exit;
}

// Admin = false por defecto
$admin = 0;

// Preparar consulta
$stmt = $conn->prepare("INSERT INTO users 
    (nombre, apellido, usuario, correo, `contraseña`, imagen, admin, cedula) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

// Placeholder para el blob (null)
$null_blob = null;

$stmt->bind_param("sssssbis", $nombre, $apellido, $usuario, $correo, $pass, $null_blob, $admin, $cedula);

// Enviar el blob real si existe
if ($imagenBin !== null) {
    $stmt->send_long_data(5, $imagenBin); // índice 5, sexto parámetro (0-based)
}

// Ejecutar y responder
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'msg' => 'Registro exitoso']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Error en el registro: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
