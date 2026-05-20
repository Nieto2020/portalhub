<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

// Admin puede ver todo, Asesor solo lo suyo
checkRole([ROL_ADMIN, ROL_ASESOR]);

$id_usuario_sesion = $_SESSION['id_usuario'];
$id_rol_sesion = $_SESSION['id_rol'];

try {
    $sql = "SELECT 
                ca.id_asignacion,
                ca.fecha_asignacion,
                u.id_usuario as id_cliente,
                u.correo as correo_cliente,
                u.numero_cliente,
                p.nombre_completo as nombre_cliente,
                p.telefono as telefono_cliente,
                ase.id_usuario as id_asesor,
                ap.nombre_completo as nombre_asesor
            FROM cliente_asesor ca
            JOIN usuarios u ON ca.id_cliente = u.id_usuario
            JOIN usuarios ase ON ca.id_asesor = ase.id_usuario
            LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario
            LEFT JOIN perfiles ap ON ase.id_usuario = ap.id_usuario
            WHERE ca.estado = 'activo'";

    $params = [];

    // Si es asesor, filtrar solo sus clientes
    if ($id_rol_sesion == ROL_ASESOR) {
        $sql .= " AND ca.id_asesor = ?";
        $params[] = $id_usuario_sesion;
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Cartera de clientes obtenida", $clientes);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>