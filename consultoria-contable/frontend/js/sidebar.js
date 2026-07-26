/**
 * sidebar.js - Control del sidebar lateral colapsable
 * 
 * Configuración: definir window.SIDEBAR_CONFIG antes de cargar este script.
 * Ejemplo:
 *   window.SIDEBAR_CONFIG = {
 *       role: 'admin', // 'admin' | 'asesor' | 'cliente'
 *       userName: 'Admin',
 *       userRole: 'Administrador',
 *       currentPage: 'dashboard' // nombre único para marcar active
 *   };
 */

(function () {
    'use strict';

    // ── Inyectar dependencias del popup de confirmación ──
    (function injectConfirmDeps() {
        if (!document.querySelector('link[href*="confirm-popup.css"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = '../../css/shared/confirm-popup.css';
            document.head.appendChild(link);
        }
        if (!document.querySelector('script[src*="confirm-popup.js"]') && !window.showConfirm) {
            const basePath = document.querySelector('script[src*="sidebar.js"]')
                ? document.querySelector('script[src*="sidebar.js"]').src.replace('sidebar.js', '')
                : '../../js/';
            const script = document.createElement('script');
            script.src = basePath + 'confirm-popup.js';
            script.defer = true;
            document.head.appendChild(script);
        }
    })();

    const config = window.SIDEBAR_CONFIG || { role: 'cliente', userName: 'Usuario', userRole: 'Usuario', currentPage: '' };

    // ===== Definición de menús por rol =====
    const menus = {
        admin: {
            sections: [
                {
                    title: 'Principal',
                    links: [
                        { label: 'Inicio', icon: 'bx bx-home', href: 'dashboard.html', id: 'dashboard' },
                        { label: 'Acciones', icon: 'bx bxs-zap', href: 'acciones.html', id: 'acciones' },
                        { label: 'Asignar Asesor', icon: 'bx bx-user-pin', href: 'asignaciones.html', id: 'asignaciones' },
                        { label: 'Reportes', icon: 'bx bx-bar-chart-alt-2', href: 'reportes.html', id: 'reportes' },
                    ]
                },
                {
                    title: 'Sistema',
                    links: [
                        { label: 'Mi Perfil', icon: 'bx bx-user', href: 'perfil.html', id: 'perfil' },
                    ]
                }
            ]
        },
        asesor: {
            sections: [
                {
                    title: 'Principal',
                    links: [
                        { label: 'Inicio', icon: 'bx bx-home', href: 'dashboard.html', id: 'dashboard' },
                        { label: 'Inbox', icon: 'bx bx-conversation', href: 'inbox.html', id: 'inbox' },
                        { label: 'Clientes', icon: 'bx bx-group', href: 'clientes.html', id: 'clientes' },
                        { label: 'Citas', icon: 'bx bx-calendar', href: 'citas.html', id: 'citas' },
                        { label: 'Documentos', icon: 'bx bx-file', href: 'documentos.html', id: 'documentos' },
                    ]
                },
                {
                    title: 'Reportes',
                    links: [
                        { label: 'Mis Reportes', icon: 'bx bx-bar-chart-alt-2', href: 'reportes.html', id: 'reportes' },
                        { label: 'Mi Perfil', icon: 'bx bx-user', href: 'perfil.html', id: 'perfil' },
                    ]
                }
            ]
        },
        cliente: {
            sections: [
                {
                    title: 'Panel',
                    links: [
                        { label: 'Inicio', icon: 'bx bx-home', href: 'dashboard.html', id: 'dashboard' },
                        { label: 'Inbox', icon: 'bx bx-conversation', href: 'inbox.html', id: 'inbox' },
                        { label: 'Mis Declaraciones', icon: 'bx bx-notepad', href: 'mis_declaraciones.html', id: 'declaraciones' },
                        { label: 'Documentos', icon: 'bx bx-file', href: 'documentos.html', id: 'documentos' },
                        { label: 'Pagos', icon: 'bx bx-credit-card', href: 'pagos.html', id: 'pagos' },
                        { label: 'Tareas', icon: 'bx bx-task', href: 'tareas.html', id: 'tareas' },
                    ]
                },
                {
                    title: 'Configuración',
                    links: [
                        { label: 'Servicios', icon: 'bx bx-briefcase', href: 'servicios.html', id: 'servicios' },
                        { label: 'Perfil', icon: 'bx bx-user', href: 'perfil.html', id: 'perfil' },
                    ]
                }
            ]
        }
    };

    // ===== Construir HTML del sidebar =====
    function buildSidebar() {
        const roleMenus = menus[config.role] || menus.cliente;

        let navHtml = '';
        roleMenus.sections.forEach(section => {
            navHtml += `<div class="nav-section-title">${section.title}</div>`;
            section.links.forEach(link => {
                const activeClass = link.id === config.currentPage ? ' active' : '';
                navHtml += `<a href="${link.href}" class="${activeClass}">
                    <i class="${link.icon}"></i>
                    <span class="nav-label">${link.label}</span>
                </a>`;
            });
        });

        // Avatar iniciales
        const nameParts = config.userName.split(' ');
        const initials = nameParts.map(p => p[0]).join('').substring(0, 2).toUpperCase();

        const sidebarHtml = `
            <!-- Overlay móvil -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <!-- Botón toggle móvil -->
            <button class="mobile-toggle" id="mobileToggle" aria-label="Abrir menú">
                <i class="bx bx-menu"></i>
            </button>

            <!-- Sidebar -->
            <aside class="sidebar" id="sidebar">

                <div class="sidebar-profile">
                    <div class="avatar">${initials}</div>
                    <div class="user-info">
                        <div class="user-name">${config.userName}</div>
                        <div class="user-role">${config.userRole}</div>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    ${navHtml}
                </nav>

                <div class="sidebar-footer">
                    <a href="../../pages/auth/change_password.html" class="change-pass-link" title="Cambiar contraseña">
                        <i class="bx bx-lock-alt"></i>
                        <span class="nav-label">Cambiar Contraseña</span>
                    </a>
                    <a href="#" class="logout-btn-sidebar" id="logoutSidebarBtn" title="Cerrar sesión">
                        <i class="bx bx-log-out"></i>
                        <span class="nav-label">Cerrar Sesión</span>
                    </a>
                </div>
            </aside>

            <button class="toggle-btn" id="sidebarToggle" aria-label="Colapsar menú">
                <i class="bx bx-chevron-left"></i>
            </button>
        `;

        // Insertar al inicio del body
        document.body.insertAdjacentHTML('afterbegin', sidebarHtml);
    }

    // ===== Inicializar comportamientos =====
    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if (!sidebar) return;

        // Estado colapsado desde localStorage
        const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (collapsed) {
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
            if (toggleBtn) toggleBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';
        }

        // Toggle colapso en desktop
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                this.innerHTML = isCollapsed ? '<i class="bx bx-chevron-right"></i>' : '<i class="bx bx-chevron-left"></i>';
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }

        // Toggle móvil
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function () {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
            });
        }

        // Cerrar al hacer clic en overlay
        if (overlay) {
            overlay.addEventListener('click', function () {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            });
        }

        // ── Confirmación al cambiar contraseña ──
        const changePassLink = document.querySelector('.change-pass-link');
        if (changePassLink) {
            changePassLink.addEventListener('click', async function (e) {
                e.preventDefault();
                const href = this.getAttribute('href');

                if (typeof showConfirm === 'function') {
                    const confirmed = await showConfirm({
                        title: 'Cambiar Contraseña',
                        message: '¿Estás seguro de que quieres cambiar tu contraseña ahora?',
                        confirmText: 'Sí, cambiar',
                        cancelText: 'Cancelar',
                        icon: 'bx bx-lock-alt',
                        variant: 'warning'
                    });
                    if (confirmed && href) window.location.href = href;
                } else {
                    // Fallback si confirm-popup.js no está cargado
                    if (href) window.location.href = href;
                }
            });
        }

        // ── Confirmación al cerrar sesión ──
        const logoutBtn = document.getElementById('logoutSidebarBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async function (e) {
                e.preventDefault();

                if (typeof showConfirm === 'function') {
                    const confirmed = await showConfirm({
                        title: 'Cerrar Sesión',
                        message: '¿Estás seguro de que deseas cerrar tu sesión actual?',
                        confirmText: 'Sí, cerrar sesión',
                        cancelText: 'Cancelar',
                        icon: 'bx bx-log-out-circle',
                        variant: 'danger'
                    });
                    if (confirmed) logout();
                } else {
                    logout();
                }
            });
        }

    }

    // ===== Ejecutar =====
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            buildSidebar();
            initSidebar();
        });
    } else {
        buildSidebar();
        initSidebar();
    }
})();
