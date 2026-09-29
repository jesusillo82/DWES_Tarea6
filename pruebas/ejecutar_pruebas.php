<?php

/*
    El archivo `ejecutar_pruebas.php` es un script de pruebas automáticas. Comprueba que la aplicación responde correctamente y que sus operaciones principales funcionan.
    Creado por agente ia. Se ejecuta desde la línea de comandos y requiere PHP con las extensiones `curl` y `PDO_MySQL` habilitadas.

**Qué comprueba**

- La página principal responde correctamente.
- `listar.php` devuelve un JSON válido.
- Los equipos aparecen ordenados por puesto.
- Se rechazan datos incompletos.
- Se puede crear un equipo con una imagen.
- Se puede actualizar su puesto.
- Se puede eliminar el equipo creado.
- Los puestos quedan consecutivos después de las operaciones.

El equipo creado durante la prueba es temporal: el script lo modifica y lo elimina al finalizar.

**Cómo ejecutarlo**

1. Inicia Apache y MySQL/MariaDB desde XAMPP.
2. Comprueba que la base de datos `equipos` existe y contiene la tabla correspondiente.
3. Abre PowerShell en la carpeta raíz del proyecto:

```text
C:\xampp\htdocs\DWES\aF_DWES06_Tarea
```

4. Ejecuta:

```text
php pruebas\ejecutar_pruebas.php
```

También puedes indicar manualmente la URL de la aplicación:

```text
php pruebas\ejecutar_pruebas.php http://localhost/DWES/aF_DWES06_Tarea
```

Si todo funciona, aparecerá:

```text
Resultado: 8/8 pruebas correctas
```

El script devuelve código `0` cuando todo está correcto y código `1` si alguna prueba falla. Requiere que PHP tenga habilitadas las extensiones `curl` y `PDO_MySQL`.


*/

declare(strict_types=1);

// Uso: php ejecutar_pruebas.php [URL_BASE]
// Ejemplo: php ejecutar_pruebas.php http://localhost/DWES/aF_DWES06_Tarea

$baseUrl = rtrim($argv[1] ?? 'http://localhost/DWES/aF_DWES06_Tarea', '/');
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'equipos';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

$tests = 0;
$passedTests = 0;
$createdId = null;
$tempImage = null;

// Función auxiliar para registrar el resultado de cada prueba
function checkTest(string $id, string $description, bool $success, string $details = ''): void
{
    global $tests;
    global $passedTests;

    $tests++;
    if ($success) {
        $passedTests++;
    }

    $status = $success ? 'OK' : 'FALLO';
    echo sprintf("[%s] %s - %s", $status, $id, $description);
    if ($details !== '') {
        echo ': ' . $details;
    }
    echo PHP_EOL;
}

// Función auxiliar para realizar solicitudes HTTP con cURL
function request(string $url, string $method = 'GET', array $fields = [], ?string $file = null): array
{
    $curl = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CUSTOMREQUEST => $method,
    ];

    if ($method === 'POST') {
        if ($file !== null) {
            $fields['logo'] = new CURLFile($file, 'image/png', 'prueba.png');
        }
        $options[CURLOPT_POSTFIELDS] = $fields;
    }

    curl_setopt_array($curl, $options);
    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return [
        'status' => $status,
        'body' => $body === false ? '' : $body,
        'error' => $error,
    ];
}

// Función auxiliar para decodificar la respuesta JSON
function jsonResponse(array $response): ?array
{
    $data = json_decode($response['body'], true);
    return is_array($data) ? $data : null;
}

// Función auxiliar para verificar si los equipos están ordenados por puesto consecutivo
function isSortedByPosition(array $teams): bool
{
    $positions = array_map(static fn(array $team): int => (int)($team['Puesto'] ?? 0), $teams);
    return $positions === array_values($positions) && $positions === array_map('intval', $positions)
        && $positions === array_keys(array_flip($positions))
        && $positions === range(1, count($positions));
}

/*
   **************+  Conexión a la base de datos y ejecución de pruebas
*/

// Conexión a la base de datos
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Throwable $exception) {
    fwrite(STDERR, "No se pudo conectar a la base de datos: {$exception->getMessage()}" . PHP_EOL);
    exit(1);
}

// Ejecutar las pruebas
$index = request($baseUrl . '/index.html');
checkTest(
    'P01',
    'La página principal responde',
    $index['status'] === 200 && str_contains($index['body'], 'tablaEquipos'),
    "HTTP {$index['status']}"
);

// Verificar que listar.php devuelve JSON válido
$list = request($baseUrl . '/listar.php');
$teams = jsonResponse($list);
$validList = $list['status'] === 200 && is_array($teams);
checkTest('P02', 'listar.php devuelve JSON válido', $validList, "HTTP {$list['status']}");

