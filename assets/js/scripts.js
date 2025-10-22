/**
 * Scripts Globales - Sistema de Gestión de Compras y Proveedores
 */

// Función para toggle de submenús
function toggleSubmenu(id, element) {
    event.preventDefault();
    const submenu = document.getElementById('submenu-' + id);
    submenu.classList.toggle('show');

    if (submenu.classList.contains('show')) {
        element.classList.remove('collapsed');
        element.innerHTML = element.innerHTML.replace('▼', '▲');
    } else {
        element.classList.add('collapsed');
        element.innerHTML = element.innerHTML.replace('▲', '▼');
    }
}

// Función para formatear moneda
function formatearMoneda(monto) {
    return '$' + parseFloat(monto).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para confirmar eliminación
function confirmarEliminacion(mensaje) {
    return confirm(mensaje || '¿Está seguro de que desea eliminar este registro?');
}

// Validación de formularios
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('[required]');
    let valido = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#e74a3b';
            valido = false;
        } else {
            input.style.borderColor = '#d1d3e2';
        }
    });

    return valido;
}

// Auto-cerrar alertas después de 5 segundos
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Prevenir envío múltiple de formularios
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Procesando...';
            }
        });
    });
});