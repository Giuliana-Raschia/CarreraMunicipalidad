<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Para asegurar que el contenido sea interpretado como JSON

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

// Consulta para obtener todos los datos de la tabla `corredor` y el tiempo de `corredor_tiempo`
$query = "
    SELECT 
        c.id AS `Número de Corredor`,
        CONCAT(c.nombre, ' ', c.apellido) AS `Nombre Completo`,
        CONCAT(k.distancia_km, ' KM') AS `Distancia`,
        c.edad AS `Edad`,
        CASE
            WHEN ct.tiempo IS NOT NULL THEN ct.tiempo
            ELSE 'No registrado'
        END AS `Tiempo`
    FROM corredor c
    LEFT JOIN kilometros k ON c.distancia = k.id
    LEFT JOIN corredor_tiempo ct ON c.id = ct.corredor_id
";

$result = $conn->query($query);

if ($result === false) {
    echo json_encode(['status' => 'error', 'code' => 500, 'message' => 'Error al ejecutar la consulta.']);
    $conn->close();
    exit;
}

// Obtener todos los registros
$corredores = $result->fetch_all(MYSQLI_ASSOC);

// Cerrar la conexión
$conn->close();

// Enviar los datos en formato JSON
echo json_encode([
    'status' => 'success',
    'data' => $corredores
]);
?>
