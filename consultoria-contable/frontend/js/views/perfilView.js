/**
 * perfilView.js — Carga y edita el perfil desde la API con control de visibilidad por rol.
 *
 * Roles soportados:
 *   Admin (1):   Ve y edita todos los campos. Secciones: consultoría.
 *   Asesor (2):  Ve y edita sus campos profesionales. Ve datos fiscales de clientes asignados.
 *   Cliente (3): Ve y edita su información de contacto. RFC/razón social solo lectura.
 *
 * Dependencias: fetch nativo.
 */
(function () {
    'use strict';

    const API_BASE = '../../../backend/modules/perfil';
    const API_OBTENER = API_BASE + '/obtener.php';
    const API_ACTUALIZAR = API_BASE + '/actualizar.php';

    let rol = null;
    let isEditable = true; // Admin viendo otro perfil puede editar, cliente/asesor solo el propio

    // ── Cargar perfil ──
    async function loadProfile() {
        const loading = document.getElementById('profileLoading');
        const content = document.getElementById('profileContent');
        const feedback = document.getElementById('profileFeedback');

        if (loading) loading.style.display = 'flex';
        if (content) content.style.display = 'none';
        if (feedback) feedback.style.display = 'none';

        try {
            const res = await fetch(API_OBTENER, { credentials: 'include' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Error al cargar perfil');

            const data = json.data;
            if (!data) throw new Error('Perfil no encontrado');

            rol = parseInt(data.id_rol);
            isEditable = true; // Propio perfil siempre editable
            renderProfile(data);
        } catch (e) {
            if (feedback) {
                feedback.textContent = e.message;
                feedback.className = 'profile-feedback error';
                feedback.style.display = 'block';
            }
        } finally {
            if (loading) loading.style.display = 'none';
            if (content) content.style.display = 'flex';
        }
    }

    // ═══════════════════════════════════════════════════════════
    // RENDERIZAR PERFIL (solo lectura)
    // ═══════════════════════════════════════════════════════════
    function renderProfile(data) {
        setText('userNombre', data.nombre_completo || '—');
        setText('userCorreo', data.correo || '—');
        setText('userRol', data.nombre_rol || '—');
        setText('userTelefono', data.telefono || '—');
        // Número de cliente
        setText('userNumeroCliente', data.numero_cliente || '—');

        // Ocultar todas las secciones primero
        hideAllSections();

        if (rol === 1) {
            renderAdminView(data);
        } else if (rol === 2) {
            renderAsesorView(data);
        } else if (rol === 3) {
            renderClienteView(data);
        }

        window._profileData = data;
    }

    function renderAdminView(data) {
        showSection('adminConsultoriaFields');
        setText('userConsultoriaNombre', data.consultoria_nombre || '—');
        setText('userConsultoriaDireccion', data.consultoria_direccion || '—');
        setText('userConsultoriaTelefono', data.consultoria_telefono || '—');
        setText('userConsultoriaCorreo', data.consultoria_correo || '—');
    }

    function renderAsesorView(data) {
        showSection('asesorFields');
        setText('userEspecialidad', data.especialidad || '—');
        setText('userBiografia', data.biografia || '—');
        setText('userCedula', data.cedula_profesional || '—');
        setText('userCorreoCorp', data.correo_corporativo || '—');
        setText('userExtension', data.extension || '—');

        var disp = data.disponibilidad || 'activo';
        var dispLabels = { activo: '✅ Activo', descanso: '⏸️ En descanso', vacaciones: '🏖️ De vacaciones' };
        setText('userDisponibilidad', dispLabels[disp] || disp);

        // Clientes asignados (carga desde el backend)
        setText('userCarteraClientes', data.total_clientes_asignados
            ? data.total_clientes_asignados + ' clientes'
            : '—');
    }

    function renderClienteView(data) {
        showSection('clienteFields');
        setText('userRfc', data.rfc || '—');
        setText('userRazonSocial', data.razon_social || '—');
        setText('userDireccionFiscal', data.direccion_fiscal || '—');
        setText('userRegimenFiscal', data.regimen_fiscal || '—');
        setText('userRepresentanteLegal', data.representante_legal || '—');
        setText('userDireccionComercial', data.direccion_comercial || '—');
        setText('userPaquete', data.paquete_contratado || '—');
        setText('userEstatusSuscripcion', data.estatus_suscripcion || '—');
        setText('userConstanciaFiscal', data.constancia_fiscal
            ? '📄 Archivo disponible'
            : 'No disponible');

        // Marcar campos que el cliente NO puede editar
        markReadOnly('userRfc', true);
        markReadOnly('userRazonSocial', true);
        markReadOnly('userDireccionFiscal', true);
        markReadOnly('userRegimenFiscal', true);
    }

    // ═══════════════════════════════════════════════════════════
    // ENTRAR EN MODO EDICIÓN
    // ═══════════════════════════════════════════════════════════
    function enterEditMode() {
        var data = window._profileData;
        if (!data) return;

        var view = document.getElementById('profileView');
        var form = document.getElementById('editForm');
        if (view) view.style.display = 'none';
        if (form) form.classList.add('active');

        var feedback = document.getElementById('profileFeedback');
        if (feedback) feedback.style.display = 'none';

        // Ocultar todos los grupos de edición
        hideAllEditSections();

        // Campos comunes
        setInputValue('editNombre', data.nombre_completo || '');
        setInputValue('editTelefono', data.telefono || '');

        if (rol === 1) {
            showSection('editAdminConsultoriaFields');
            setInputValue('editConsultoriaNombre', data.consultoria_nombre || '');
            setInputValue('editConsultoriaDireccion', data.consultoria_direccion || '');
            setInputValue('editConsultoriaTelefono', data.consultoria_telefono || '');
            setInputValue('editConsultoriaCorreo', data.consultoria_correo || '');
        } else if (rol === 2) {
            showSection('editAsesorFields');
            setInputValue('editEspecialidad', data.especialidad || '');
            setTextareaValue('editBiografia', data.biografia || '');
            setInputValue('editCedula', data.cedula_profesional || '');
            setInputValue('editCorreoCorp', data.correo_corporativo || '');
            setInputValue('editExtension', data.extension || '');
        } else if (rol === 3) {
            showSection('editClienteFields');
            // Solo editable por admin
            setInputValue('editRfc', data.rfc || '');
            setInputValue('editRazonSocial', data.razon_social || '');
            setInputValue('editDireccionFiscal', data.direccion_fiscal || '');
            setInputValue('editRegimenFiscal', data.regimen_fiscal || '');
            setInputValue('editRepresentanteLegal', data.representante_legal || '');
            // Campos que el cliente sí puede editar
            setInputValue('editDireccionComercial', data.direccion_comercial || '');
            // Solo lectura para cliente: RFC, razón social, dirección fiscal, régimen
            disableInput('editRfc');
            disableInput('editRazonSocial');
            disableInput('editDireccionFiscal');
            disableInput('editRegimenFiscal');
        }
    }

    // ═══════════════════════════════════════════════════════════
    // CANCELAR EDICIÓN
    // ═══════════════════════════════════════════════════════════
    function cancelEdit() {
        var view = document.getElementById('profileView');
        var form = document.getElementById('editForm');
        if (view) view.style.display = 'block';
        if (form) form.classList.remove('active');

        var feedback = document.getElementById('profileFeedback');
        if (feedback) feedback.style.display = 'none';

        // Reactivar inputs deshabilitados
        enableAllInputs();
    }

    // ═══════════════════════════════════════════════════════════
    // GUARDAR CAMBIOS
    // ═══════════════════════════════════════════════════════════
    async function saveProfile() {
        var feedback = document.getElementById('profileFeedback');
        if (feedback) feedback.style.display = 'none';

        var payload = {
            nombre_completo: getInputValue('editNombre'),
            telefono: getInputValue('editTelefono'),
        };

        if (!payload.nombre_completo || payload.nombre_completo.trim() === '') {
            showFeedback('El nombre completo es obligatorio.', 'error');
            return;
        }

        if (rol === 1) {
            payload.consultoria_nombre    = getInputValue('editConsultoriaNombre');
            payload.consultoria_direccion  = getInputValue('editConsultoriaDireccion');
            payload.consultoria_telefono   = getInputValue('editConsultoriaTelefono');
            payload.consultoria_correo     = getInputValue('editConsultoriaCorreo');
        } else if (rol === 2) {
            payload.especialidad        = getInputValue('editEspecialidad');
            payload.biografia           = getTextareaValue('editBiografia');
            payload.cedula_profesional  = getInputValue('editCedula');
            payload.correo_corporativo  = getInputValue('editCorreoCorp');
            payload.extension           = getInputValue('editExtension');
        } else if (rol === 3) {
            // Solo campos que el cliente puede modificar
            payload.direccion_comercial = getInputValue('editDireccionComercial');
            payload.representante_legal = getInputValue('editRepresentanteLegal');
            // RFC/razón social no se envían (solo admin)
        }

        try {
            var res = await fetch(API_ACTUALIZAR, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(payload),
            });

            var json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Error al guardar');

            showFeedback('Perfil actualizado exitosamente.', 'success');
            cancelEdit();
            enableAllInputs();
            await loadProfile();
        } catch (e) {
            showFeedback(e.message, 'error');
        }
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════
    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val ?? '—';
    }

    function setInputValue(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val ?? '';
    }

    function setTextareaValue(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val ?? '';
    }

    function getInputValue(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function getTextareaValue(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function showSection(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'block';
    }

    function hideAllSections() {
        var ids = ['asesorFields', 'clienteFields', 'adminConsultoriaFields'];
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    }

    function hideAllEditSections() {
        var ids = ['editAsesorFields', 'editClienteFields', 'editAdminConsultoriaFields'];
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    }

    function disableInput(id) {
        var el = document.getElementById(id);
        if (el) {
            el.disabled = true;
            el.style.opacity = '0.6';
            el.style.cursor = 'not-allowed';
            el.title = 'Solo el administrador puede modificar este campo';
        }
    }

    function enableAllInputs() {
        document.querySelectorAll('#editForm input[disabled], #editForm textarea[disabled]')
            .forEach(function (el) {
                el.disabled = false;
                el.style.opacity = '';
                el.style.cursor = '';
                el.title = '';
            });
    }

    function markReadOnly(id, isReadOnly) {
        var el = document.getElementById(id);
        if (!el) return;
        if (isReadOnly) {
            el.classList.add('readonly-field');
            var badge = document.createElement('span');
            badge.className = 'readonly-badge';
            badge.textContent = ' 🔒';
            if (!el.querySelector('.readonly-badge')) {
                el.appendChild(badge);
            }
        }
    }

    function showFeedback(msg, type) {
        var feedback = document.getElementById('profileFeedback');
        if (!feedback) return;
        feedback.textContent = msg;
        feedback.className = 'profile-feedback ' + type;
        feedback.style.display = 'block';
    }

    // ── Bindear botones ──
    function bindButtons() {
        var editBtn = document.getElementById('btnEditProfile');
        if (editBtn) editBtn.addEventListener('click', enterEditMode);

        var cancelBtn = document.getElementById('btnCancelEdit');
        if (cancelBtn) cancelBtn.addEventListener('click', cancelEdit);

        var saveBtn = document.getElementById('btnSaveProfile');
        if (saveBtn) saveBtn.addEventListener('click', saveProfile);
    }

    function init() {
        bindButtons();
        loadProfile();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();