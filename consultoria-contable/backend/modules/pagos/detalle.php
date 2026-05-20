<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, "Método no permitido");
}

$id_pago = $_GET['id_pago'] ?? null;

if (!$id_pago) {
    sendResponse(400, "ID de pago no proporcionado");
}

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

    $id_usuario = $_SESSION['id_usuario'];
    $rol = $_SESSION['rol'] ?? null;
    
    if ($rol === ROL_CLIENTE && $pago['id_cliente'] != $id_usuario) {
        sendResponse(403, "No tiene permisos para ver este pago");
    }

    sendResponse(200, "Detalle de pago obtenido correctamente", $pago);

} catch (PDOException $e) {

    sendResponse(500, "Error al obtener detalle de pago", [
        "error" => $e->getMessage()
    ]);

}

?>
