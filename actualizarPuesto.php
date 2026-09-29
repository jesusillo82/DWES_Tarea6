<?php
// Realizamos conexión con la librería
require_once 'LibreriaPDO.php'; 

//Instanciamos en esa base de datos.
$db = new DB("equipos");

header("Content-Type: application/json");

//Envío seguro de datos desde el formulario mediante POST
$id = $_POST['id'] ?? null;
$puestoNuevo = $_POST['puesto'] ?? null;

if ($id === null || $puestoNuevo === null) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos para actualizar el puesto"]);
    exit;
}

$db->ConsultaDatos("SELECT Id, Puesto FROM equipos ORDER BY Puesto ASC", []);
$equipos = $db->filas;

if (empty($equipos)) {
    http_response_code(404);
    echo json_encode(["error" => "No hay equipos en la base de datos"]);
    exit;
}

// Buscamos el índice del equipo con el ID proporcionado
$indiceEquipo = null;
foreach ($equipos as $indice => $equipo) {
    if ((int)$equipo['Id'] === (int)$id) {
        $indiceEquipo = $indice;
        break;
    }
}

// Si no se encuentra el equipo, devolvemos un error
if ($indiceEquipo === null) {
    http_response_code(404);
    echo json_encode(["error" => "No se ha encontrado el equipo"]);
    exit;
}

// Validamos el nuevo puesto para que esté dentro del rango permitido
$puestoActual = (int)$equipos[$indiceEquipo]['Puesto'];
$puestoNuevo = max(1, min((int)$puestoNuevo, count($equipos)));

// Si el puesto actual es igual al nuevo, no hacemos nada
if ($puestoActual === $puestoNuevo) {
    echo json_encode(["success" => true]);
    exit;
}
// Movemos el equipo al nuevo puesto y reordenamos los demás
$equipoMovido = $equipos[$indiceEquipo];
array_splice($equipos, $indiceEquipo, 1);
array_splice($equipos, $puestoNuevo - 1, 0, [$equipoMovido]);

// Actualizamos los puestos en la base de datos
foreach ($equipos as $indice => $equipo) {
    $nuevoPuesto = $indice + 1;
    if (!($db->ConsultaSimple("UPDATE equipos SET Puesto = ? WHERE Id = ?", [$nuevoPuesto, $equipo['Id']]))) {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo reordenar la clasificación"]);
        exit;
    }
}

echo json_encode(["success" => true]);

?>