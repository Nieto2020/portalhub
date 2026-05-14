<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

try {
    $sql = "
        SELECT 
            sc.id_servicio,
            sc.id_cliente,
            sc.id_tipo_servicio,
            sc.estado,
            sc.fecha_actualizacion,
            cs.nombre_servicio
        FROM servicios_contables sc
        LEFT JOIN cat_servicios cs 
            ON sc.id_tipo_servicio = cs.id_tipo_servicio
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Servicios obtenidos correctamente", $servicios);

} catch (PDOException $e) {
    sendResponse(500, "Error en la consulta", [
        "error" => $e->getMessage()
    ]);
}

?>
