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

// Obtener los datos del formulario
$id = $_POST['id'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$distancia = $_POST['distancia']; // Distancia en km proporcionada por el usuario
$edad = $_POST['edad'];

// Verificar si el ID ya existe en la tabla `corredor`
$checkIdQuery = "SELECT id FROM corredor WHERE id = ?";
$stmt = $conn->prepare($checkIdQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'code' => 409, 'message' => 'El ID proporcionado ya está registrado.']);
    $stmt->close();
    $conn->close();
    exit;
}

// Verificar si la distancia existe en la tabla `kilometros`
$kilometrosQuery = "SELECT id FROM kilometros WHERE distancia_km = ?";
$stmt = $conn->prepare($kilometrosQuery);
$stmt->bind_param("i", $distancia);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'code' => 400, 'message' => 'La distancia proporcionada no es válida.']);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$kilometros_id = $row['id'];

// Obtener una lista de `rango_edad_id` para el `kilometros_id` desde la tabla `categoria`
$categoriaQuery = "SELECT rango_edad_id FROM categoria WHERE kilometros_id = ?";
$stmt = $conn->prepare($categoriaQuery);
$stmt->bind_param("i", $kilometros_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'code' => 404, 'message' => 'No existe una categoría para los datos proporcionados.']);
    $stmt->close();
    $conn->close();
    exit;
}

// Obtener todos los `rango_edad_id` para verificar el rango de edad
$rangoEdadIds = [];
while ($row = $result->fetch_assoc()) {
    $rangoEdadIds[] = $row['rango_edad_id'];
}

// Verificar el rango de edad para cada `rango_edad_id` obtenido
$found = false;
$categoria_id = null;

foreach ($rangoEdadIds as $rango_edad_id) {
    $rangoEdadQuery = "SELECT id FROM rango_edad WHERE id = ? AND ? BETWEEN edad_minima AND edad_maxima";
    $stmt = $conn->prepare($rangoEdadQuery);
    $stmt->bind_param("ii", $rango_edad_id, $edad);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Encontrar el `categoria_id` basado en el `rango_edad_id` y `kilometros_id`
        $categoriaQuery = "SELECT id FROM categoria WHERE kilometros_id = ? AND rango_edad_id = ?";
        $stmt = $conn->prepare($categoriaQuery);
        $stmt->bind_param("ii", $kilometros_id, $rango_edad_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $categoria_id = $row['id'];
            $found = true;
            break; // Salir del bucle una vez que se ha encontrado una categoría válida
        }
    }
}

if (!$found) {
    echo json_encode(['status' => 'error', 'code' => 401, 'message' => 'La edad proporcionada no se encuentra en un rango válido.']);
    $stmt->close();
    $conn->close();
    exit;
}

// Insertar los datos en la tabla `corredor`
$insertCorredorQuery = "INSERT INTO corredor (id, nombre, apellido, distancia, edad, categoria) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertCorredorQuery);
$stmt->bind_param("issiii", $id, $nombre, $apellido, $kilometros_id, $edad, $categoria_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Corredor registrado exitosamente.']);
} else {
    echo json_encode(['status' => 'error', 'code' => 500, 'message' => 'Error al registrar el corredor: ' . $conn->error]);
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
