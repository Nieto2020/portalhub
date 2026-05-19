<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

// Solo Admin y Asesor pueden listar usuarios
checkRole([ROL_ADMIN, ROL_ASESOR]);

try {
    $stmt = $conexion->prepare("SELECT u.id_usuario, u.correo, u.numero_cliente, u.estado, u.fecha_registro, r.nombre_rol 
                                FROM usuarios u 
                                JOIN cat_roles r ON u.id_rol = r.id_rol");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Usuarios obtenidos", $usuarios);
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>