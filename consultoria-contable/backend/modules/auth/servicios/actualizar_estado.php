<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_servicio = 9;
$estado = "Completado";

try {

    $sql = "UPDATE servicios_contables
            SET estado = :estado,
                fecha_actualizacion = NOW()
            WHERE id_servicio = :id_servicio";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
    $stmt->bindParam(":id_servicio", $id_servicio, PDO::PARAM_INT);

    $stmt->execute();

    sendResponse(200, "Estado actualizado correctamente");

} catch (PDOException $e) {

    sendResponse(500, "Error al actualizar estado", [
        "error" => $e->getMessage()
    ]);

}

?>
