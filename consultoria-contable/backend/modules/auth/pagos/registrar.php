<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_cliente = 1;
$id_servicio = 9;
$monto = 1500.00;
$estado_pago = "Pendiente";

try {

    $sql = "INSERT INTO pagos_facturacion
            (id_cliente, id_servicio, monto, estado_pago)
            VALUES (:id_cliente, :id_servicio, :monto, :estado_pago)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(":id_servicio", $id_servicio, PDO::PARAM_INT);
    $stmt->bindParam(":monto", $monto);
    $stmt->bindParam(":estado_pago", $estado_pago, PDO::PARAM_STR);

    $stmt->execute();

    sendResponse(201, "Pago registrado correctamente", [
        "id_pago" => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {

    sendResponse(500, "Error al registrar pago", [
        "error" => $e->getMessage()
    ]);

}

?>