// Verificar que los equipos están ordenados por puesto consecutivo
if ($validList) {
    $positions = array_map(static fn(array $team): int => (int)$team['Puesto'], $teams);
    checkTest(
        'P03',
        'Los equipos están ordenados por puesto',
        $positions === array_values($positions) && $positions === range(1, count($positions)),
        'puestos: ' . implode(', ', $positions)
    );
} else {
    checkTest('P03', 'Los equipos están ordenados por puesto', false, 'No se pudo leer el listado');
}

// Verificar que la actualización rechaza datos incompletos
$missingUpdate = request($baseUrl . '/actualizarPuesto.php', 'POST', []);
checkTest(
    'P11',
    'La actualización rechaza datos incompletos',
    $missingUpdate['status'] === 400 && jsonResponse($missingUpdate) !== null,
    "HTTP {$missingUpdate['status']}"
);

// Crear un equipo temporal para las pruebas
$tempImage = tempnam(sys_get_temp_dir(), 'equipo_');
file_put_contents($tempImage, base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
));

// Crear un equipo temporal con imagen
$uniqueName = 'Prueba automatizada ' . date('YmdHis');
$create = request($baseUrl . '/agregar.php', 'POST', [
    'nombre' => $uniqueName,
    'fecha_fund' => '2000-01-01',
    'presupuesto' => '250',
    'puesto' => '1',
], $tempImage);
$createData = jsonResponse($create);
$createdId = $createData['id'] ?? null;
// Si no se devuelve el ID, buscarlo en la base de datos por nombre
if ($createdId === null) {
    $createdTeam = $pdo->prepare('SELECT Id FROM equipos WHERE Nombre = ? ORDER BY Id DESC LIMIT 1');
    $createdTeam->execute([$uniqueName]);
    $createdId = $createdTeam->fetchColumn() ?: null;
}
// Verificar que se creó correctamente el equipo temporal
checkTest(
    'P05',
    'Se puede agregar un equipo con imagen',
    $create['status'] === 200 && $createData !== null && ($createData['success'] ?? false) === true && $createdId !== null,
    "HTTP {$create['status']}"
);
// Verificar que el equipo temporal aparece en la lista
if ($createdId !== null) {
    $update = request($baseUrl . '/actualizarPuesto.php', 'POST', [
        'id' => (string)$createdId,
        'puesto' => '1',
    ]);
    $updateData = jsonResponse($update);
    // Verificar que se puede actualizar el puesto del equipo temporal
    checkTest(
        'P09',
        'Se puede actualizar el puesto de un equipo',
        $update['status'] === 200 && ($updateData['success'] ?? false) === true,
        "HTTP {$update['status']}"
    );
    // Verificar que se puede eliminar el equipo temporal
    $delete = request($baseUrl . '/eliminar.php', 'POST', [
        'codigos[]' => (string)$createdId,
    ]);
    // Verificar que el equipo temporal ya no existe en la base de datos
    $deleteData = jsonResponse($delete);
    $remaining = $pdo->prepare('SELECT COUNT(*) FROM equipos WHERE Id = ?');
    $remaining->execute([$createdId]);
    // Verificar que se puede eliminar el equipo temporal y que ya no existe en la base de datos
    checkTest(
        'P14',
        'Se puede eliminar el equipo de prueba',
        $delete['status'] === 200 && ($deleteData['success'] ?? false) === true && (int)$remaining->fetchColumn() === 0,
        "HTTP {$delete['status']}"
    );
} else {
    checkTest('P09', 'Se puede actualizar el puesto de un equipo', false, 'No se creó el equipo de prueba');
    checkTest('P14', 'Se puede eliminar el equipo de prueba', false, 'No se creó el equipo de prueba');
}

// Verificar que los puestos quedan consecutivos después de las operaciones
$integrity = $pdo->query('SELECT Puesto FROM equipos ORDER BY Puesto ASC')->fetchAll(PDO::FETCH_COLUMN);
$integrity = array_map('intval', $integrity);
// Verificar que los puestos son consecutivos
checkTest(
    'P16',
    'Los puestos quedan consecutivos después de las operaciones',
    $integrity === range(1, count($integrity)),
    'puestos: ' . implode(', ', $integrity)
);
// Limpiar la imagen temporal
if ($tempImage !== null && is_file($tempImage)) {
    unlink($tempImage);
}
// Mostrar el resultado final y salir con el código adecuado
$totalFailed = $tests - $passedTests;
echo PHP_EOL . "Resultado: {$passedTests}/{$tests} pruebas correctas" . PHP_EOL;
exit($totalFailed === 0 ? 0 : 1);
