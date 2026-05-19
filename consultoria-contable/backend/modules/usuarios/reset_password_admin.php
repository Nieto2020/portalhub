<?php
require_once "../../middleware/auth.php";
require_once "../../config/conexion.php";
require_once "../../utils/validator.php";

# Solo Administradores pueden resetear contraseñas de otros
checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['id_usuario'])) {
    sendResponse(400, "Faltan campos requeridos");
}

# Función para generar una contraseña aleatoria segura de 10 caracteres
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

$id_usuario = $data['id_usuario'];
$temp_password = generateRandomPassword();
$password_hash = password_hash($temp_password, PASSWORD_BCRYPT);

try {
    $stmt = $conexion->prepare("UPDATE usuarios SET password_hash = ?, require_password_change = 1 WHERE id_usuario = ?");
    $stmt->execute([$password_hash, $id_usuario]);

    if ($stmt->rowCount() > 0) {
        sendResponse(200, "Contraseña restablecida correctamente", [
            "temporary_password" => $temp_password
        ]);
    } else {
        sendResponse(404, "Usuario no encontrado");
    }
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>