<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Datos de conexión
$host = 'srv1788.hstgr.io';
$user = 'u119832370_rifas';
$pass = '+s>e6|Sd1H';
$db   = 'u119832370_rifas';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['Result' => 'ERROR', 'Message' => $conn->connect_error]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {

    // ------------------------- LISTA DE EVENTOS
    case 'list':
        $sql = "SELECT id, titulo, stock, precio, imagen, descripcion
                FROM events
                ORDER BY titulo ASC";
        $res = $conn->query($sql);
        if (!$res) {
            echo json_encode(['Result' => 'ERROR', 'Message' => $conn->error]);
            break;
        }

        $records = [];
        while ($row = $res->fetch_assoc()) {
            $eventoId = (int)$row['id'];

            // Contar boletos vendidos
            $vendidos = 0;
            $sqlV = "SELECT boleto 
                     FROM sales 
                     WHERE id_evento = $eventoId
                       AND estado IN ('Pendiente','Pagado')";
            $resV = $conn->query($sqlV);
            if ($resV) {
                while ($venta = $resV->fetch_assoc()) {
                    $nums = array_filter(array_map('trim', explode(',', $venta['boleto'])));
                    $vendidos += count($nums);
                }
            }

            $records[] = [
                'id'       => $eventoId,
                'title'    => $row['titulo'],       // Nombre amigable
                'max'      => (int)$row['stock'],   // Cupos totales
                'price'    => (float)$row['precio'],// Precio unitario
                'vendidos' => $vendidos,
                //'imagen'   => $row['imagen'],// Imagen del evento
                'descripcion' => $row['descripcion']
            ];
        }

        echo json_encode(['Result' => 'OK', 'Records' => $records]);
        break;

    // ------------------------- DETALLE DE EVENTO
    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['Result' => 'ERROR', 'Message' => 'ID inválido']);
            break;
        }

        $sql = "SELECT id, titulo, stock, precio 
                FROM events 
                WHERE id = $id LIMIT 1";
        $res = $conn->query($sql);
        if (!$res || $res->num_rows === 0) {
            echo json_encode(['Result' => 'ERROR', 'Message' => 'No encontrado']);
            break;
        }

        $row = $res->fetch_assoc();

        // Buscar boletos ocupados
        $ocupados = [];
        $sql2 = "SELECT boleto 
                 FROM sales 
                 WHERE id_evento = $id
                   AND estado IN ('Pendiente','Pagado')";
        $res2 = $conn->query($sql2);
        if ($res2) {
            while ($venta = $res2->fetch_assoc()) {
                $nums = array_filter(array_map('trim', explode(',', $venta['boleto'])));
                $ocupados = array_merge($ocupados, array_map('intval', $nums));
            }
        }

        // Contar vendidos
        $vendidos = count($ocupados);

        echo json_encode([
            'Result' => 'OK',
            'Record' => [
                'id'       => (int)$row['id'],
                'title'    => $row['titulo'],
                'max'      => (int)$row['stock'],
                'price'    => (float)$row['precio'],
                'vendidos' => $vendidos,
                'ocupados' => $ocupados
            ]
        ]);
        break;
    //Destacados
    case 'featured':
        $sql = "SELECT id, titulo, stock, precio, imagen, descripcion
            FROM events
            WHERE is_destacada=1
            ORDER BY titulo ASC";
        $res = $conn->query($sql);
        if (!$res) {
            echo json_encode(['Result' => 'ERROR', 'Message' => $conn->error]);
            break;
        }
        $records = [];
        while ($row = $res->fetch_assoc()) {
            $eventoId = (int)$row['id'];
            // Contar boletos vendidos
            $vendidos = 0;
            $sqlV = "SELECT boleto 
                 FROM sales 
                 WHERE id_evento = $eventoId
                   AND estado IN ('Pendiente','Pagado')";
            $resV = $conn->query($sqlV);
            if ($resV) {
                while ($venta = $resV->fetch_assoc()) {
                    $nums = array_filter(array_map('trim', explode(',', $venta['boleto'])));
                    $vendidos += count($nums);
                }
            }
            $records[] = [
                'id'       => $eventoId,
                'title'    => $row['titulo'],
                'max'      => (int)$row['stock'],
                'price'    => (float)$row['precio'],
                'vendidos' => $vendidos,
                'descripcion' => $row['descripcion']
            ];
        }
        echo json_encode(['Result' => 'OK', 'Records' => $records]);
        break;

    // ------------------------- ACCIÓN NO VÁLIDA
    default:
        echo json_encode(['Result' => 'ERROR', 'Message' => 'Acción no válida']);
        break;
}

$conn->close();
