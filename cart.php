<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$conn = new mysqli("srv1788.hstgr.io", "u119832370_rifas", "+s>e6|Sd1H", "u119832370_rifas");
if ($conn->connect_error) {
    echo json_encode(["success"=>false, "msg"=>"Error conexión DB"]);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $input = json_decode(file_get_contents("php://input"), true);

    $usuarioId = (int)($input['usuarioId'] ?? 0);
    $eventoId  = (int)($input['eventoId'] ?? 0);
    $titulo    = $conn->real_escape_string($input['titulo'] ?? '');
    $precio    = (float)($input['precio'] ?? 0);
    $cantidad  = (int)($input['cantidad'] ?? 0);
    $numeros   = implode(",", $input['numeros'] ?? []);
    $status    = "Pendiente"; // Estado siempre inicia en Pendiente

    if ($usuarioId<=0 || $eventoId<=0 || $cantidad<=0) {
        echo json_encode(["success"=>false, "msg"=>"Datos inválidos"]);
        exit;
    }

    $sql = "INSERT INTO cart (usuario_id, evento_id, titulo, precio, cantidad, numeros, status)
            VALUES ($usuarioId, $eventoId, '$titulo', $precio, $cantidad, '$numeros', '$status')";

    if ($conn->query($sql)) {
        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false, "msg"=>$conn->error]);
    }

} elseif ($action === 'list') {
    $usuarioId = (int)($_GET['usuarioId'] ?? 0);
    $res = $conn->query("SELECT * FROM cart WHERE usuario_id=$usuarioId AND status='Pendiente'");
    $items = [];
    while($row = $res->fetch_assoc()){
        $row['numeros'] = explode(",", $row['numeros']);
        $items[] = $row;
    }
    echo json_encode(["success"=>true, "records"=>$items]);
} elseif ($action === 'clear') {
    $usuarioId = (int)($_GET['usuarioId'] ?? 0);
    $conn->query("DELETE FROM cart WHERE usuario_id=$usuarioId");
    echo json_encode(["success"=>true]);
} elseif ($action === 'delete') {
    $usuarioId = (int)($_GET['usuarioId'] ?? 0);
    $carroId   = (int)($_GET['carroId'] ?? 0);
    if ($usuarioId <= 0 || $carroId <= 0) {
        echo json_encode(["success" => false, "msg" => "Parámetros inválidos"]);
        exit;
    }
    // Eliminar solo si el producto pertenece al usuario para evitar abusos
    $sql = "DELETE FROM cart WHERE id = $carroId AND usuario_id = $usuarioId";
    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "msg" => $conn->error]);
    }
}


$conn->close();
