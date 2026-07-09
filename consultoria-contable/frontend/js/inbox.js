/**
 * inbox.js — Mensajería global (contactos + conversaciones)
 *
 * Dependencias: Boxicons, inbox.css
 * API: mensajes/contactos.php, mensajes/conversacion.php,
 *       mensajes/enviar.php, mensajes/no_leidos.php, mensajes/marcar_leido.php
 */

(function () {
    'use strict';

    const API_BASE = '../../../backend/modules/mensajes/';
    const POLL_INTERVAL = 10000; // 10 segundos

    let contactos = [];
    let contactoActivo = null;
    let mensajes = [];
    let pollTimer = null;

    // ── Inicializar ──
    function init() {
        cargarContactos();
        iniciarPolling();
    }

    // ── Cargar contactos ──
    async function cargarContactos() {
        const list = document.getElementById('inboxContactList');
        if (!list) return;

        list.innerHTML = '<div class="inbox-loading"><i class="bx bx-loader bx-spin"></i> Cargando...</div>';

        try {
            const res = await fetch(API_BASE + 'contactos.php', { credentials: 'include' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message);

            contactos = json.data || [];
            renderContactos();
        } catch (e) {
            list.innerHTML = '<div class="inbox-empty"><i class="bx bx-error-circle"></i><p>Error al cargar contactos</p></div>';
        }
    }

    function renderContactos() {
        const list = document.getElementById('inboxContactList');
        if (!list) return;

        if (contactos.length === 0) {
            list.innerHTML = '<div class="inbox-empty"><i class="bx bx-group"></i><p>No hay contactos disponibles</p></div>';
            return;
        }

        list.innerHTML = '';
        contactos.forEach(c => {
            const div = document.createElement('div');
            div.className = 'inbox-contact-item';
            div.dataset.id = c.id_usuario;

            const initials = (c.correo || '??').substring(0, 2).toUpperCase();
            const rolBadge = c.nombre_rol === 'Administrador' ? 'Admin' :
                             c.nombre_rol === 'Asesor' ? 'Asesor' : 'Cliente';

            div.innerHTML = `
                <div class="inbox-contact-avatar">${initials}</div>
                <div class="inbox-contact-info">
                    <div class="inbox-contact-name">${escapeHtml(c.correo)}</div>
                    <div class="inbox-contact-preview">${escapeHtml(rolBadge)}${c.numero_cliente ? ' · ' + escapeHtml(c.numero_cliente) : ''}</div>
                </div>
            `;

            div.addEventListener('click', () => abrirConversacion(c));
            list.appendChild(div);
        });
    }

    // ── Abrir conversación ──
    async function abrirConversacion(contacto) {
        contactoActivo = contacto;

        // Marcar activo en lista
        document.querySelectorAll('.inbox-contact-item').forEach(el => el.classList.remove('active'));
        const item = document.querySelector(`.inbox-contact-item[data-id="${contacto.id_usuario}"]`);
        if (item) item.classList.add('active');

        // Mostrar panel de chat
        mostrarChatPanel(contacto);

        // Cargar mensajes
        await cargarMensajes(contacto.id_usuario);

        // Marcar como leídos
        try {
            await fetch(API_BASE + 'marcar_leido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id_otro_usuario: contacto.id_usuario })
            });
            // Quitar badge del contacto
            const badge = item ? item.querySelector('.inbox-contact-badge') : null;
            if (badge) badge.remove();
        } catch (e) { /* silencio */ }

        // Scroll al final
        scrollChatToBottom();

        // Mobile: mostrar chat
        if (window.innerWidth <= 768) {
            document.getElementById('inboxContacts').classList.remove('mobile-visible');
            document.getElementById('inboxChat').classList.add('mobile-visible');
        }
    }

    function mostrarChatPanel(contacto) {
        const chatEmpty = document.getElementById('inboxChatEmpty');
        const chatContent = document.getElementById('inboxChatContent');
        const chatName = document.getElementById('inboxChatName');

        if (chatEmpty) chatEmpty.style.display = 'none';
        if (chatContent) chatContent.style.display = 'flex';
        if (chatName) chatName.textContent = contacto.correo;
    }

    function ocultarChatPanel() {
        const chatEmpty = document.getElementById('inboxChatEmpty');
        const chatContent = document.getElementById('inboxChatContent');
        if (chatEmpty) chatEmpty.style.display = 'flex';
        if (chatContent) chatContent.style.display = 'none';
        contactoActivo = null;

        document.querySelectorAll('.inbox-contact-item')
            .forEach(el => el.classList.remove('active'));
    }

    // ── Cargar mensajes ──
    async function cargarMensajes(idOtro) {
        const container = document.getElementById('inboxMessages');
        if (!container) return;

        container.innerHTML = '<div class="inbox-loading"><i class="bx bx-loader bx-spin"></i></div>';

        try {
            const res = await fetch(API_BASE + 'conversacion.php?id_usuario=' + idOtro, { credentials: 'include' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message);

            mensajes = json.data || [];
            renderMensajes();
        } catch (e) {
            container.innerHTML = '<div class="inbox-empty"><i class="bx bx-error-circle"></i><p>Error al cargar</p></div>';
        }
    }

    function renderMensajes() {
        const container = document.getElementById('inboxMessages');
        if (!container) return;

        if (mensajes.length === 0) {
            container.innerHTML = '<div class="inbox-empty"><i class="bx bx-message-detail"></i><p>No hay mensajes aún</p></div>';
            return;
        }

        container.innerHTML = '';
        mensajes.forEach(m => {
            const isSent = m.id_remitente == window.USER_ID;
            const div = document.createElement('div');
            div.className = 'inbox-msg ' + (isSent ? 'sent' : 'received');
            div.innerHTML = `
                ${escapeHtml(m.contenido_texto)}
                <div class="inbox-msg-time">${formatearHora(m.fecha_envio)}</div>
            `;
            container.appendChild(div);
        });

        scrollChatToBottom();
    }

    function scrollChatToBottom() {
        const container = document.getElementById('inboxMessages');
        if (container) {
            setTimeout(() => { container.scrollTop = container.scrollHeight; }, 100);
        }
    }

    // ── Enviar mensaje ──
    async function enviarMensaje() {
        const input = document.getElementById('inboxInput');
        const btn = document.getElementById('inboxSendBtn');
        if (!input || !btn || !contactoActivo) return;

        const texto = input.value.trim();
        if (!texto) return;

        btn.disabled = true;

        try {
            const res = await fetch(API_BASE + 'enviar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    id_destinatario: contactoActivo.id_usuario,
                    contenido_texto: texto
                })
            });

            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Error');
            }

            input.value = '';
            input.focus();
            await cargarMensajes(contactoActivo.id_usuario);
        } catch (e) {
            alert('Error: ' + e.message);
        } finally {
            btn.disabled = false;
        }
    }

    // ── Polling mensajes no leídos ──
    async function iniciarPolling() {
        await actualizarBadges();
        pollTimer = setInterval(actualizarBadges, POLL_INTERVAL);
    }

    async function actualizarBadges() {
        try {
            const res = await fetch(API_BASE + 'no_leidos.php', { credentials: 'include' });
            const json = await res.json();
            if (!res.ok) return;

            const count = json.data ? json.data.no_leidos : 0;
            actualizarBadgeTopBar(count);
            actualizarBadgesContactos();

            // Si estamos en una conversación activa, refrescar mensajes
            if (contactoActivo) {
                await cargarMensajes(contactoActivo.id_usuario);
            }
        } catch (e) { /* silencio */ }
    }

    function actualizarBadgeTopBar(count) {
        const badge = document.getElementById('msgBadge');
        if (!badge) return;

        if (count === 0) {
            badge.classList.add('hidden');
            return;
        }

        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('hidden');
        badge.classList.add('pulse');
        setTimeout(() => badge.classList.remove('pulse'), 300);
    }

    function actualizarBadgesContactos() {
        document.querySelectorAll('.inbox-contact-item').forEach(el => {
            // Simple: remover badges previos (se actualizarán al abrir conversación)
        });
    }

    // ── Helpers ──
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatearHora(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const now = new Date();
        const isToday = d.toDateString() === now.toDateString();
        const h = d.getHours().toString().padStart(2, '0');
        const m = d.getMinutes().toString().padStart(2, '0');
        if (isToday) return h + ':' + m;
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit' }) + ' ' + h + ':' + m;
    }

    // ── Events ──
    function bindEvents() {
        // Enviar con Enter
        const input = document.getElementById('inboxInput');
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') enviarMensaje();
            });
        }

        // Botón enviar
        const btn = document.getElementById('inboxSendBtn');
        if (btn) {
            btn.addEventListener('click', enviarMensaje);
        }

        // Botón volver (mobile)
        const backBtn = document.getElementById('inboxMobileBack');
        if (backBtn) {
            backBtn.addEventListener('click', function () {
                document.getElementById('inboxChat').classList.remove('mobile-visible');
                document.getElementById('inboxContacts').classList.add('mobile-visible');
            });
        }
    }

    // ── Exponer para refresco desde fuera ──
    window.refreshInbox = cargarContactos;
    window.refreshInboxBadge = actualizarBadges;

    // ── Arrancar ──
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            init();
            bindEvents();
        });
    } else {
        init();
        bindEvents();
    }

})();
