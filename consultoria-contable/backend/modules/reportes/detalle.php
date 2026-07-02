<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

if (!isset($_GET['id'])) {
    sendResponse(400, "ID de reporte requerido");
}

$id_reporte = intval($_GET['id']);

try {
    $sql = "
        SELECT 
            r.id_reporte,
            r.id_asesor,
            r.titulo,
            r.descripcion,
            r.contenido,
            r.tipo,
            r.estado,
            r.fecha_creacion,
            r.fecha_actualizacion,
            u.correo  AS correo_asesor,
            p.nombre_completo AS nombre_asesor,
            p.especialidad
        FROM reportes r
        JOIN usuarios u  ON r.id_asesor = u.id_usuario
        LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario
        WHERE r.id_reporte = ?
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_reporte]);
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reporte) {
        sendResponse(404, "Reporte no encontrado");
    }

    // Asesor solo ve sus propios reportes
    if ($rol == ROL_ASESOR && $reporte['id_asesor'] != $id_usuario) {
        sendResponse(403, "No tienes permiso para ver este reporte");
    }

    sendResponse(200, "Reporte obtenido correctamente", $reporte);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}

?>
