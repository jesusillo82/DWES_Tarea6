<?php

/*Campos del formulario para solicitar al usuario
    
*/

require_once 'LibreriaPDO.php';

$db = new DB("equipos");

header("Content-Type: application/json"); 




//Se envían datos del formulario de forma segura con POST
  
    $nombre_equipo = $_POST['nombre'];
    $fundac_Año= $_POST['fecha_fund'];
    $fecha_form = DateTime::createFromFormat("Y-m-d", $fundac_Año);
    $fecha_epoch = $fecha_form->getTimestamp();
    $presupuesto = $_POST['presupuesto'];
    // Compactamos puestos existentes y reservamos el siguiente para el nuevo equipo.
    $db->ConsultaDatos("SELECT Id FROM equipos ORDER BY Puesto ASC, Id ASC");
    $equiposExistentes = $db->filas;
    $puesto = 1;
    foreach ($equiposExistentes as $equipoExistente) {
        if (!$db->ConsultaSimple("UPDATE equipos SET Puesto = ? WHERE Id = ?", [$puesto, $equipoExistente['Id']])) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo reorganizar la clasificación"]);
            exit;
        }
        $puesto++;
    }

    //Convertimos en un BLOB la imagen
    $logo_tmp = $_FILES['logo']['tmp_name'];
    $logo_dat = file_get_contents($logo_tmp); // Convertir la imagen en un BLOB

    // Codificamos la imagen en base64 para almacenarla en la base de datos
    $logo_data = base64_encode($logo_dat); 
    
    // Guardo la imagen en formato Blob en la base de datos con sql
    $consulta = "INSERT INTO equipos (Nombre, FechaFund, Presupuesto, Puesto, Logo) VALUES (?, ?, ?, ?, ?)";
    $parametros = [$nombre_equipo, $fecha_epoch, $presupuesto, $puesto, $logo_data];

    // Verifico si es correcto, y sino enviamos un error.
    if (!$db->ConsultaSimple($consulta, $parametros)) {
        http_response_code(500);
        echo json_encode(["error" => "Error al insertar en la base de datos"]);
        exit;
    }
    // Si todo es correcto, enviamos una respuesta de éxito
    echo json_encode(["success" => true]);

?>
