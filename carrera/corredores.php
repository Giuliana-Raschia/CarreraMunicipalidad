<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Conexión a la base de datos MySQL
$servername = "localhost";
$username = "u983319120_franciscardo";
$password = "@20Cqx#VGm";
$database = "u983319120_carrera"; // Cambia esto por el nombre real de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'code' => 500, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// Obtener parámetros de la solicitud POST
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$sexo = isset($_POST['sexo']) ? $_POST['sexo'] : '';
$categoria = isset($_POST['categoria']) ? $_POST['categoria'] : '';
$edadMin = isset($_POST['edad_min']) ? (int)$_POST['edad_min'] : 0;
$edadMax = isset($_POST['edad_max']) ? (int)$_POST['edad_max'] : 120;

if ($accion === 'ranking') {
    // Consulta de ranking
    $query = "
        SELECT c.id AS `Número de Corredor`,
               CONCAT(c.nombre, ' ', c.apellido) AS `Nombre Completo`,
               c.edad AS `Edad`,
               CONCAT(re.rango, ' - ', k.distancia_km, ' KM') AS `Categoría`,
               t.tiempo AS `Tiempo`
        FROM corredor_tiempo t
        JOIN corredor c ON t.corredor_id = c.id
        JOIN categoria ca ON c.categoria = ca.id
        JOIN kilometros k ON ca.kilometros_id = k.id
        JOIN rango_edad re ON ca.rango_edad_id = re.id
        WHERE 1=1
    ";

    // Aplicar filtros
    if ($sexo) {
        $query .= " AND c.sexo = ?";
    }
    if ($categoria) {
        $query .= " AND ca.id = ?";
    }
    if ($edadMin) {
        $query .= " AND c.edad >= ?";
    }
    if ($edadMax) {
        $query .= " AND c.edad <= ?";
    }

    // Ordenar los resultados por tiempo
    $query .= " ORDER BY t.tiempo ASC";

    try {
        $stmt = $conn->prepare($query);

        // Asignar parámetros de los filtros
        $params = [];
        $types = '';
        if ($sexo) {
            $params[] = $sexo;
            $types .= 's';
        }
        if ($categoria) {
            $params[] = $categoria;
            $types .= 'i';
        }
        if ($edadMin) {
            $params[] = $edadMin;
            $types .= 'i';
        }
        if ($edadMax) {
            $params[] = $edadMax;
            $types .= 'i';
        }

        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al ejecutar la consulta']);
    }
} else {
    // Consulta de corredores (código existente para esta funcionalidad)
    $query = "
        SELECT 
            c.id AS `Número de Corredor`,
            CONCAT(c.nombre, ' ', c.apellido) AS `Nombre Completo`,
            c.edad AS `Edad`,
            CONCAT(re.rango, ' - ', k.distancia_km, ' KM') AS `Categoría`,
            c.sexo AS `Sexo`
        FROM corredor c
        INNER JOIN kilometros k ON c.distancia = k.id
        INNER JOIN categoria cat ON k.id = cat.kilometros_id
        INNER JOIN rango_edad re ON cat.rango_edad_id = re.id
        INNER JOIN sexo s ON c.sexo_id = s.id
        GROUP BY c.id, c.nombre, c.apellido, c.edad, k.distancia_km, c.sexo
    ";

    try {
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
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al ejecutar la consulta']);
    }
}

// Cerrar la conexión
$conn->close();
?>
