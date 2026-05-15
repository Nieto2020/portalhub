<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_cliente = 1;
$id_tipo_servicio = 1;
$estado = "Pendiente";

try {
    $sql = "INSERT INTO servicios_contables 
            (id_cliente, id_tipo_servicio, estado)
            VALUES (:id_cliente, :id_tipo_servicio, :estado)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(":id_tipo_servicio", $id_tipo_servicio, PDO::PARAM_INT);
    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

    $stmt->execute();

    sendResponse(201, "Servicio creado correctamente", [
        "id_servicio" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error al crear servicio", [
        "error" => $e->getMessage()
    ]);
}

?>
