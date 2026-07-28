/**
 * metricasView.js — Carga y renderiza el panel de métricas del admin.
 *
 * Consume: backend/modules/reportes/metricas.php
 * Requiere autenticación de administrador.
 */
(function () {
    'use strict';

    const API_METRICAS = '../../../backend/modules/reportes/metricas.php';

    // ── Inicialización ──
    async function init() {
        showLoading(true);
        hideError();

        try {
            let data;
            // ▼ MOCKUP BRANCH — eliminar en producción, conservar solo el else
            if (window.MOCK && window.MOCK_DATA) {
                data = window.MOCK_DATA;
            } else {
            // ▲ END MOCKUP BRANCH
                const res = await fetch(API_METRICAS, { credentials: 'include' });
                const json = await res.json();

                if (!res.ok || !json.data) {
                    throw new Error(json.message || 'Error al cargar métricas');
                }
                data = json.data;
            // ▼ MOCKUP CLOSING BRACE — eliminar en producción
            }
            // ▲ END MOCKUP CLOSING

            renderAll(data);
        } catch (e) {
            showError(e.message);
        } finally {
            showLoading(false);
        }
    }

    // ── Renderizar todo ──
    function renderAll(data) {
        renderAdopcion(data.adopcion);
        renderOperaciones(data.operaciones);
        renderFinancieras(data.financieras);
    }

    // ═══════════════════════════════════════════════════════════════
    // 1. ADOPCIÓN Y USO
    // ═══════════════════════════════════════════════════════════════
    function renderAdopcion(d) {
        if (!d) return;

        setText('kpiTotalClientes', formatNum(d.total_clientes));
        setText('kpiClientesActivos', formatNum(d.clientes_activos));
        setText('kpiTasaActivacion', d.tasa_activacion + '%');
        setBar('barActivacion', d.tasa_activacion, barColor(d.tasa_activacion));

        setText('kpiDAU', formatNum(d.dau));
        setText('kpiMAU', formatNum(d.mau));
        setText('kpiInteraccion', d.tasa_interaccion + '%');
        setBar('barInteraccion', d.tasa_interaccion, barColor(d.tasa_interaccion));

        setText('kpiDetalleActivacion',
            d.clientes_con_perfil + ' de ' + d.total_clientes + ' clientes');
        setText('kpiDetalleDAU',
            (d.mau > 0 ? Math.round((d.dau / d.mau) * 100) : 0) + '% ratio DAU/MAU');
        setText('kpiDetalleInteraccion',
            d.clientes_interactuaron + ' de ' + d.total_clientes + ' clientes');

        // Gráficas
        renderDonutClientes(d.total_clientes, d.clientes_activos);
        renderBarrasSemanales(d.actividad_semanal || []);
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. OPERACIONES Y FLUJO DE TRABAJO
    // ═══════════════════════════════════════════════════════════════
    function renderOperaciones(d) {
        if (!d) return;

        // Documentos
        setText('kpiServiciosPendientes', formatNum(d.servicios_pendientes_doc));
        setText('kpiDocumentosRecientes', formatNum(d.documentos_recientes));

        // TTR
        setText('kpiTTR', d.ttr_formateado || 'Sin datos');
        setBadge('kpiTTRBadge', ttrBadge(d.ttr_horas));

        // Tickets abiertos
        setText('kpiTicketsAbiertos', formatNum(d.tickets_abiertos));
        setBadge('kpiTicketsBadge',
            d.tickets_abiertos === 0 ? 'positive' : (d.tickets_abiertos > 5 ? 'danger' : 'warning'));

        // Compliance
        var comp = d.compliance || {};
        setText('compPendiente', formatNum(comp.pendiente));
        setText('compEnProceso', formatNum(comp.en_proceso));
        setText('compCompletado', formatNum(comp.completado));
        setText('compTotal', formatNum(comp.total));

        var totalComp = comp.total || 1;
        setBar('barCompCompletado', (comp.completado / totalComp) * 100, 'green');
        setBar('barCompEnProceso', (comp.en_proceso / totalComp) * 100, 'amber');
        setBar('barCompPendiente', (comp.pendiente / totalComp) * 100, 'neutral');

        // Carga de asesores
        renderWorkload(d.carga_asesores || []);
    }

    // ═══════════════════════════════════════════════════════════════
    // 3. FINANCIERAS Y DE SERVICIO
    // ═══════════════════════════════════════════════════════════════
    function renderFinancieras(d) {
        if (!d) return;

        var fact = d.facturacion || {};
        var cobr = d.cobranza || {};
        var csat = d.csat || {};
        var serv = d.servicios_adicionales || {};

        // Facturación
        setText('kpiFacturasEmitidas', formatNum(fact.emitidas));
        setText('kpiFacturasPendientes', formatNum(fact.pendientes));
        setBar('barFacturacion',
            fact.total > 0 ? Math.round((fact.emitidas / fact.total) * 100) : 0, 'green');

        // Cobranza
        setText('kpiPagosRecibidos', formatNum(cobr.recibidos));
        setText('kpiPagosVencidos', formatNum(cobr.vencidos));
        setText('kpiMontoAprobado', formatCurrency(cobr.monto_aprobado));
        setText('kpiMontoPendiente', formatCurrency(cobr.monto_pendiente));
        setBadge('kpiMontoBadge',
            cobr.monto_pendiente > 0 ? 'warning' : 'positive');

        // CSAT
        var csatVal = csat.promedio;
        setText('kpiCSAT', csatVal !== null ? csatVal.toFixed(1) : '—');
        setText('kpiCSATLabel', csatLabel(csatVal));
        setText('kpiCSATDetail',
            csat.total_calificaciones > 0
                ? csat.tasa_satisfaccion + '% positivas (' + csat.positivas + '/' + csat.total_calificaciones + ')'
                : 'Sin calificaciones');

        var csatCircle = document.getElementById('csatCircle');
        if (csatCircle) {
            csatCircle.className = 'csat-circle ' + csatCircleClass(csatVal);
        }

        // Servicios adicionales
        setText('kpiServiciosNuevos', formatNum(serv.nuevos_30_dias));
        renderServiceTypes(serv.por_tipo || []);
    }

    // ── Carga de trabajo ──
    function renderWorkload(asesores) {
        var tbody = document.getElementById('workloadBody');
        if (!tbody) return;

        if (!asesores || asesores.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px;">No hay asesores registrados</td></tr>';
            return;
        }

        var maxClientes = Math.max.apply(null, asesores.map(function (a) { return parseInt(a.clientes_asignados) || 0; }));
        if (maxClientes === 0) maxClientes = 1;

        tbody.innerHTML = asesores.map(function (a) {
            var nombre = a.nombre_completo || a.correo || 'Sin nombre';
            var clientes = parseInt(a.clientes_asignados) || 0;
            var servicios = parseInt(a.servicios_activos) || 0;
            var pct = Math.round((clientes / maxClientes) * 100);
            var level = pct >= 80 ? 'high' : (pct >= 40 ? 'medium' : 'low');

            return '<tr>' +
                '<td>' + escapeHtml(nombre) + '</td>' +
                '<td>' + clientes + '</td>' +
                '<td>' + servicios + '</td>' +
                '<td>' +
                    '<div class="wl-bar-wrap">' +
                        '<div class="wl-bar"><div class="wl-bar-fill ' + level + '" style="width:' + pct + '%"></div></div>' +
                        '<span style="font-size:0.75rem;color:var(--text-muted)">' + pct + '%</span>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        }).join('');
    }

    // ── Servicios por tipo ──
    function renderServiceTypes(tipos) {
        var container = document.getElementById('serviceTypeList');
        if (!container) return;

        if (!tipos || tipos.length === 0) {
            container.innerHTML = '<div style="color:var(--text-muted);text-align:center;padding:16px;">No hay servicios registrados</div>';
            return;
        }

        container.innerHTML = tipos.map(function (t) {
            return '<div class="service-type-item">' +
                '<span class="st-name">' + escapeHtml(t.nombre_servicio) + '</span>' +
                '<span class="st-count">' + (parseInt(t.total) || 0) + '</span>' +
            '</div>';
        }).join('');
    }

    // ── Donut: Proporción clientes totales vs activos ──
    function renderDonutClientes(total, activos) {
        var canvas = document.getElementById('donutClientes');
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        var w = canvas.width, h = canvas.height;
        var cx = w / 2, cy = h / 2, r = 85, ancho = 22;
        var inactivos = Math.max(0, total - activos);

        ctx.clearRect(0, 0, w, h);

        if (total === 0) {
            ctx.beginPath();
            ctx.arc(cx, cy, r - ancho / 2, 0, Math.PI * 2);
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = ancho;
            ctx.stroke();
            setText('donutLegend',
                'Sin datos');
            return;
        }

        var pctActivos = activos / total;
        var startAngle = -Math.PI / 2;

        // Arco activos
        ctx.beginPath();
        ctx.arc(cx, cy, r - ancho / 2, startAngle, startAngle + pctActivos * Math.PI * 2);
        ctx.strokeStyle = '#10b981';
        ctx.lineWidth = ancho;
        ctx.lineCap = 'round';
        ctx.stroke();

        // Arco inactivos
        ctx.beginPath();
        ctx.arc(cx, cy, r - ancho / 2, startAngle + pctActivos * Math.PI * 2, startAngle + Math.PI * 2);
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = ancho;
        ctx.lineCap = 'butt';
        ctx.stroke();

        // Texto centro
        ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text').trim() || '#0f172a';
        ctx.font = 'bold 22px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(Math.round(pctActivos * 100) + '%', cx, cy - 6);
        ctx.font = '11px sans-serif';
        ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim() || '#64748b';
        ctx.fillText('activos', cx, cy + 16);

        // Leyenda
        var leg = document.getElementById('donutLegend');
        if (leg) {
            leg.innerHTML =
                '<div class="leg-item"><span class="leg-dot" style="background:#10b981"></span> Activos <strong>' + activos + '</strong></div>' +
                '<div class="leg-item"><span class="leg-dot" style="background:#e2e8f0"></span> Inactivos <strong>' + inactivos + '</strong></div>';
        }
    }

    // ── Barras: Actividad por día de la semana ──
    function renderBarrasSemanales(datos) {
        var canvas = document.getElementById('barSemanal');
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        var w = canvas.width, h = canvas.height;
        var dias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        var padding = { top: 12, bottom: 28, left: 10, right: 10 };
        var chartW = w - padding.left - padding.right;
        var chartH = h - padding.top - padding.bottom;
        var maxVal = Math.max.apply(null, datos) || 1;
        var barW = Math.max(8, (chartW / datos.length) - 8);
        var gap = (chartW / datos.length);

        ctx.clearRect(0, 0, w, h);

        // Color de barras
        var barColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#6366f1';
        var barHover = getComputedStyle(document.documentElement).getPropertyValue('--primary-light').trim() || '#eef2ff';
        var textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim() || '#94a3b8';

        datos.forEach(function (val, i) {
            var barH = (val / maxVal) * chartH;
            var x = padding.left + i * gap + (gap - barW) / 2;
            var y = padding.top + chartH - barH;

            // Barra degradada
            var grad = ctx.createLinearGradient(x, y, x, padding.top + chartH);
            grad.addColorStop(0, barColor);
            grad.addColorStop(1, barColor + '55');
            ctx.fillStyle = grad;

            // Esquinas redondeadas
            var radius = Math.min(4, barW / 2);
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + barW - radius, y);
            ctx.quadraticCurveTo(x + barW, y, x + barW, y + radius);
            ctx.lineTo(x + barW, padding.top + chartH);
            ctx.lineTo(x, padding.top + chartH);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
            ctx.fill();

            // Valor encima
            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text').trim() || '#0f172a';
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(val, x + barW / 2, y - 4);

            // Etiqueta abajo
            ctx.fillStyle = textColor;
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(dias[i], x + barW / 2, padding.top + chartH + 16);
        });
    }

    // ── Helpers ──
    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function setBar(id, pct, color) {
        var el = document.getElementById(id);
        if (el) {
            el.style.width = Math.min(100, Math.max(0, pct)) + '%';
            el.className = 'kpi-bar-fill ' + (color || '');
        }
    }

    function setBadge(id, type) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = 'kpi-badge ' + type;
        if (type === 'positive') el.textContent = '✓';
        else if (type === 'warning') el.textContent = '!';
        else if (type === 'danger') el.textContent = '✗';
        else el.textContent = '—';
    }

    function barColor(pct) {
        if (pct >= 70) return 'green';
        if (pct >= 40) return 'amber';
        return 'red';
    }

    function ttrBadge(horas) {
        if (horas === null) return 'neutral';
        if (horas <= 4) return 'positive';
        if (horas <= 24) return 'warning';
        return 'danger';
    }

    function csatLabel(val) {
        if (val === null) return 'Sin datos';
        if (val >= 4.5) return 'Excelente';
        if (val >= 3.5) return 'Bueno';
        if (val >= 2.5) return 'Regular';
        return 'Bajo';
    }

    function csatCircleClass(val) {
        if (val === null) return 'no-data';
        if (val >= 4.5) return 'excellent';
        if (val >= 3.5) return 'good';
        if (val >= 2.5) return 'average';
        return 'poor';
    }

    function formatNum(n) {
        if (n === null || n === undefined) return '—';
        return Number(n).toLocaleString('es-MX');
    }

    function formatCurrency(n) {
        if (n === null || n === undefined) return '—';
        return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── UI State ──
    function showLoading(show) {
        var el = document.getElementById('metricsLoading');
        var content = document.getElementById('metricsContent');
        if (el) el.style.display = show ? 'flex' : 'none';
        if (content) content.style.display = show ? 'none' : 'block';
    }

    function showError(msg) {
        var el = document.getElementById('metricsError');
        if (el) {
            el.innerHTML = '<i class="bx bx-error-circle"></i> ' + escapeHtml(msg);
            el.style.display = 'flex';
        }
    }

    function hideError() {
        var el = document.getElementById('metricsError');
        if (el) el.style.display = 'none';
    }

    // ── Iniciar ──
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
