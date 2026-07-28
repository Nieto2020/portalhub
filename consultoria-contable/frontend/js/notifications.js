/**
 * notifications.js — Barra superior global + campana de notificaciones
 *
 * Inyecta automáticamente el #globalTopBar con la campana.
 * Solo requiere incluir el CSS y este JS; no necesita HTML adicional.
 *
 * Dependencias: Boxicons (CDN), notifications.css
 */

(function () {
    'use strict';

    const NOTIFICATIONS_API = '../../../backend/modules/notificaciones/listar.php';
    const MARK_READ_API = '../../../backend/modules/notificaciones/marcar_leido.php';
    const POLL_INTERVAL = 30000; // 30 segundos

    let notifData = [];
    let pollTimer = null;

    // ── Mapa de iconos según tipo_evento ──
    const ICON_MAP = {
        cita_creada:       { icon: 'bx bx-calendar-check', color: 'info' },
        cita_cancelada:    { icon: 'bx bx-calendar-x',     color: 'error' },
        cita_modificada:   { icon: 'bx bx-calendar-edit',  color: 'warning' },
        documento_subido:  { icon: 'bx bx-file',           color: 'info' },
        documento_aprobado:{ icon: 'bx bx-file-find',      color: 'success' },
        pago_recibido:     { icon: 'bx bx-credit-card',    color: 'success' },
        pago_pendiente:    { icon: 'bx bx-credit-card-alt',color: 'warning' },
        usuario_creado:    { icon: 'bx bx-user-plus',      color: 'info' },
        password_reset:    { icon: 'bx bx-key',            color: 'warning' },
        reporte_generado:  { icon: 'bx bx-bar-chart',      color: 'info' },
        mensaje_nuevo:     { icon: 'bx bx-message',        color: 'info' },
        sistema:           { icon: 'bx bx-info-circle',    color: 'info' },
    };

    function getIconConfig(tipo) {
        return ICON_MAP[tipo] || { icon: 'bx bx-bell', color: 'info' };
    }

    function timeAgo(dateStr) {
        const now = new Date();
        const date = new Date(dateStr);
        const diffMs = now - date;
        const diffMin = Math.floor(diffMs / 60000);
        if (diffMin < 1) return 'Ahora';
        if (diffMin < 60) return `Hace ${diffMin} min`;
        const diffHrs = Math.floor(diffMin / 60);
        if (diffHrs < 24) return `Hace ${diffHrs}h`;
        const diffDays = Math.floor(diffHrs / 24);
        if (diffDays < 7) return `Hace ${diffDays}d`;
        return date.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit' });
    }

    // ── Inyectar la barra superior global al <body> ──
    function injectTopBar() {
        // Evitar doble inyección
        if (document.getElementById('globalTopBar')) return;

        const topBarHTML = `
            <div id="globalTopBar">
                <div class="topbar-brand">
                    <img src="../../css/img/icon.png" alt="Logo" class="topbar-logo" onerror="this.style.display='none'">
                    <span>CONSULTANCY</span>
                </div>
                <div class="topbar-title" id="topbarPageTitle">
                    <span></span>
                </div>
                <div class="topbar-right" id="notifBellContainer">
                    <button class="topbar-theme-btn" id="themeToggle" title="Cambiar tema">
                        <i class="bx bx-moon"></i>
                    </button>
                    <a href="inbox.html" class="topbar-msg-btn" id="topbarMsgBtn" title="Mensajes">
                        <i class="bx bx-message-square-detail"></i>
                        <span class="notif-badge hidden" id="msgBadge">0</span>
                    </a>
                    <div class="notif-bell" id="notifBell">
                        <i class="bx bx-bell"></i>
                        <span class="notif-badge hidden" id="notifBadge">0</span>
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-dropdown-header">
                                <h3><i class="bx bx-bell"></i> Notificaciones</h3>
                                <button class="notif-mark-all" id="notifMarkAll">Marcar todo leído</button>
                            </div>
                            <div class="notif-dropdown-body" id="notifDropdownBody">
                                <div class="notif-loading"><i class="bx bx-loader bx-spin"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('afterbegin', topBarHTML);

        // Set page title from config
        const config = window.SIDEBAR_CONFIG || {};
        const titleEl = document.getElementById('topbarPageTitle');
        if (titleEl && config.pageTitle) {
            titleEl.querySelector('span').textContent = config.pageTitle;
            titleEl.style.display = 'flex';
        }
    }

    // ── Bindear eventos del dropdown ──
    function bindEvents() {
        const bell = document.getElementById('notifBell');
        if (!bell) return;

        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            this.classList.toggle('open');
            if (this.classList.contains('open')) {
                loadNotifications();
            }
        });

        document.addEventListener('click', function (e) {
            if (!bell.contains(e.target)) {
                bell.classList.remove('open');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') bell.classList.remove('open');
        });

        const markAll = document.getElementById('notifMarkAll');
        if (markAll) {
            markAll.addEventListener('click', function (e) {
                e.stopPropagation();
                markAllRead();
            });
        }
    }

    // ── Cargar notificaciones ──
    async function loadNotifications() {
        const body = document.getElementById('notifDropdownBody');
        if (!body) return;

        try {
            const res = await fetch(NOTIFICATIONS_API, { credentials: 'include' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Error al cargar');

            notifData = json.data || [];
            renderNotifications(notifData);
            updateBadge(notifData);
        } catch (e) {
            body.innerHTML = `
                <div class="notif-error">
                    <i class="bx bx-error-circle"></i>
                    <p>Error al cargar notificaciones</p>
                </div>`;
        }
    }

    // ── Renderizar notificaciones ──
    function renderNotifications(list) {
        const body = document.getElementById('notifDropdownBody');
        if (!body) return;

        if (!list || list.length === 0) {
            body.innerHTML = `
                <div class="notif-empty">
                    <i class="bx bx-check-circle"></i>
                    <p>No hay notificaciones</p>
                </div>`;
            return;
        }

        body.innerHTML = '';
        list.forEach(n => {
            const ic = getIconConfig(n.tipo_evento);
            const isUnread = n.leida == 0;
            const item = document.createElement('div');
            item.className = `notif-item ${isUnread ? 'unread' : 'read'}`;
            item.dataset.id = n.id_notificacion;

            item.innerHTML = `
                <div class="notif-icon ${ic.color}"><i class="${ic.icon}"></i></div>
                <div class="notif-content">
                    <p class="notif-text">${escapeHtml(n.mensaje_texto)}</p>
                    <span class="notif-time">${timeAgo(n.fecha_creacion)}</span>
                </div>
                ${isUnread ? '<div class="notif-dot"></div>' : ''}`;

            if (isUnread) {
                item.addEventListener('click', function () {
                    markAsRead(n.id_notificacion, this);
                });
            }

            body.appendChild(item);
        });
    }

    // ── Actualizar badge ──
    function updateBadge(list) {
        const badge = document.getElementById('notifBadge');
        if (!badge) return;

        const unread = list.filter(n => n.leida == 0).length;
        if (unread === 0) {
            badge.classList.add('hidden');
            return;
        }

        badge.textContent = unread > 99 ? '99+' : unread;
        badge.classList.remove('hidden');
        badge.classList.add('pulse');
        setTimeout(() => badge.classList.remove('pulse'), 300);
    }

    // ── Marcar una como leída ──
    async function markAsRead(id, el) {
        try {
            const res = await fetch(MARK_READ_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id_notificacion: id })
            });
            if (!res.ok) return;

            el.classList.remove('unread');
            el.classList.add('read');
            const dot = el.querySelector('.notif-dot');
            if (dot) dot.remove();

            const notif = notifData.find(n => n.id_notificacion == id);
            if (notif) notif.leida = 1;
            updateBadge(notifData);
        } catch (e) { /* silencio */ }
    }

    // ── Marcar todo leído ──
    async function markAllRead() {
        const unreadItems = document.querySelectorAll('.notif-item.unread');
        if (unreadItems.length === 0) return;

        unreadItems.forEach(el => {
            el.classList.remove('unread');
            el.classList.add('read');
            const dot = el.querySelector('.notif-dot');
            if (dot) dot.remove();
        });

        notifData.forEach(n => { n.leida = 1; });
        updateBadge(notifData);

        for (const el of unreadItems) {
            const id = el.dataset.id;
            if (id) {
                try {
                    await fetch(MARK_READ_API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({ id_notificacion: parseInt(id) })
                    });
                } catch (e) { /* silencio */ }
            }
        }
    }

    // ── Polling periódico ──
    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        loadBadgeOnly();
        loadMsgBadge();
        pollTimer = setInterval(function () {
            loadBadgeOnly();
            loadMsgBadge();
        }, POLL_INTERVAL);
    }

    async function loadBadgeOnly() {
        try {
            const res = await fetch(NOTIFICATIONS_API, { credentials: 'include' });
            const json = await res.json();
            if (res.ok) {
                notifData = json.data || [];
                updateBadge(notifData);
            }
        } catch (e) { /* silencio */ }
    }

    // ── Badge de mensajes no leídos ──
    async function loadMsgBadge() {
        try {
            const res = await fetch('../../../backend/modules/mensajes/no_leidos.php', {
                credentials: 'include'
            });
            const json = await res.json();
            if (!res.ok) return;

            const count = json.data ? json.data.no_leidos : 0;
            const badge = document.getElementById('msgBadge');
            if (!badge) return;

            if (count === 0) {
                badge.classList.add('hidden');
                return;
            }
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } catch (e) { /* silencio */ }
    }

    // ── Helper: escape HTML ──
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ── Theme toggle: cicla entre light → dark → dark-blue → papersheet ──
    function initThemeToggle() {
        const btn = document.getElementById('themeToggle');
        const icon = btn ? btn.querySelector('i') : null;
        const themes = ['light', 'dark', 'dark-blue'];

        function applyTheme(themeName) {
            document.documentElement.setAttribute('data-theme', themeName);
            if (icon) {
                const icons = { light: 'bx-sun', dark: 'bx-moon', 'dark-blue': 'bx-cloud' };
                icon.className = 'bx ' + (icons[themeName] || 'bx-sun');
            }
            localStorage.setItem('theme', themeName);
        }

        const savedTheme = localStorage.getItem('theme');
        const isValid = themes.includes(savedTheme);
        applyTheme(isValid ? savedTheme : 'light');

        if (btn) {
            btn.addEventListener('click', function () {
                const current = document.documentElement.getAttribute('data-theme') || 'light';
                const idx = themes.indexOf(current);
                const next = themes[(idx + 1) % themes.length];
                applyTheme(next);
            });
        }
    }

    // ── Inicializar ──
    function init() {
        injectTopBar();
        bindEvents();
        initThemeToggle();
        startPolling();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exponer para recarga manual
    window.refreshNotifications = loadBadgeOnly;

})();
