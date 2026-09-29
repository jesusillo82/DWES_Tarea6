<?php
// Realizamos conexión con la librería
require_once 'LibreriaPDO.php'; 

//Instanciamos en esa base de datos.
$db = new DB("equipos"); 

header("Content-Type: application/json"); 

//Envío seguro de la selección de equipos mediante el método POST 
$codigos = $_POST['codigos']; 

//array_fill para generar un ? para código
$arrayCod = array_fill(0, count($codigos), '?'); // desde el indice 0 del array $ids, los cuenta y genera para cada id un ?


$codigosCad = implode(',', $arrayCod); 

//Consulta SQL para la base de datos
$consulta = "DELETE FROM equipos WHERE Id IN ($codigosCad)";

//Verifico si es correcto, y sino enviamos un error.
if ( !($db->ConsultaSimple($consulta, $codigos))) {
    http_response_code(500);
    echo json_encode(["error" => "NO se ha eliminado en la Base de Datos"]);
    exit;
}

$db->ConsultaDatos("SELECT Id FROM equipos ORDER BY Puesto ASC, Id ASC");
$puesto = 1;
foreach ($db->filas as $equipo) {
    if (!$db->ConsultaSimple("UPDATE equipos SET Puesto = ? WHERE Id = ?", [$puesto, $equipo['Id']])) {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo reorganizar la clasificación"]);
        exit;
    }
    $puesto++;
}

echo json_encode(["success" => true]);

?>