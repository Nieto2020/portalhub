<?php
/**
 * obtener.php — Obtiene el perfil del usuario autenticado o de otro usuario.
 *
 * Reglas de visibilidad por rol:
 *   Admin (rol 1): Ve todos los campos de cualquier usuario.
 *   Asesor (rol 2): Ve su propio perfil completo. De clientes asignados ve datos fiscales
 *                   pero NO edita campos sensibles (RFC, razón social requieren admin).
 *   Cliente (rol 3): Solo ve su propio perfil.
 *
 * Parámetros GET opcionales:
 *   ?id_usuario=X — Solo admin y asesor (para ver clientes asignados).
 */

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

$id_usuario_sesion = $_SESSION['id_usuario'];
$id_rol_sesion     = (int) $_SESSION['id_rol'];

// Determinar a quién se consulta
$target_id = isset($_GET['id_usuario']) ? (int) $_GET['id_usuario'] : $id_usuario_sesion;

// ── Validar permisos de lectura ──
if ($target_id !== $id_usuario_sesion) {
    if ($id_rol_sesion === ROL_CLIENTE) {
        sendResponse(403, "No tienes permiso para ver el perfil de otro usuario.");
    }

    if ($id_rol_sesion === ROL_ASESOR) {
        // Asesor solo puede ver clientes que tiene asignados
        $stmt = $conexion->prepare(
            "SELECT COUNT(*) FROM cliente_asesor 
             WHERE id_asesor = ? AND id_cliente = ? AND estado = 'activo'"
        );
        $stmt->execute([$id_usuario_sesion, $target_id]);
        if ((int) $stmt->fetchColumn() === 0) {
            sendResponse(403, "No tienes asignado a este cliente.");
        }
    }
    // Admin no tiene restricciones
}

try {
    // Obtener datos del usuario objetivo con conteo de clientes asignados (para asesores)
    $stmt = $conexion->prepare(
        "SELECT u.id_usuario, u.correo, u.id_rol, u.numero_cliente, u.estado AS estado_usuario,
                r.nombre_rol, p.*,
                (SELECT COUNT(*) FROM cliente_asesor 
                 WHERE id_asesor = u.id_usuario AND estado = 'activo') AS total_clientes_asignados
         FROM usuarios u 
         JOIN cat_roles r ON u.id_rol = r.id_rol
         LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario 
         WHERE u.id_usuario = ?"
    );
    $stmt->execute([$target_id]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$perfil) {
        sendResponse(404, "Perfil no encontrado.");
    }

    $target_rol = (int) $perfil['id_rol'];

    // ── Filtrar campos según quién consulta ──
    $response = filtrarCamposLectura($perfil, $id_rol_sesion, $target_rol, $target_id === $id_usuario_sesion);

    sendResponse(200, "Perfil obtenido.", $response);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}

/**
 * Filtra los campos del perfil según las reglas de visibilidad por rol.
 */
function filtrarCamposLectura(array $perfil, int $rol_lector, int $rol_objetivo, bool $esPropio): array {
    // Campos base (todos los roles pueden ver)
    $base = [
        'id_usuario'      => $perfil['id_usuario'],
        'correo'          => $perfil['correo'],
        'id_rol'          => $perfil['id_rol'],
        'nombre_rol'      => $perfil['nombre_rol'],
        'numero_cliente'  => $perfil['numero_cliente'],
        'estado_usuario'  => $perfil['estado_usuario'],
        'nombre_completo' => $perfil['nombre_completo'],
        'telefono'        => $perfil['telefono'],
        'foto_perfil'     => $perfil['foto_perfil'],
        'total_clientes_asignados' => $perfil['total_clientes_asignados'] ?? 0,
        'fecha_actualizacion' => $perfil['fecha_actualizacion'],
    ];

    // Admin ve TODO
    if ($rol_lector === ROL_ADMIN) {
        return array_merge($base, extraerCamposEspecificos($perfil, $rol_objetivo, true));
    }

    // Si es el propio usuario, ve todos sus campos
    if ($esPropio) {
        return array_merge($base, extraerCamposEspecificos($perfil, $rol_objetivo, true));
    }

    // Asesor viendo perfil de un cliente asignado
    if ($rol_lector === ROL_ASESOR && $rol_objetivo === ROL_CLIENTE) {
        $cliente = [
            'rfc'                  => $perfil['rfc'],
            'razon_social'         => $perfil['razon_social'],
            'direccion_fiscal'     => $perfil['direccion_fiscal'],
            'regimen_fiscal'       => $perfil['regimen_fiscal'],
            'representante_legal'  => $perfil['representante_legal'],
            'direccion_comercial'  => $perfil['direccion_comercial'],
            'paquete_contratado'   => $perfil['paquete_contratado'],
            'estatus_suscripcion'  => $perfil['estatus_suscripcion'],
        ];
        return array_merge($base, $cliente);
    }

    // Asesor viendo a otro asesor — solo datos de contacto
    if ($rol_lector === ROL_ASESOR && $rol_objetivo === ROL_ASESOR) {
        return array_merge($base, [
            'especialidad'       => $perfil['especialidad'],
            'correo_corporativo' => $perfil['correo_corporativo'],
            'extension'          => $perfil['extension'],
        ]);
    }

    return $base;
}

/**
 * Extrae campos específicos según el rol del perfil.
 */
function extraerCamposEspecificos(array $p, int $rol, bool $incluirAdmin): array {
    $campos = [];

    // Asesor
    $campos['especialidad']        = $p['especialidad'] ?? null;
    $campos['biografia']           = $p['biografia'] ?? null;
    $campos['cedula_profesional']  = $p['cedula_profesional'] ?? null;
    $campos['correo_corporativo']  = $p['correo_corporativo'] ?? null;
    $campos['extension']           = $p['extension'] ?? null;
    $campos['disponibilidad']      = $p['disponibilidad'] ?? null;

    // Cliente
    $campos['rfc']                 = $p['rfc'] ?? null;
    $campos['razon_social']        = $p['razon_social'] ?? null;
    $campos['direccion_fiscal']    = $p['direccion_fiscal'] ?? null;
    $campos['regimen_fiscal']      = $p['regimen_fiscal'] ?? null;
    $campos['representante_legal'] = $p['representante_legal'] ?? null;
    $campos['direccion_comercial'] = $p['direccion_comercial'] ?? null;
    $campos['paquete_contratado']  = $p['paquete_contratado'] ?? null;
    $campos['estatus_suscripcion'] = $p['estatus_suscripcion'] ?? null;
    $campos['constancia_fiscal']   = $p['constancia_fiscal'] ?? null;

    // Admin (consultoría)
    if ($incluirAdmin) {
        $campos['consultoria_nombre']    = $p['consultoria_nombre'] ?? null;
        $campos['consultoria_direccion'] = $p['consultoria_direccion'] ?? null;
        $campos['consultoria_telefono']  = $p['consultoria_telefono'] ?? null;
        $campos['consultoria_correo']    = $p['consultoria_correo'] ?? null;
    }

    return $campos;
}
