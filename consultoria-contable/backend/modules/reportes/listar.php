<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

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
            p.nombre_completo AS nombre_asesor
        FROM reportes r
        JOIN usuarios u  ON r.id_asesor = u.id_usuario
        LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario
    ";

    $params = [];
    $where = [];

    if ($rol == ROL_ASESOR) {
        $where[] = "r.id_asesor = ?";
        $params[] = $id_usuario;
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " ORDER BY r.fecha_creacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Reportes obtenidos correctamente", $reportes);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}

?>
