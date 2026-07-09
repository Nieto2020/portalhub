<?php
/**
 * estadisticas.php - Estadísticas agregadas de calificaciones por asesor (solo admin)
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkRole([ROL_ADMIN]);

try {
    $stmt = $conexion->prepare("
        SELECT 
            u.id_usuario,
            u.correo,
            COALESCE(perf.nombre_completo, u.correo) AS nombre,
            COUNT(c.id_calificacion) AS total_calificaciones,
            ROUND(AVG(c.puntuacion), 1) AS promedio,
            MAX(c.fecha) AS ultima_calificacion
        FROM usuarios u
        JOIN cliente_asesor ca ON ca.id_asesor = u.id_usuario AND ca.estado = 'activo'
        LEFT JOIN perfiles perf ON perf.id_usuario = u.id_usuario
        LEFT JOIN calificaciones c ON c.id_asesor = u.id_usuario
        WHERE u.id_rol = ?
        GROUP BY u.id_usuario, u.correo, perf.nombre_completo
        HAVING COUNT(c.id_calificacion) > 0
        ORDER BY promedio DESC
    ");
    $stmt->execute([ROL_ASESOR]);
    $estadisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "OK", $estadisticas);
} catch (PDOException $e) {
    sendResponse(500, "Error: " . $e->getMessage());
}
