<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_cita = 2;
$estado = "Cancelada";
$motivo_cancelacion = "Cancelación de prueba";

try {

    $sql = "UPDATE citas
            SET estado = :estado,
                motivo_cancelacion = :motivo_cancelacion
            WHERE id_cita = :id_cita";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
    $stmt->bindParam(":motivo_cancelacion", $motivo_cancelacion, PDO::PARAM_STR);
    $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);

    $stmt->execute();

    sendResponse(200, "Cita cancelada correctamente");

} catch (PDOException $e) {

    sendResponse(500, "Error al cancelar cita", [
        "error" => $e->getMessage()
    ]);

}

?>
