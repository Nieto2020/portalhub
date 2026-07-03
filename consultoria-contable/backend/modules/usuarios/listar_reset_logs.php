<?php
/**
 * Listar historial de reseteos de contraseña
 * GET /backend/modules/usuarios/listar_reset_logs.php
 * Solo Administradores
 */
require_once "../../middleware/auth.php";
require_once "../../config/conexion.php";
require_once "../../utils/response.php";

checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, "Método no permitido");
}

try {
    $stmt = $conexion->prepare("
        SELECT 
            rl.id_log,
            rl.fecha_reseteo,
            admin.correo AS correo_admin,
            afec.correo AS correo_afectado,
            afec.id_usuario AS id_afectado,
            afec.numero_cliente AS numero_cliente_afectado,
            rol.nombre_rol AS rol_afectado
        FROM reset_logs rl
        INNER JOIN usuarios admin ON rl.id_admin = admin.id_usuario
        INNER JOIN usuarios afec ON rl.id_usuario_afectado = afec.id_usuario
        INNER JOIN cat_roles rol ON afec.id_rol = rol.id_rol
        ORDER BY rl.fecha_reseteo DESC
        LIMIT 50
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "OK", $logs);
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
