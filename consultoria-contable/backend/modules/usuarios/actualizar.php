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

if (!validateEmail($correo)) {
    sendResponse(400, "Formato de correo inválido");
}

$estados_validos = ['activo', 'inactivo'];

if (!in_array($estado, $estados_validos)) {
    sendResponse(400, "Estado no válido");
}

try {
    // Validar si el correo ya existe en otro usuario
    $checkStmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?");
    $checkStmt->execute([$correo, $id_usuario]);
    if ($checkStmt->fetch()) {
        sendResponse(400, "El correo ya está registrado por otro usuario");
    }

    // No repetir N cliente.
    if ($numero_cliente) {
        $checkCliStmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE numero_cliente = ? AND id_usuario != ?");
        $checkCliStmt->execute([$numero_cliente, $id_usuario]);
        if ($checkCliStmt->fetch()) {
            sendResponse(400, "El número de cliente ya está registrado por otro usuario");
        }
    }

    # contraseña temporal
    $password_sql = "";
    $params = [$correo, $id_rol, $estado, $numero_cliente];

    if (!empty($data['temp_password'])) {
        $password_hash = password_hash($data['temp_password'], PASSWORD_BCRYPT);
        $password_sql = ", password_hash = ?, require_password_change = 1";
        $params[] = $password_hash;
    }

    $params[] = $id_usuario;

    $stmt = $conexion->prepare("UPDATE usuarios SET correo = ?, id_rol = ?, estado = ?, numero_cliente = ? $password_sql WHERE id_usuario = ?");
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        sendResponse(200, "Usuario actualizado exitosamente");
    } else {
        sendResponse(404, "Usuario no encontrado o sin cambios");
    }
} catch (PDOException $e) {
    error_log("Error en actualizar.php: " . $e->getMessage());
    sendResponse(500, "Ocurrió un error interno al actualizar el usuario");
}
?>
