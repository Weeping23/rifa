<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "srv1788.hstgr.io";
$username = "u119832370_rifas";
$password = "+s>e6|Sd1H";
$dbname = "u119832370_rifas";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "msg" => "Error de conexión: " . $conn->connect_error]);
    exit;
}

$action = $_GET["action"] ?? "";

function getJsonInput() {
    $data = json_decode(file_get_contents("php://input"), true);
    return $data ?: [];
}

function normCurrency($c) {
    return strtoupper(trim($c ?? ""));
}

function normRate($r) {
    if ($r === null) return null;
    $r = str_replace(",", ".", trim((string)$r));
    return is_numeric($r) ? (float)$r : null;
}

switch ($action) {
    case "list":
        $sql = "SELECT currency, amount FROM rates ORDER BY currency ASC";
        $result = $conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode(["success" => true, "records" => $rows]);
        break;

    case "create":
        $input = getJsonInput();
        $currency = normCurrency($input["currency"] ?? "");
        $amount = normRate($input["amount"] ?? null);

        if ($currency === "" || $amount === null) {
            echo json_encode(["success" => false, "code" => "invalid", "msg" => "Datos incompletos"]);
            break;
        }

        // Verificar si ya existe la moneda
        $stmt = $conn->prepare("SELECT 1 FROM rates WHERE currency = ? LIMIT 1");
        $stmt->bind_param("s", $currency);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            echo json_encode(["success" => false, "code" => "duplicate", "msg" => "La moneda {$currency} ya existe"]);
            break;
        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO rates (currency, amount) VALUES (?, ?)");
        $stmt->bind_param("sd", $currency, $amount);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "msg" => "Tarifa creada"]);
        } else {
            echo json_encode(["success" => false, "msg" => "Error: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case "update":
        $input = getJsonInput();
        $currency = normCurrency($input["currency"] ?? "");
        $amount = normRate($input["amount"] ?? null);
        $originalCurrency = normCurrency($input["originalCurrency"] ?? "");

        if ($currency === "" || $amount === null || $originalCurrency === "") {
            echo json_encode(["success" => false, "code" => "invalid", "msg" => "Datos incompletos"]);
            break;
        }

        // Si se cambia la moneda, verifica que la nueva no exista
        if ($currency !== $originalCurrency) {
            $stmt = $conn->prepare("SELECT 1 FROM rates WHERE currency = ? LIMIT 1");
            $stmt->bind_param("s", $currency);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->close();
                echo json_encode(["success" => false, "code" => "duplicate", "msg" => "La moneda {$currency} ya existe"]);
                break;
            }
            $stmt->close();
        }

        // Actualizar
        $stmt = $conn->prepare("UPDATE rates SET currency = ?, amount = ? WHERE currency = ?");
        $stmt->bind_param("sds", $currency, $amount, $originalCurrency);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "msg" => "Tarifa actualizada"]);
        } else {
            echo json_encode(["success" => false, "msg" => "Error: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case "delete":
        $currency = normCurrency($_GET["currency"] ?? "");
        if ($currency === "") {
            echo json_encode(["success" => false, "code" => "invalid", "msg" => "Moneda no especificada"]);
            break;
        }
        $stmt = $conn->prepare("DELETE FROM rates WHERE currency=?");
        $stmt->bind_param("s", $currency);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "msg" => "Tarifa eliminada"]);
        } else {
            echo json_encode(["success" => false, "msg" => "Error: " . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(["success" => false, "code" => "bad_action", "msg" => "Acción no válida"]);
        break;
}
$conn->close();
?>
