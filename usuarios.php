<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Datos de conexión
$host = 'srv1788.hstgr.io';
$user = 'u119832370_rifas';
$pass = '+s>e6|Sd1H';
$dbname = 'u119832370_rifas';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "msg" => "Error de conexión: " . $conn->connect_error
    ]);
    exit;
}

// Consulta para traer usuarios con los campos correctos
$sql = "SELECT usuario, nombre, apellido, cedula, correo, admin FROM users ORDER BY usuario ASC";
$result = $conn->query($sql);

$users = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            "user"    => $row["usuario"],
            "nombres" => $row["nombre"],
            "apellidos"=> $row["apellido"],
            "telefono"=> $row["cedula"],  // Aquí usas "cedula" porque no tienes campo teléfono
            "correo"  => $row["correo"],
            "isAdmin" => (bool)$row["admin"]
        ];
    }
    echo json_encode(["success" => true, "users" => $users]);
} else {
    echo json_encode([
        "success" => false,
        "msg" => "Error en consulta: " . $conn->error
    ]);
}

$conn->close();
?>
