<?php
$conexion = new mysqli("srv1788.hstgr.io", "u119832370_rifas", "+s>e6|Sd1H", "u119832370_rifas");
if ($conexion->connect_error) {
    die(json_encode(["status" => "error", "msg" => "Error de conexión"]));
}

$id = intval($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $conexion->prepare("DELETE FROM evnts WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "ok"]);
    } else {
        echo json_encode(["status" => "error", "msg" => $conexion->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "msg" => "ID inválido"]);
}
$conexion->close();
?>
