<?php
// getimage.php?id=XX
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('ID no válido');
}

$conn = new mysqli("srv1788.hstgr.io", "u119832370_rifas", "+s>e6|Sd1H", "u119832370_rifas");
if ($conn->connect_error) {
    http_response_code(500);
    exit('Error en la conexión a la base de datos');
}

// Selecciona campo imagen de la tabla sales
$stmt = $conn->prepare("SELECT imagen FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($imagenBinaria);
if ($stmt->fetch() && $imagenBinaria !== null) {
    // Enviar cabecera content-type - ajusta "image/jpeg" si sabes otro tipo
    header("Content-Type: image/jpeg");
    header("Content-Length: " . strlen($imagenBinaria));
    echo $imagenBinaria;
} else {
    http_response_code(404);
    echo "Imagen no encontrada";
}

$stmt->close();
$conn->close();
