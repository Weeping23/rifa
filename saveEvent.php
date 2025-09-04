<?php
$conexion = new mysqli("srv1788.hstgr.io", "u119832370_rifas", "+s>e6|Sd1H", "u119832370_rifas");
$titulo = $_POST['titulo'] ?? '';
$max = $_POST['max'] ?? 0;
$precio = $_POST['precio'] ?? 0;

if ($titulo && $max && $precio) {
    $sql = "INSERT INTO events (titulo, max, precio, is_destacada) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sid", $titulo, $max, $precio, 0);
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
?>
