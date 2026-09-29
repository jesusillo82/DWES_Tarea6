<?php

//Incluimos la libreria para hacer consultas
require_once 'LibreriaPDO.php';  

//Instanciamos en esa BBDD
$db = new DB("equipos"); 

$db->ConsultaDatos("SELECT * FROM equipos ORDER BY Puesto ASC");  //Listamos todos los equipos ordenados por su puesto

$equipos = array();  //Creamos un array para guardar todos los equipos

//Por cada una de las filas...
foreach($db->filas as $fila)
{
    $fila['FechaFund']=date("d/m/Y", $fila['FechaFund']); //Convertimos la fecha de formato Epoch a formato legible
    $equipos[]=$fila;  //Insertamos la fila de ese equipo
}

header('Content-Type: application/json');     //Se indica que la salida de pantalla va a mostrar información en formato JSON

echo json_encode($equipos);  //Codificamos el array Php a datos Json

?>
