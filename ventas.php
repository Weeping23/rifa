<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$conn = new mysqli(
    "srv1788.hstgr.io",
    "u119832370_rifas",
    "+s>e6|Sd1H",
    "u119832370_rifas"
);

if ($conn->connect_error) {
    echo json_encode([
        'Result' => 'ERROR',
        'Message' => 'Error de conexión: ' . $conn->connect_error
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $sql = "SELECT id, nombre, apellido, usuario, cantidad, precio, estado, fecha, boleto, imagen, id_evento
            FROM sales
            ORDER BY fecha DESC";

    $res = $conn->query($sql);
    if (!$res) {
        echo json_encode([
            'Result' => 'ERROR',
            'Message' => 'Error en consulta: ' . $conn->error
        ]);
        exit;
    }

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        // Limpiar y codificar imagen
        if (!empty($row['imagen'])) {
            // Evitar saltos de línea
            $row['imagen'] = base64_encode($row['imagen']);
        } else {
            $row['imagen'] = null;
        }

        // Generar detalle con boletos
        $nums = array_map('trim', explode(',', $row['boleto'] ?? ''));
        $row['detalle'] = [
            [
                'titulo' => 'Boletos',
                'nums'   => $nums
            ]
        ];

        unset($row['boleto']); // ya lo tenemos en detalle
        $rows[] = $row;
    }

    echo json_encode([
        'Result'  => 'OK',
        'Records' => $rows
    ]);
} else {
    echo json_encode([
        'Result' => 'ERROR',
        'Message' => 'Acción no válida'
    ]);
}

$conn->close();
