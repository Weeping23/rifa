<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$conn = new mysqli("srv1788.hstgr.io", "u119832370_rifas", "+s>e6|Sd1H", "u119832370_rifas");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'msg' => "Error de conexión"]);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true);
$usuario = $conn->real_escape_string($input['usuario'] ?? '');
$pass    = $conn->real_escape_string($input['password'] ?? '');

if (empty($usuario) || empty($pass)) {
    echo json_encode(['success' => false, 'msg' => "Faltan datos"]);
    exit;
}

// Prepara la consulta con marcadores de posición
$stmt = $conn->prepare("SELECT * FROM users WHERE usuario = ? AND `contraseña` = ? LIMIT 1");

// Asocia las variables a los marcadores
$stmt->bind_param("ss", $usuario, $pass);

// Ejecuta la consulta
$stmt->execute();

// Obtiene el resultado
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $user = $res->fetch_assoc();

    // Guardar en sesión
    $_SESSION['user'] = [
        'id'      => $user['id'],
        'nombre'  => $user['nombre'],
        'apellido' => $user['apellido'],
        'usuario' => $user['usuario'],
        'admin'   => (int)$user['admin']
    ];

    // Responder con el usuario para que el front lo guarde
    echo json_encode([
        'success' => true,
        'msg'     => "Login correcto",
        'user'    => [
            'id'      => (int)$user['id'],
            'usuario' => $user['usuario'],
            'apellido' => $user['apellido'],
            'nombre'  => $user['nombre'],
            'admin'   => (int)$user['admin']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'msg' => "Usuario o contraseña incorrectos"]);
}

$conn->close();
