/**
 * perfilView.js — Carga y edita el perfil desde la API
 * Funciona para Admin, Asesor y Cliente.
 *
 * Dependencias: ninguna externa (fetch nativo)
 */

(function () {
    'use strict';

    const API_BASE = '../../../backend/modules/perfil';
    const API_OBTENER = API_BASE + '/obtener.php';
    const API_ACTUALIZAR = API_BASE + '/actualizar.php';

    let rol = null;

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

    // ── Renderizar perfil (solo lectura) ──
    function renderProfile(data) {
        setText('userNombre', data.nombre_completo || '—');
        setText('userCorreo', data.correo || '—');
        setText('userRol', data.nombre_rol || '—');
        setText('userTelefono', data.telefono || '—');

        if (rol === 2) {
            setText('userEspecialidad', data.especialidad || '—');
            setText('userBiografia', data.biografia || '—');
            var af = document.getElementById('asesorFields');
            var cf = document.getElementById('clienteFields');
            if (af) af.style.display = 'block';
            if (cf) cf.style.display = 'none';
        } else if (rol === 3) {
            setText('userRfc', data.rfc || '—');
            setText('userRazonSocial', data.razon_social || '—');
            setText('userDireccionFiscal', data.direccion_fiscal || '—');
            var af = document.getElementById('asesorFields');
            var cf = document.getElementById('clienteFields');
            if (af) af.style.display = 'none';
            if (cf) cf.style.display = 'block';
        } else {
            var af = document.getElementById('asesorFields');
            var cf = document.getElementById('clienteFields');
            if (af) af.style.display = 'none';
            if (cf) cf.style.display = 'none';
        }

        window._profileData = data;
    }

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    // ── Entrar en modo edición ──
    function enterEditMode() {
        var data = window._profileData;
        if (!data) return;

        var view = document.getElementById('profileView');
        var form = document.getElementById('editForm');
        if (view) view.style.display = 'none';
        if (form) form.classList.add('active');

        var feedback = document.getElementById('profileFeedback');
        if (feedback) feedback.style.display = 'none';

        setInputValue('editNombre', data.nombre_completo || '');
        setInputValue('editTelefono', data.telefono || '');

        if (rol === 2) {
            setInputValue('editEspecialidad', data.especialidad || '');
            setTextareaValue('editBiografia', data.biografia || '');
        } else if (rol === 3) {
            setInputValue('editRfc', data.rfc || '');
            setInputValue('editRazonSocial', data.razon_social || '');
            setInputValue('editDireccionFiscal', data.direccion_fiscal || '');
        }
    }

    function setInputValue(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val;
    }

    function setTextareaValue(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val;
    }

    // ── Cancelar edición ──
    function cancelEdit() {
        var view = document.getElementById('profileView');
        var form = document.getElementById('editForm');
        if (view) view.style.display = 'block';
        if (form) form.classList.remove('active');

        var feedback = document.getElementById('profileFeedback');
        if (feedback) feedback.style.display = 'none';
    }

    // ── Guardar cambios ──
    async function saveProfile() {
        var feedback = document.getElementById('profileFeedback');
        if (feedback) feedback.style.display = 'none';

        var data = {
            nombre_completo: getInputValue('editNombre'),
            telefono: getInputValue('editTelefono'),
        };

        if (!data.nombre_completo || data.nombre_completo.trim() === '') {
            showFeedback('El nombre completo es obligatorio.', 'error');
            return;
        }

        if (rol === 2) {
            data.especialidad = getInputValue('editEspecialidad');
            data.biografia = getTextareaValue('editBiografia');
        } else if (rol === 3) {
            data.rfc = getInputValue('editRfc');
            data.razon_social = getInputValue('editRazonSocial');
            data.direccion_fiscal = getInputValue('editDireccionFiscal');
        }

        try {
            var res = await fetch(API_ACTUALIZAR, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data),
            });

            var json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Error al guardar');

            showFeedback('Perfil actualizado exitosamente.', 'success');
            cancelEdit();
            await loadProfile();
        } catch (e) {
            showFeedback('❌ ' + e.message, 'error');
        }
    }

    function getInputValue(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function getTextareaValue(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
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

    // ── Inicializar ──
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