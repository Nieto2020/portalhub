<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_cliente = 1;
$id_asesor = 1;
$fecha_hora = "2026-05-30 10:00:00";
$estado = "Programada";

try {
    $sql = "INSERT INTO citas
            (id_cliente, id_asesor, fecha_hora, estado)
            VALUES (:id_cliente, :id_asesor, :fecha_hora, :estado)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(":id_asesor", $id_asesor, PDO::PARAM_INT);
    $stmt->bindParam(":fecha_hora", $fecha_hora, PDO::PARAM_STR);
    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

    $stmt->execute();

    sendResponse(201, "Cita creada correctamente", [
        "id_cita" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error al crear cita", [
        "error" => $e->getMessage()
    ]);
}

?>
