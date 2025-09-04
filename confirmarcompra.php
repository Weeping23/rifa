<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("srv1788.hstgr.io", "u119832370_rifas", "+s>e6|Sd1H", "u119832370_rifas");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'msg' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
// DEBUG: Ver qué datos llegan (se puede ver en logs de PHP o respuesta HTTP)
error_log("Datos recibidos: " . print_r($data, true));

$usuario = $conn->real_escape_string($data['usuario'] ?? '');
$usuarioId = intval($data['usuarioId'] ?? 0);
$nombre = $conn->real_escape_string($data['nombre'] ?? '');
$apellido = $conn->real_escape_string($data['apellido'] ?? '');
$imagen = $data['imagen'] ?? '';
$detalle = $data['detalle'] ?? [];

if (!$usuario || $usuarioId <= 0 || empty($detalle) || !$imagen) {
   echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos']);
exit;
}
// Procesar imagen (base64 -> binario)
$imagenBinaria = null;
if (preg_match('/^data:image\/(\w+);base64,/', $imagen, $type)) {
    $imagen = substr($imagen, strpos($imagen, ',') + 1);
    $imagenBinaria = base64_decode($imagen);
    if ($imagenBinaria === false) {
        echo json_encode(['status' => 'error', 'msg' => 'Imagen inválida']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Formato de imagen inválido']);
    exit;
}

$conn->autocommit(false);

$stmt = $conn->prepare("
    INSERT INTO sales (
        nombre, apellido, usuario, cantidad, precio, estado, fecha, boleto, imagen,
        id_evento, id_cliente, totalusd, totalbsf
    ) VALUES (?, ?, ?, ?, ?, 'Pendiente', CURDATE(), ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'msg' => 'Error preparar consulta: ' . $conn->error]);
    exit;
}

try {
    foreach ($detalle as $item) {
        //$cantidad = count($item['nums'] ?? []);
        //$cantidad = $item['cantidad'] ?? count($item['nums'] ?? []);
        $cantidad = intval($item['cantidad'] ?? count($item['nums'] ?? []));
        $boleto = implode(',', $item['nums'] ?? []);
        $precio = floatval($item['precio'] ?? 0);
        $totalusd = $cantidad * $precio;
        $totalbsf = $totalusd * 1; // Ajusta tasa si es necesario
        $boleto = implode(',', $item['nums'] ?? []);
        $id_evento = intval($item['eventoId'] ?? 0);
        
        
        $empty_blob = null;

        $stmt->bind_param(
            "sssidsbiidd",
            $nombre,
            $apellido,
            $usuario,
            $cantidad,
            $precio,
            $boleto,
            $empty_blob,
            $id_evento,
            $usuarioId,
            $totalusd,
            $totalbsf
        );
        
        $stmt->send_long_data(6, $imagenBinaria);
        
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutar: " . $stmt->error);
        }
    }

    // Actualizar estado a 'Confirmado' en carrito para productos pendientes del usuario
    $usuarioEscaped = $conn->real_escape_string($usuario);
    $sqlUpdate = "UPDATE cart SET status = 'Confirmado' WHERE usuario_id = $usuarioId AND status = 'Pendiente'";
    if (!$conn->query($sqlUpdate)) {
        throw new Exception("Error actualizando carrito: " . $conn->error);
    }

    $conn->commit();
    $stmt->close();
    $conn->close();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    $conn->rollback();
    $stmt->close();
    $conn->close();
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}
