<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario_sesion = $_SESSION['id_usuario'];
$id_rol_sesion = $_SESSION['id_rol'];

try {
    $sql = "
        SELECT 
            d.id_documento,
            d.id_usuario_propietario,
            d.id_tipo_doc,
            d.ruta_archivo,
            d.nombre_original,
            d.version,
            d.validacion_cfdi,
            d.fecha_subida,
            td.nombre_tipo,
            u.correo as propietario_correo
        FROM documentos d
        JOIN cat_tipos_documentos td ON d.id_tipo_doc = td.id_tipo_doc
        JOIN usuarios u ON d.id_usuario_propietario = u.id_usuario
    ";

    if ($id_rol_sesion == 3) {
        $sql .= " WHERE d.id_usuario_propietario = :id_usuario";
        $params = [':id_usuario' => $id_usuario_sesion];
    } elseif ($id_rol_sesion == 2) {
        // Asesor: Clientes asignados
        $sql .= " WHERE d.id_usuario_propietario IN (
                    SELECT id_cliente FROM cliente_asesor 
                    WHERE id_asesor = :id_usuario AND estado = 'activo'
                  )";
        $params = [':id_usuario' => $id_usuario_sesion];
    } else {
        // Admin: Todos los documentos
        $params = [];
    }

    $sql .= " ORDER BY d.fecha_subida DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Documentos obtenidos correctamente", $documentos);

} catch (PDOException $e) {
    sendResponse(500, "Error al listar documentos", ["error" => $e->getMessage()]);
}