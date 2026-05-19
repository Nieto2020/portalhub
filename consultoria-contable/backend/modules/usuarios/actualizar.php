<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['id_usuario', 'correo', 'id_rol', 'estado'])) {
    sendResponse(400, "Faltan campos requeridos");
}

$id_usuario = $data['id_usuario'];
$correo = $data['correo'];
$id_rol = $data['id_rol'];
$estado = $data['estado'];
$numero_cliente = isset($data['numero_cliente']) ? $data['numero_cliente'] : null;

# contraseña temporal
$password_sql = "";
$params = [$correo, $id_rol, $estado, $numero_cliente];

if (!empty($data['temp_password'])) {
    $password_hash = password_hash($data['temp_password'], PASSWORD_BCRYPT);
    $password_sql = ", password_hash = ?, require_password_change = 1";
    $params[] = $password_hash;
}

$params[] = $id_usuario;

try {
    $stmt = $conexion->prepare("UPDATE usuarios SET correo = ?, id_rol = ?, estado = ?, numero_cliente = ? $password_sql WHERE id_usuario = ?");
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        sendResponse(200, "Usuario actualizado exitosamente");
    } else {
        sendResponse(404, "Usuario no encontrado o sin cambios");
    }
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>