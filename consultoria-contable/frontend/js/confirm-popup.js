/**
 * confirm-popup.js — Popup de confirmación global
 *
 * Uso desde cualquier página:
 *
 *   // Confirmación simple
 *   const ok = await showConfirm('¿Estás seguro de eliminar este registro?');
 *   if (ok) { ... }
 *
 *   // Con personalización
 *   const ok = await showConfirm({
 *       title: 'Eliminar Usuario',
 *       message: 'Esta acción no se puede deshacer.',
 *       confirmText: 'Eliminar',
 *       cancelText: 'Cancelar',
 *       variant: 'danger' // 'default' | 'danger' | 'warning'
 *   });
 *
 * Dependencias: Boxicons, confirm-popup.css
 */

(function () {
    'use strict';

    /**
     * @param {string|object} options - Mensaje string o objeto de configuración
     * @param {string} options.title    - Título del popup
     * @param {string} options.message  - Mensaje descriptivo
     * @param {string} [options.confirmText] - Texto del botón confirmar (default: 'Sí')
     * @param {string} [options.cancelText]  - Texto del botón cancelar (default: 'No')
     * @param {string} [options.icon]        - Clase Boxicon del icono (default: 'bx bx-help-circle')
     * @param {string} [options.variant]     - 'default' | 'danger' | 'warning'
     * @returns {Promise<boolean>}
     */
    window.showConfirm = function (options) {
        if (typeof options === 'string') {
            options = { message: options };
        }

        const {
            title       = 'Confirmar Acción',
            message     = '¿Estás seguro de que deseas continuar?',
            confirmText = 'Sí',
            cancelText  = 'No',
            icon        = 'bx bx-help-circle',
            variant     = 'default'
        } = options;

        return new Promise(function (resolve) {

            // Si ya existe uno abierto, removerlo primero
            const existing = document.getElementById('confirmPopupOverlay');
            if (existing) existing.remove();

            const iconVariantClass = variant === 'danger' ? ' danger' : variant === 'warning' ? ' warning' : '';
            const btnClass = variant === 'danger' ? ' danger' : '';

            const html = `
                <div id="confirmPopupOverlay" class="active">
                    <div class="confirm-popup">
                        <div class="confirm-popup-icon${iconVariantClass}">
                            <i class="${icon}"></i>
                        </div>
                        <h3>${escapeHtml(title)}</h3>
                        <p>${escapeHtml(message)}</p>
                        <div class="confirm-popup-actions">
                            <button class="confirm-popup-btn-cancel" id="confirmPopupCancel">${escapeHtml(cancelText)}</button>
                            <button class="confirm-popup-btn-confirm${btnClass}" id="confirmPopupOk">${escapeHtml(confirmText)}</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', html);

            const overlay = document.getElementById('confirmPopupOverlay');
            const btnOk  = document.getElementById('confirmPopupOk');
            const btnCancel = document.getElementById('confirmPopupCancel');

            function cleanup() {
                overlay.classList.remove('active');
                // Brief delay for fade-out animation feel
                setTimeout(function () {
                    if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                }, 150);
            }

            btnOk.addEventListener('click', function () {
                cleanup();
                resolve(true);
            });

            btnCancel.addEventListener('click', function () {
                cleanup();
                resolve(false);
            });

            // Cerrar al hacer clic fuera del popup
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    cleanup();
                    resolve(false);
                }
            });

            // Cerrar con Escape
            function onKeyDown(e) {
                if (e.key === 'Escape') {
                    cleanup();
                    resolve(false);
                    document.removeEventListener('keydown', onKeyDown);
                }
            }
            document.addEventListener('keydown', onKeyDown);
        });
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

})();
