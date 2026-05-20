<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario = $_SESSION['id_usuario'];

try {

    $sql = "
        SELECT 
            m.id_mensaje,
            m.id_remitente,
            m.id_destinatario,
            m.tipo,
            m.contenido_texto,
            m.ruta_archivo_adjunto,
            m.fecha_envio,

            CASE
                WHEN m.id_remitente = :id_usuario 
                    THEN ur.correo
                ELSE ud.correo
            END AS otro_usuario_correo

        FROM mensajes m

        LEFT JOIN usuarios ur 
            ON m.id_remitente = ur.id_usuario

        LEFT JOIN usuarios ud 
            ON m.id_destinatario = ud.id_usuario

        WHERE 
            m.id_remitente = :id_usuario
            OR
            m.id_destinatario = :id_usuario

        ORDER BY m.fecha_envio DESC
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

    $stmt->execute();

    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Mensajes obtenidos correctamente", $mensajes);

} catch (PDOException $e) {

    sendResponse(500, "Error en el servidor", [
        "error" => $e->getMessage()
    ]);

}

?>
