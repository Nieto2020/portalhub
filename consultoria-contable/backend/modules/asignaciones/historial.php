<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

// El historial es vital para auditoría, solo Admin y Asesor (de sus propios clientes pasados/presentes)
checkRole([ROL_ADMIN, ROL_ASESOR]);

$id_cliente = $_GET['id_cliente'] ?? null;
$id_usuario_sesion = $_SESSION['id_usuario'];
$id_rol_sesion = $_SESSION['id_rol'];

if (!$id_cliente && $id_rol_sesion != ROL_ADMIN) {
    sendResponse(400, "Debe especificar un id_cliente");
}

try {
    $sql = "SELECT 
                ca.id_asignacion,
                ca.fecha_asignacion,
                ca.fecha_fin,
                ca.estado,
                ca.motivo_cambio,
                ase.correo as correo_asesor,
                ap.nombre_completo as nombre_asesor,
                adm.correo as correo_asignador
            FROM cliente_asesor ca
            JOIN usuarios ase ON ca.id_asesor = ase.id_usuario
            JOIN usuarios adm ON ca.id_asignador = adm.id_usuario
            LEFT JOIN perfiles ap ON ase.id_usuario = ap.id_usuario
            WHERE 1=1";
    
    $params = [];

    if ($id_cliente) {
        $sql .= " AND ca.id_cliente = ?";
        $params[] = $id_cliente;
    }

    // Si es asesor, solo puede ver historial de clientes que tiene o tuvo asignados
    if ($id_rol_sesion == ROL_ASESOR) {
        $sql .= " AND ca.id_cliente IN (SELECT id_cliente FROM cliente_asesor WHERE id_asesor = ?)";
        $params[] = $id_usuario_sesion;
    }

    $sql .= " ORDER BY ca.fecha_asignacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Historial de asignaciones obtenido", $historial);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>