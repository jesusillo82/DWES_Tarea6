# Batería de pruebas - Gestión de equipos

## 1. Datos de la prueba

- Aplicación: `aF_DWES06_Tarea`
- URL: `http://localhost/DWES/aF_DWES06_Tarea/index.html`
- Entorno: XAMPP, Apache, PHP y MariaDB/MySQL activos.
- Base de datos: importar `baseDeDatos/equipos.sql` antes de comenzar.
- Datos iniciales esperados: Real Madrid, Barcelona, Atletico Madrid y Valencia, ordenados por puesto.
- Imagen válida: cualquier archivo `.jpg`, `.jpeg` o `.png` pequeño.

> Para repetir las pruebas destructivas, importar de nuevo el script SQL o restaurar una copia de la base de datos.

## 2. Criterio de registro

En cada prueba se debe marcar `OK` si el resultado real coincide con el esperado. Si no coincide, marcar `FALLO` y anotar el mensaje o comportamiento observado.

| ID | Caso | Pasos | Resultado esperado | Resultado real / Observaciones |
|---|---|---|---|---|
| P01 | Carga inicial | Abrir la URL de la aplicación. | Se muestra la tabla con los equipos sin recargar manualmente la página. | [ ] OK  [ ] FALLO  |
| P02 | Orden inicial | Revisar la columna `Puesto`. | Los equipos aparecen ordenados de menor a mayor puesto y sin puestos repetidos. | [ ] OK  [ ] FALLO  |
| P03 | Datos e imágenes | Revisar nombre, fecha, presupuesto y logo de cada fila. | Todos los datos se muestran y cada logo se visualiza correctamente. | [ ] OK  [ ] FALLO  |
| P04 | Actualización automática | Abrir la aplicación y esperar al menos 15 segundos. | La tabla vuelve a solicitar los datos sin que se recargue toda la página. | [ ] OK  [ ] FALLO  |
| P05 | Alta válida | Completar nombre `Sevilla`, fecha válida, presupuesto `250`, seleccionar el puesto mostrado y adjuntar una imagen. Pulsar `Agregar`. | Aparece el mensaje de éxito, el formulario se limpia y el nuevo equipo aparece en la tabla sin recargar la página. | [ ] OK  [ ] FALLO  |
| P06 | Campos obligatorios | Intentar enviar el formulario sin nombre, fecha, presupuesto o imagen. | El navegador impide el envío e indica el campo obligatorio que falta. | [ ] OK  [ ] FALLO  |
| P07 | Alta con imagen no válida | Intentar adjuntar un archivo que no sea una imagen. | La aplicación rechaza el archivo o muestra un error controlado; no se crea un equipo incompleto. | [ ] OK  [ ] FALLO  |
| P08 | Alta con datos extremos | Probar presupuesto `0`, un nombre de 50 caracteres y una fecha válida. | La aplicación acepta o rechaza los valores de forma controlada, mostrando un mensaje claro. | [ ] OK  [ ] FALLO  |
| P09 | Cambio de puesto | Cambiar el puesto de un equipo, por ejemplo de `4` a `1`. | Se muestra confirmación, se actualiza la tabla y los demás equipos se reordenan sin puestos repetidos. | [ ] OK  [ ] FALLO  |
| P10 | Mismo puesto | Seleccionar para un equipo el mismo puesto que ya tenía. | No se realiza una petición innecesaria ni cambia la clasificación. | [ ] OK  [ ] FALLO  |
| P11 | Puesto inexistente | Probar mediante una petición directa un ID inexistente o un puesto fuera del rango. | Se devuelve un error JSON controlado o se limita el puesto al rango permitido; no se modifica otro equipo. | [ ] OK  [ ] FALLO  |
| P12 | Cancelar eliminación | Marcar un equipo, pulsar `Eliminar` y elegir `Cancelar`. | El equipo permanece en la tabla y en la base de datos. | [ ] OK  [ ] FALLO  |
| P13 | Eliminar sin selección | Pulsar `Eliminar` sin marcar equipos. | Se muestra `Escoja un equipo.` y no se realiza ninguna petición de borrado. | [ ] OK  [ ] FALLO  |
| P14 | Eliminar un equipo | Marcar un equipo, pulsar `Eliminar` y confirmar. | El equipo desaparece sin recargar la página y los puestos restantes se compactan. | [ ] OK  [ ] FALLO  |
| P15 | Eliminar varios equipos | Marcar dos o más equipos, pulsar `Eliminar` y confirmar. | Todos los seleccionados desaparecen y los puestos restantes quedan consecutivos. | [ ] OK  [ ] FALLO  |
| P16 | Persistencia | Después de un alta, cambio o borrado, cerrar y volver a abrir la página. | La modificación permanece en la base de datos y se muestra al volver a cargar. | [ ] OK  [ ] FALLO  |
| P17 | Respuesta de listado | Abrir `listar.php` directamente. | Se devuelve un JSON válido con los equipos, fechas en formato `dd/mm/aaaa` y el logo codificado. | [ ] OK  [ ] FALLO  |
| P18 | Diseño adaptable | Probar la página en escritorio y reducir el ancho hasta móvil. | La tabla se puede desplazar horizontalmente, los formularios siguen siendo utilizables y no se solapan los controles. | [ ] OK  [ ] FALLO  |

## 3. Pruebas de integridad de la base de datos

Después de ejecutar las pruebas, comprobar en `equipos`:

- `Id` es único.
- `Nombre`, `FechaFund`, `Presupuesto`, `Puesto` y `Logo` contienen valores válidos.
- Los puestos empiezan en `1` y no tienen duplicados.
- Tras eliminar equipos, los puestos siguen siendo consecutivos.
- Tras cambiar un puesto, solo cambia el orden, no se pierden equipos.

## 4. Resumen de ejecución

- Fecha:
- Persona responsable:
- Navegador y versión:
- Pruebas realizadas:
- Pruebas correctas:
- Pruebas fallidas:
- Incidencias encontradas:

## 5. Ejecución automática

El archivo `ejecutar_pruebas.php` comprueba automáticamente la respuesta de la página, el listado JSON, la validación de datos incompletos, el alta de un equipo de prueba, el cambio de puesto, la eliminación y la integridad de los puestos.

Con Apache, PHP y MariaDB/MySQL activos, ejecutar desde la raíz del proyecto:

```text
php pruebas/ejecutar_pruebas.php
```

También se puede indicar otra URL base:

```text
php pruebas/ejecutar_pruebas.php http://localhost/DWES/aF_DWES06_Tarea
```

El script crea un equipo temporal, lo modifica y lo elimina al terminar. Las pruebas visuales, de responsive y de interacción con el navegador deben seguir realizándose manualmente.
