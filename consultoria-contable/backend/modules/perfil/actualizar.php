<?php
/**
 * actualizar.php — Actualiza el perfil del usuario con restricciones por rol.
 *
 * Reglas de escritura:
 *   Admin (rol 1):  Puede editar cualquier campo de cualquier usuario.
 *   Asesor (rol 2): Edita sus campos profesionales. Solo ve datos fiscales de clientes, no los edita.
 *   Cliente (rol 3): Edita nombre_completo y telefono. RFC/razón_social requieren validación admin.
 *
 * Body JSON: { campos... }
 * Parámetro opcional: ?id_usuario=X (solo admin)
 */

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido.");
}

$data = json_decode(file_get_contents("php://input"), true);
$id_sesion = $_SESSION['id_usuario'];
$id_rol    = (int) $_SESSION['id_rol'];

// Admin puede editar a otro usuario
$target_id = ($id_rol === ROL_ADMIN && !empty($data['id_usuario']))
    ? (int) $data['id_usuario']
    : $id_sesion;

if ($target_id !== $id_sesion && $id_rol !== ROL_ADMIN) {
    sendResponse(403, "No tienes permiso para editar este perfil.");
}

if (!validateRequired($data, ['nombre_completo'])) {
    sendResponse(400, "El nombre completo es obligatorio.");
}

try {
    // Determinar campos permitidos según el rol del editor
    $camposPermitidos = obtenerCamposPermitidos($id_rol, $target_id, $data);

    // Construir SQL dinámico
    $cols = array_keys($camposPermitidos);
    $placeholders = array_fill(0, count($cols), "?");
    $updates = array_map(function ($col) { return "$col = VALUES($col)"; }, $cols);

    $sql = "INSERT INTO perfiles (id_usuario, " . implode(", ", $cols) . ") 
            VALUES (?, " . implode(", ", $placeholders) . ") 
            ON DUPLICATE KEY UPDATE " . implode(", ", $updates);

    $stmt = $conexion->prepare($sql);
    $params = array_values($camposPermitidos);
    array_unshift($params, $target_id);

    $stmt->execute($params);

    sendResponse(200, "Perfil actualizado exitosamente.");

} catch (PDOException $e) {
    sendResponse(500, "Error al guardar el perfil: " . $e->getMessage());
}

/**
 * Devuelve solo los campos que el rol actual puede modificar.
 */
function obtenerCamposPermitidos(int $rol, int $target_id, array $data): array {
    // Campos base que todos pueden editar de sí mismos
    $campos = [
        'nombre_completo' => $data['nombre_completo'] ?? null,
        'telefono'        => $data['telefono'] ?? null,
    ];

    if ($rol === ROL_ADMIN) {
        // Admin: acceso total a todos los campos de cualquier usuario
        // Asesor
        $campos['especialidad']        = $data['especialidad'] ?? null;
        $campos['biografia']           = $data['biografia'] ?? null;
        $campos['cedula_profesional']  = $data['cedula_profesional'] ?? null;
        $campos['correo_corporativo']  = $data['correo_corporativo'] ?? null;
        $campos['extension']           = $data['extension'] ?? null;
        $campos['disponibilidad']      = $data['disponibilidad'] ?? null;
        // Cliente
        $campos['rfc']                 = $data['rfc'] ?? null;
        $campos['razon_social']        = $data['razon_social'] ?? null;
        $campos['direccion_fiscal']    = $data['direccion_fiscal'] ?? null;
        $campos['regimen_fiscal']      = $data['regimen_fiscal'] ?? null;
        $campos['representante_legal'] = $data['representante_legal'] ?? null;
        $campos['direccion_comercial'] = $data['direccion_comercial'] ?? null;
        $campos['paquete_contratado']  = $data['paquete_contratado'] ?? null;
        $campos['estatus_suscripcion'] = $data['estatus_suscripcion'] ?? null;
        $campos['constancia_fiscal']   = $data['constancia_fiscal'] ?? null;
        // Admin
        $campos['consultoria_nombre']    = $data['consultoria_nombre'] ?? null;
        $campos['consultoria_direccion'] = $data['consultoria_direccion'] ?? null;
        $campos['consultoria_telefono']  = $data['consultoria_telefono'] ?? null;
        $campos['consultoria_correo']    = $data['consultoria_correo'] ?? null;

        return $campos;
    }

    if ($rol === ROL_ASESOR) {
        $campos['especialidad']        = $data['especialidad'] ?? null;
        $campos['biografia']           = $data['biografia'] ?? null;
        $campos['cedula_profesional']  = $data['cedula_profesional'] ?? null;
        $campos['correo_corporativo']  = $data['correo_corporativo'] ?? null;
        $campos['extension']           = $data['extension'] ?? null;
        // disponibilidad solo admin
        return $campos;
    }

    if ($rol === ROL_CLIENTE) {
        // Cliente edita sus datos de contacto. RFC y razón social requieren admin.
        $campos['direccion_comercial'] = $data['direccion_comercial'] ?? null;
        return $campos;
    }

    return $campos;
}
