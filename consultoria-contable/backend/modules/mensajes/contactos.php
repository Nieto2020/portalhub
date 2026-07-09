<?php
/**
 * contactos.php - Lista de usuarios con los que el usuario actual PUEDE conversar
 *
 * Reglas:
 *   Admin      → todos los usuarios
 *   Asesor     → admins, otros asesores, y SOLO sus clientes asignados activos
 *   Cliente    → SOLO su asesor asignado activo
 */
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, "Método no permitido");
}

$id_usuario = $_SESSION['id_usuario'];
$id_rol     = $_SESSION['id_rol'];

try {
    if ($id_rol == ROL_ADMIN) {
        // Admin ve todos los usuarios excepto a sí mismo
        $sql = "
            SELECT u.id_usuario, u.correo, u.numero_cliente, r.nombre_rol
            FROM usuarios u
            JOIN cat_roles r ON u.id_rol = r.id_rol
            WHERE u.id_usuario != ?
            ORDER BY r.id_rol, u.correo
        ";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_usuario]);

    } elseif ($id_rol == ROL_ASESOR) {
        // Asesor: admins + otros asesores + sus clientes asignados activos
        $sql = "
            SELECT u.id_usuario, u.correo, u.numero_cliente, r.nombre_rol
            FROM usuarios u
            JOIN cat_roles r ON u.id_rol = r.id_rol
            WHERE u.id_usuario != ?
              AND (
                u.id_rol IN (?, ?)   -- Admin y otros asesores
                OR (
                  u.id_rol = ?       -- Clientes
                  AND EXISTS (
                    SELECT 1 FROM cliente_asesor ca
                    WHERE ca.id_cliente = u.id_usuario
                      AND ca.id_asesor = ?
                      AND ca.estado = 'activo'
                  )
                )
              )
            ORDER BY r.id_rol, u.correo
        ";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_usuario, ROL_ADMIN, ROL_ASESOR, ROL_CLIENTE, $id_usuario]);

    } elseif ($id_rol == ROL_CLIENTE) {
        // Cliente: SOLO su asesor asignado activo
        $sql = "
            SELECT u.id_usuario, u.correo, u.numero_cliente, r.nombre_rol
            FROM usuarios u
            JOIN cat_roles r ON u.id_rol = r.id_rol
            WHERE u.id_rol = ?
              AND EXISTS (
                SELECT 1 FROM cliente_asesor ca
                WHERE ca.id_cliente = ?
                  AND ca.id_asesor = u.id_usuario
                  AND ca.estado = 'activo'
              )
            LIMIT 1
        ";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([ROL_ASESOR, $id_usuario]);

    } else {
        sendResponse(403, "Rol no reconocido");
    }

    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendResponse(200, "Contactos obtenidos", $contactos);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
