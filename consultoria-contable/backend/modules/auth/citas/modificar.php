<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_cita = 2;
$nueva_fecha = "2026-06-01 09:30:00";

try {

    $sql = "UPDATE citas
            SET fecha_hora = :fecha_hora
            WHERE id_cita = :id_cita";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":fecha_hora", $nueva_fecha, PDO::PARAM_STR);
    $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);

    $stmt->execute();

    sendResponse(200, "Cita modificada correctamente");

} catch (PDOException $e) {

    sendResponse(500, "Error al modificar cita", [
        "error" => $e->getMessage()
    ]);

}

?>
