<?php

require_once "../../config/conexion.php";
require_once "../../utils/response.php";

$id_pago = 4;

try {

    $sql = "
        SELECT
            pf.id_pago,
            pf.id_cliente,
            pf.id_servicio,
            pf.monto,
            pf.estado_pago,
            pf.estado_factura,
            pf.fecha_pago,
            sc.estado AS estado_servicio,
            cs.nombre_servicio
        FROM pagos_facturacion pf
        LEFT JOIN servicios_contables sc
            ON pf.id_servicio = sc.id_servicio
        LEFT JOIN cat_servicios cs
            ON sc.id_tipo_servicio = cs.id_tipo_servicio
        WHERE pf.id_pago = :id_pago
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":id_pago", $id_pago, PDO::PARAM_INT);
    $stmt->execute();

    $pago = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pago) {
        sendResponse(404, "Pago no encontrado");
    }

    sendResponse(200, "Detalle de pago obtenido correctamente", $pago);

} catch (PDOException $e) {

    sendResponse(500, "Error al obtener detalle de pago", [
        "error" => $e->getMessage()
    ]);

}

?>
