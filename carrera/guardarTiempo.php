<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "carrera";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// Obtener datos del POST
$data = json_decode(file_get_contents("php://input"), true);
$corredorId = $data['corredorId'] ?? null;
$tiempo = $data['tiempo'] ?? null;

// Validar datos
if ($corredorId && $tiempo) {
    // Asegúrate de que el formato de tiempo sea correcto
    if (preg_match('/^\d{2}:\d{2}:\d{2}:\d{2}$/', $tiempo)) {
        // Preparar y ejecutar la consulta
        $stmt = $conn->prepare("
            INSERT INTO corredor_tiempo (corredor_id, tiempo) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE tiempo = VALUES(tiempo)
        ");
        $stmt->bind_param("is", $corredorId, $tiempo);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Tiempo guardado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar el tiempo.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Formato de tiempo inválido.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos faltantes.']);
}

$conn->close();
?>
