<?php
require_once "../../config/config.php";
session_start();
require_once "../../config/conexion.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

# Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

# Validar campos requeridos
if (!validateRequired($data, ['correo', 'password'])) {
    sendResponse(400, "Faltan campos requeridos");
}

$correo = trim($data['correo']);
$password = $data['password'];

try {
    $stmt = $conexion->prepare("SELECT id_usuario, id_rol, password_hash, estado, require_password_change FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        //retraso en caso de fallo
        sleep(1);
        sendResponse(401, "Credenciales incorrectas");
    }

    if ($usuario['estado'] !== 'activo') {
        sendResponse(403, "Usuario inactivo, contacte al administrador");
    }
    
    # Session Fixation
    session_regenerate_id(true);

    # Establecer sesión
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['id_rol'] = $usuario['id_rol'];
    $_SESSION['require_password_change'] = $usuario['require_password_change'];
    $_SESSION['ultima_actividad'] = time();

    # Generar CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    # Enviar respuesta exitosa con información del usuario
    sendResponse(200, "Inicio de sesión exitoso", [
        "id_usuario" => $usuario['id_usuario'],
        "id_rol" => $usuario['id_rol'],
        "require_password_change" => (bool)$usuario['require_password_change']
    ]);
} catch (PDOException $e) {
    error_log("Error en login.php: " . $e->getMessage());
    sendResponse(500, "Ocurrió un error interno en el servidor");
}
?>