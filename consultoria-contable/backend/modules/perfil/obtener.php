<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

// Todos los usuarios autenticados pueden ver su propio perfil
checkAuth();

$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt = $conexion->prepare("SELECT u.correo, u.id_rol, r.nombre_rol, p.* 
                               FROM usuarios u 
                               JOIN cat_roles r ON u.id_rol = r.id_rol
                               LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario 
                               WHERE u.id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$perfil) {
        sendResponse(404, "Perfil no encontrado");
    }

    sendResponse(200, "Perfil obtenido", $perfil);
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>