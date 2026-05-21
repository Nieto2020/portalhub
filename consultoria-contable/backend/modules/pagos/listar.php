<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'] ?? null;

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
    ";

    $params = [];

    if ($id_rol == ROL_CLIENTE) {
        $sql .= " WHERE pf.id_cliente = ?";
        $params[] = $id_usuario;
    }

    $sql .= " ORDER BY pf.id_pago DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Pagos obtenidos correctamente", $pagos);

} catch (PDOException $e) {

    sendResponse(500, "Error en la consulta", [
        "error" => $e->getMessage()
    ]);

}

?>
