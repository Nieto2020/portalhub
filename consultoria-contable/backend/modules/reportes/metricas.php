<?php
/**
 * metricas.php — Endpoint de métricas avanzadas para el panel de administración.
 * Solo accesible para administradores (rol 1).
 *
 * Devuelve datos agrupados en tres áreas estratégicas:
 *   1. Adopción y Uso del Portal
 *   2. Operaciones y Flujo de Trabajo
 *   3. Financieras y de Servicio
 */

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

// Solo el admin puede ver este panel
checkRole([1]); // ROL_ADMIN = 1

try {
    // ═══════════════════════════════════════════════════════════════
    // 1. MÉTRICAS DE ADOPCIÓN Y USO DEL PORTAL
    // ═══════════════════════════════════════════════════════════════

    // Total de clientes
    $totalClientes = (int) $conexion->query(
        "SELECT COUNT(*) FROM usuarios WHERE id_rol = 3"
    )->fetchColumn();

    // Clientes activos (estado = activo)
    $clientesActivos = (int) $conexion->query(
        "SELECT COUNT(*) FROM usuarios WHERE id_rol = 3 AND estado = 'activo'"
    )->fetchColumn();

    // Tasa de activación: clientes que ya tienen perfil configurado (nombre_completo no nulo)
    $clientesConPerfil = (int) $conexion->query(
        "SELECT COUNT(*) FROM usuarios u
         INNER JOIN perfiles p ON u.id_usuario = p.id_usuario
         WHERE u.id_rol = 3 AND p.nombre_completo IS NOT NULL AND p.nombre_completo != ''"
    )->fetchColumn();

    // Clientes que han interactuado (enviado mensajes o subido documentos)
    $clientesInteractuaron = (int) $conexion->query(
        "SELECT COUNT(DISTINCT u.id_usuario)
         FROM usuarios u
         WHERE u.id_rol = 3
           AND (EXISTS (SELECT 1 FROM mensajes m WHERE m.id_remitente = u.id_usuario)
                OR EXISTS (SELECT 1 FROM documentos d WHERE d.id_usuario_propietario = u.id_usuario))"
    )->fetchColumn();

    // DAU/MAU simulado: usuarios que han tenido actividad en los últimos 1 / 30 días
    // Usamos el perfil actualizado recientemente como proxy de actividad
    $dau = (int) $conexion->query(
        "SELECT COUNT(*) FROM perfiles p
         INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
         WHERE u.id_rol = 3 AND p.fecha_actualizacion >= DATE_SUB(NOW(), INTERVAL 1 DAY)"
    )->fetchColumn();

    $mau = (int) $conexion->query(
        "SELECT COUNT(*) FROM perfiles p
         INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
         WHERE u.id_rol = 3 AND p.fecha_actualizacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    )->fetchColumn();

    // Actividad por día de la semana (mensajes + documentos, últimos 30 días)
    $actividadSemanalStmt = $conexion->query(
        "SELECT WEEKDAY(fecha) AS dia_semana, COUNT(*) AS total
         FROM (
             SELECT DATE(fecha_envio) AS fecha FROM mensajes
             WHERE fecha_envio >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             UNION ALL
             SELECT DATE(fecha_subida) AS fecha FROM documentos
             WHERE fecha_subida >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         ) AS eventos
         GROUP BY WEEKDAY(fecha)
         ORDER BY dia_semana"
    );
    $actividadSemanal = array_fill(0, 7, 0);
    while ($row = $actividadSemanalStmt->fetch(PDO::FETCH_ASSOC)) {
        $actividadSemanal[(int) $row['dia_semana']] = (int) $row['total'];
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. MÉTRICAS DE OPERACIONES Y FLUJO DE TRABAJO
    // ═══════════════════════════════════════════════════════════════

    // Documentos pendientes: documentos que el cliente no ha subido (servicios sin documentos asociados)
    // Contamos servicios en proceso/activos como proxy de "documentos esperados"
    $serviciosPendientesDoc = (int) $conexion->query(
        "SELECT COUNT(*) FROM servicios_contables WHERE estado IN ('Pendiente', 'En proceso')"
    )->fetchColumn();

    // Total de documentos subidos (últimos 30 días)
    $documentosRecientes = (int) $conexion->query(
        "SELECT COUNT(*) FROM documentos WHERE fecha_subida >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    )->fetchColumn();

    // Tiempo medio de respuesta (TTR): promedio de horas entre un mensaje de cliente y la primera respuesta del equipo
    $ttrStmt = $conexion->query(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, m1.fecha_envio, m2.fecha_envio)) AS ttr_horas
         FROM mensajes m1
         INNER JOIN mensajes m2 ON m1.id_destinatario = m2.id_remitente
            AND m1.id_remitente = m2.id_destinatario
            AND m2.fecha_envio > m1.fecha_envio
         WHERE m1.id_remitente IN (SELECT id_usuario FROM usuarios WHERE id_rol = 3)
           AND m2.id_remitente IN (SELECT id_usuario FROM usuarios WHERE id_rol IN (1, 2))
           AND m1.fecha_envio >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    $ttrRow = $ttrStmt->fetch(PDO::FETCH_ASSOC);
    $ttrHoras = $ttrRow['ttr_horas'] ? round((float) $ttrRow['ttr_horas'], 1) : null;

    // Estado de cumplimiento fiscal (compliance): servicios por estado
    $compliancePendiente = (int) $conexion->query(
        "SELECT COUNT(*) FROM servicios_contables WHERE estado = 'Pendiente'"
    )->fetchColumn();
    $complianceEnProceso = (int) $conexion->query(
        "SELECT COUNT(*) FROM servicios_contables WHERE estado = 'En proceso'"
    )->fetchColumn();
    $complianceCompletado = (int) $conexion->query(
        "SELECT COUNT(*) FROM servicios_contables WHERE estado = 'Completado'"
    )->fetchColumn();

    // Carga de trabajo por contador (asesor)
    $cargaAsesoresStmt = $conexion->query(
        "SELECT u.id_usuario, u.correo, p.nombre_completo,
                COUNT(DISTINCT ca.id_cliente) AS clientes_asignados,
                COUNT(DISTINCT sc.id_servicio) AS servicios_activos
         FROM usuarios u
         LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario
         LEFT JOIN cliente_asesor ca ON u.id_usuario = ca.id_asesor AND ca.estado = 'activo'
         LEFT JOIN servicios_contables sc ON ca.id_cliente = sc.id_cliente
            AND sc.estado IN ('Pendiente', 'En proceso')
         WHERE u.id_rol = 2
         GROUP BY u.id_usuario, u.correo, p.nombre_completo
         ORDER BY clientes_asignados DESC"
    );
    $cargaAsesores = $cargaAsesoresStmt->fetchAll(PDO::FETCH_ASSOC);

    // Total de tickets abiertos (sin respuesta del equipo)
    $ticketsAbiertos = (int) $conexion->query(
        "SELECT COUNT(DISTINCT m1.id_remitente)
         FROM mensajes m1
         WHERE m1.id_remitente IN (SELECT id_usuario FROM usuarios WHERE id_rol = 3)
           AND m1.leida = 0
           AND NOT EXISTS (
               SELECT 1 FROM mensajes m2
               WHERE m2.id_remitente = m1.id_destinatario
                 AND m2.id_destinatario = m1.id_remitente
                 AND m2.fecha_envio > m1.fecha_envio
           )"
    )->fetchColumn();

    // ═══════════════════════════════════════════════════════════════
    // 3. MÉTRICAS FINANCIERAS Y DE SERVICIO
    // ═══════════════════════════════════════════════════════════════

    // Facturación y cobranza
    $facturasEmitidas = (int) $conexion->query(
        "SELECT COUNT(*) FROM pagos_facturacion WHERE estado_factura = 'Emitida'"
    )->fetchColumn();

    $facturasPendientes = (int) $conexion->query(
        "SELECT COUNT(*) FROM pagos_facturacion WHERE estado_factura = 'Pendiente'"
    )->fetchColumn();

    $pagosRecibidos = (int) $conexion->query(
        "SELECT COUNT(*) FROM pagos_facturacion WHERE estado_pago = 'Aprobado'"
    )->fetchColumn();

    $pagosVencidos = (int) $conexion->query(
        "SELECT COUNT(*) FROM pagos_facturacion WHERE estado_pago = 'Pendiente'"
    )->fetchColumn();

    // Monto total de pagos aprobados
    $montoAprobado = (float) $conexion->query(
        "SELECT COALESCE(SUM(monto), 0) FROM pagos_facturacion WHERE estado_pago = 'Aprobado'"
    )->fetchColumn();

    // Monto pendiente por cobrar
    $montoPendiente = (float) $conexion->query(
        "SELECT COALESCE(SUM(monto), 0) FROM pagos_facturacion WHERE estado_pago = 'Pendiente'"
    )->fetchColumn();

    // Índice de satisfacción (CSAT): promedio de calificaciones
    $csatStmt = $conexion->query(
        "SELECT AVG(puntuacion) AS promedio,
                COUNT(*) AS total_calificaciones,
                COUNT(CASE WHEN puntuacion >= 4 THEN 1 END) AS positivas
         FROM calificaciones"
    );
    $csatRow = $csatStmt->fetch(PDO::FETCH_ASSOC);
    $csatPromedio = $csatRow['promedio'] ? round((float) $csatRow['promedio'], 1) : null;
    $csatTotal = (int) $csatRow['total_calificaciones'];
    $csatPositivas = (int) $csatRow['positivas'];

    // Solicitudes de servicios adicionales (servicios creados en los últimos 30 días)
    $serviciosNuevos = (int) $conexion->query(
        "SELECT COUNT(*) FROM servicios_contables
         WHERE fecha_actualizacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    )->fetchColumn();

    // Distribución por tipo de servicio
    $serviciosPorTipoStmt = $conexion->query(
        "SELECT cs.nombre_servicio, COUNT(sc.id_servicio) AS total
         FROM cat_servicios cs
         LEFT JOIN servicios_contables sc ON cs.id_tipo_servicio = sc.id_tipo_servicio
         GROUP BY cs.id_tipo_servicio, cs.nombre_servicio
         ORDER BY total DESC"
    );
    $serviciosPorTipo = $serviciosPorTipoStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Construir respuesta ──
    sendResponse(200, "Métricas obtenidas correctamente", [
        "adopcion" => [
            "total_clientes"       => $totalClientes,
            "clientes_activos"     => $clientesActivos,
            "tasa_activacion"      => $totalClientes > 0
                ? round(($clientesConPerfil / $totalClientes) * 100, 1)
                : 0,
            "clientes_con_perfil"  => $clientesConPerfil,
            "clientes_interactuaron" => $clientesInteractuaron,
            "tasa_interaccion"     => $totalClientes > 0
                ? round(($clientesInteractuaron / $totalClientes) * 100, 1)
                : 0,
            "dau"                  => $dau,
            "mau"                  => $mau,
            "actividad_semanal"    => $actividadSemanal,
        ],
        "operaciones" => [
            "servicios_pendientes_doc" => $serviciosPendientesDoc,
            "documentos_recientes"     => $documentosRecientes,
            "ttr_horas"                => $ttrHoras,
            "ttr_formateado"           => formatTTR($ttrHoras),
            "compliance" => [
                "pendiente"   => $compliancePendiente,
                "en_proceso"  => $complianceEnProceso,
                "completado"  => $complianceCompletado,
                "total"       => $compliancePendiente + $complianceEnProceso + $complianceCompletado,
            ],
            "tickets_abiertos"         => $ticketsAbiertos,
            "carga_asesores"           => $cargaAsesores,
        ],
        "financieras" => [
            "facturacion" => [
                "emitidas"    => $facturasEmitidas,
                "pendientes"  => $facturasPendientes,
                "total"       => $facturasEmitidas + $facturasPendientes,
            ],
            "cobranza" => [
                "recibidos"   => $pagosRecibidos,
                "vencidos"    => $pagosVencidos,
                "monto_aprobado"  => $montoAprobado,
                "monto_pendiente" => $montoPendiente,
            ],
            "csat" => [
                "promedio"           => $csatPromedio,
                "total_calificaciones" => $csatTotal,
                "positivas"          => $csatPositivas,
                "tasa_satisfaccion"  => $csatTotal > 0
                    ? round(($csatPositivas / $csatTotal) * 100, 1)
                    : null,
            ],
            "servicios_adicionales" => [
                "nuevos_30_dias"  => $serviciosNuevos,
                "por_tipo"        => $serviciosPorTipo,
            ],
        ],
    ]);

} catch (PDOException $e) {
    sendResponse(500, "Error al generar métricas", [
        "error" => $e->getMessage()
    ]);
}

/**
 * Formatea el TTR en horas a un string legible.
 */
function formatTTR($horas) {
    if ($horas === null) return "Sin datos";
    if ($horas < 1) return "< 1 hora";
    if ($horas < 24) return round($horas, 1) . " horas";
    $dias = floor($horas / 24);
    $hrs = round($horas % 24, 1);
    return "$dias d, $hrs h";
}
