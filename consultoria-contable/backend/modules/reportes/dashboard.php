<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

try {
    $totalUsuarios = $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalCitas = $conexion->query("SELECT COUNT(*) FROM citas")->fetchColumn();
    $citasProgramadas = $conexion->query("SELECT COUNT(*) FROM citas WHERE estado = 'Programada'")->fetchColumn();
    $citasCanceladas = $conexion->query("SELECT COUNT(*) FROM citas WHERE estado = 'Cancelada'")->fetchColumn();
    $totalServicios = $conexion->query("SELECT COUNT(*) FROM servicios_contables")->fetchColumn();
    $serviciosCompletados = $conexion->query("SELECT COUNT(*) FROM servicios_contables WHERE estado = 'Completado'")->fetchColumn();
    $totalPagos = $conexion->query("SELECT COUNT(*) FROM pagos_facturacion")->fetchColumn();
    $pagosPendientes = $conexion->query("SELECT COUNT(*) FROM pagos_facturacion WHERE estado_pago = 'Pendiente'")->fetchColumn();
    $montoTotal = $conexion->query("SELECT COALESCE(SUM(monto), 0) FROM pagos_facturacion")->fetchColumn();

    sendResponse(200, "Reporte dashboard obtenido correctamente", [
        "usuarios" => [
            "total" => $totalUsuarios
        ],
        "citas" => [
            "total" => $totalCitas,
            "programadas" => $citasProgramadas,
            "canceladas" => $citasCanceladas
        ],
        "servicios" => [
            "total" => $totalServicios,
            "completados" => $serviciosCompletados
        ],
        "pagos" => [
            "total" => $totalPagos,
            "pendientes" => $pagosPendientes,
            "monto_total" => $montoTotal
        ]
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error al generar reporte", [
        "error" => $e->getMessage()
    ]);
}

?>
