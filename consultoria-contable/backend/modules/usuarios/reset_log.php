<?php
/**
 * Registrar log de reseteo de contraseña
 * Solo accesible internamente desde reset_password_admin.php
 */

function registrarResetLog($conexion, $id_admin, $id_usuario_afectado) {
    try {
        $stmt = $conexion->prepare(
            "INSERT INTO reset_logs (id_admin, id_usuario_afectado) VALUES (?, ?)"
        );
        $stmt->execute([$id_admin, $id_usuario_afectado]);
    } catch (PDOException $e) {
        error_log("Error al registrar reset_log: " . $e->getMessage());
    }
}
