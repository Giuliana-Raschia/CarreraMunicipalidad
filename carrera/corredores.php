<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Conexión a la base de datos MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "carrera"; // Cambia esto por el nombre real de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'code' => 500, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// Consulta SQL para obtener los datos necesarios
$query = "
SELECT 
    c.id AS `Número de Corredor`,
    CONCAT(c.nombre, ' ', c.apellido) AS `Nombre Completo`,
    c.edad AS `Edad`,
    CONCAT(re.rango, ' - ', k.distancia_km, ' KM') AS `Categoria`
FROM corredor c
INNER JOIN kilometros k ON c.distancia = k.id
INNER JOIN categoria cat ON k.id = cat.kilometros_id
INNER JOIN rango_edad re ON cat.rango_edad_id = re.id
GROUP BY c.id, c.nombre, c.apellido, c.edad, k.distancia_km

";

// Ejecutar la consulta
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

// Cerrar la conexión
$conn->close();
?>
