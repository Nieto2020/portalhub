<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkRole([ROL_ADMIN]);

try {
    // ── Asesores ──
    $stmt = $conexion->prepare("
        SELECT u.id_usuario, u.correo, u.estado,
               COALESCE(p.nombre_completo, u.correo) as nombre
        FROM usuarios u
        LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario
        WHERE u.id_rol = ?
        ORDER BY p.nombre_completo ASC, u.correo ASC
    ");
    $stmt->execute([ROL_ASESOR]);
    $asesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Clientes (con su asignación activa actual) ──
    $stmt = $conexion->prepare("
        SELECT u.id_usuario, u.correo, u.numero_cliente, u.estado,
               COALESCE(p.nombre_completo, u.correo) as nombre,
               ca.id_asesor as asesor_actual_id,
               ca_ase.correo as asesor_actual_correo
        FROM usuarios u
        LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario
        LEFT JOIN cliente_asesor ca ON u.id_usuario = ca.id_cliente AND ca.estado = 'activo'
        LEFT JOIN usuarios ca_ase ON ca.id_asesor = ca_ase.id_usuario
        WHERE u.id_rol = ?
        ORDER BY COALESCE(p.nombre_completo, u.correo) ASC
    ");
    $stmt->execute([ROL_CLIENTE]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_asesores = count($asesores);
    $clientes_sin_asesor = 0;
    foreach ($clientes as $c) {
        if (is_null($c['asesor_actual_id'])) $clientes_sin_asesor++;
    }

    sendResponse(200, "Datos obtenidos", [
        "asesores"          => $asesores,
        "clientes"          => $clientes,
        "total_asesores"    => $total_asesores,
        "clientes_sin_asesor" => $clientes_sin_asesor
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
