/**
 * EJEMPLOS DE USO - Diseño Moderno & SweetAlerts
 * Gestor de Gastos v2.0
 */

// ========================================
// EJEMPLO 1: Alertas Básicas
// ========================================

// Mostrar mensaje de éxito
showSuccess('¡Operación Exitosa!', 'El gasto fue registrado correctamente');

// Mostrar error
showError('Error al Guardar', 'Por favor verifica los datos ingresados');

// Mostrar advertencia
showWarning('Confirmar Eliminación', 'Esta acción no se puede deshacer');

// Mostrar información
showInfo('Información', 'Tu presupuesto mensual es de $5,000');


// ========================================
// EJEMPLO 2: Confirmar Acciones
// ========================================

// Eliminar gasto
confirmAction(
    '¿Eliminar gasto?',
    'El gasto será eliminado permanentemente',
    () => {
        // Código para eliminar
        fetch('api/delete_gasto.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Eliminado', 'El gasto fue eliminado');
                } else {
                    showError('Error', data.message);
                }
            });
    }
);


// ========================================
// EJEMPLO 3: Validar Formulario
// ========================================

function guardarGasto() {
    const descripcion = document.getElementById('descripcion').value.trim();
    const monto = document.getElementById('monto').value.trim();
    const categoria = document.getElementById('categoria').value;

    // Validaciones
    if (!descripcion) {
        showWarning('Campo Requerido', 'Por favor ingresa una descripción');
        return;
    }

    if (!monto || parseFloat(monto) <= 0) {
        showWarning('Monto Inválido', 'Ingresa un monto válido mayor a 0');
        return;
    }

    if (!categoria) {
        showWarning('Categoría Requerida', 'Selecciona una categoría');
        return;
    }

    // Si todas las validaciones pasan
    enviarGasto(descripcion, monto, categoria);
}


// ========================================
// EJEMPLO 4: Petición a API con Loading
// ========================================

async function traerGastos() {
    // Mostrar loading
    Swal.fire({
        title: 'Cargando gastos...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await fetch('api/get_gastos.php');
        const data = await response.json();

        Swal.close();

        if (data.success) {
            console.log(data.gastos);
            // Procesar gastos
        } else {
            showError('Error', 'No se pudieron cargar los gastos');
        }
    } catch (error) {
        Swal.close();
        showError('Error de Conexión', 'No se pudo conectar con el servidor');
        console.error(error);
    }
}


// ========================================
// EJEMPLO 5: Registro de Usuario
// ========================================

async function registroCompleto() {
    const nombre = document.getElementById('nombre').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const password = document.getElementById('password').value.trim();

    // Validaciones
    if (!nombre) {
        showWarning('Nombre Requerido', 'Por favor ingresa tu nombre');
        return;
    }

    if (!validarEmail(correo)) {
        showWarning('Email Inválido', 'Por favor ingresa un email válido');
        return;
    }

    if (!validarPassword(password)) {
        showWarning('Contraseña Débil', 'La contraseña debe tener al menos 6 caracteres');
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Registrando usuario...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await fetch('api/registro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, correo, password })
        });

        const data = await response.json();

        if (data.success) {
            Swal.close();
            showSuccess('¡Registro Exitoso!', 'Tu cuenta ha sido creada');
            setTimeout(() => {
                window.location = 'index.php';
            }, 2000);
        } else {
            Swal.close();
            showError('Error en Registro', data.message);
        }
    } catch (error) {
        Swal.close();
        showError('Error de Conexión', 'No se pudo completar el registro');
    }
}


// ========================================
// EJEMPLO 6: Formulario Dinámico con SweetAlert
// ========================================

async function editarPerfil() {
    const { value: nombre } = await Swal.fire({
        title: 'Editar Nombre',
        input: 'text',
        inputLabel: 'Ingresa tu nombre',
        inputValue: document.getElementById('nombreActual').textContent,
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) {
                return 'El nombre es requerido';
            }
        },
        customClass: {
            popup: 'swal-popup-custom',
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary',
        }
    });

    if (nombre) {
        // Guardar cambios
        const response = await fetch('api/update_perfil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre })
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Perfil Actualizado', 'Tu nombre fue actualizado correctamente');
            document.getElementById('nombreActual').textContent = nombre;
        } else {
            showError('Error', 'No se pudo actualizar el perfil');
        }
    }
}


// ========================================
// EJEMPLO 7: Múltiples Pasos (Wizard)
// ========================================

async function crearProyectoCompleto() {
    const steps = ['datos', 'presupuesto', 'confirmación'];
    let currentStep = 0;

    const datosProyecto = {};

    // Paso 1: Datos
    const { value: nombre } = await Swal.fire({
        title: 'Paso 1: Datos del Proyecto',
        input: 'text',
        inputPlaceholder: 'Nombre del proyecto',
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) return 'El nombre es requerido';
        }
    });

    if (!nombre) return;
    datosProyecto.nombre = nombre;

    // Paso 2: Presupuesto
    const { value: presupuesto } = await Swal.fire({
        title: 'Paso 2: Presupuesto',
        input: 'number',
        inputPlaceholder: 'Monto presupuestado',
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value || parseFloat(value) <= 0) return 'Ingresa un monto válido';
        }
    });

    if (!presupuesto) return;
    datosProyecto.presupuesto = presupuesto;

    // Paso 3: Confirmación
    const { isConfirmed } = await Swal.fire({
        title: 'Confirmación',
        html: `
            <div style="text-align: left;">
                <p><strong>Nombre:</strong> ${datosProyecto.nombre}</p>
                <p><strong>Presupuesto:</strong> $${datosProyecto.presupuesto}</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Crear Proyecto',
        cancelButtonText: 'Cancelar'
    });

    if (isConfirmed) {
        // Guardar proyecto
        const response = await fetch('api/add_proyecto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosProyecto)
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('¡Proyecto Creado!', 'El proyecto fue creado exitosamente');
        }
    }
}


// ========================================
// EJEMPLO 8: Toast Notifications (Centro Inferior)
// ========================================

function mostrarToast(mensaje, tipo = 'info') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    const iconMap = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };

    Toast.fire({
        icon: iconMap[tipo] || 'info',
        title: mensaje
    });
}

// Uso:
// mostrarToast('Gasto agregado correctamente', 'success');
// mostrarToast('Error al guardar', 'error');


// ========================================
// EJEMPLO 9: Input con Validación Real Time
// ========================================

async function solicitarMonto() {
    const { value: monto } = await Swal.fire({
        title: 'Ingresa el Monto',
        input: 'number',
        inputPlaceholder: '0.00',
        inputAttributes: {
            min: 0,
            step: 0.01
        },
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) return 'Debes ingresar un monto';
            if (parseFloat(value) < 0) return 'El monto no puede ser negativo';
            if (parseFloat(value) > 999999) return 'El monto es demasiado grande';
        },
        customClass: {
            input: 'swal-input-custom'
        }
    });

    if (monto) {
        console.log(`Monto ingresado: $${monto}`);
    }
}


// ========================================
// EJEMPLO 10: Mostrar Lista de Items
// ========================================

async function seleccionarGasto() {
    const { value: gastoSeleccionado } = await Swal.fire({
        title: 'Selecciona un Gasto',
        input: 'select',
        inputOptions: {
            'group1': {
                'food': 'Comida',
                'transport': 'Transporte',
                'entertainment': 'Entretenimiento'
            },
            'group2': {
                'utilities': 'Servicios',
                'health': 'Salud',
                'education': 'Educación'
            }
        },
        inputPlaceholder: 'Selecciona una categoría',
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) return 'Debe seleccionar una categoría';
        }
    });

    if (gastoSeleccionado) {
        console.log(`Categoría seleccionada: ${gastoSeleccionado}`);
    }
}


// ========================================
// EJEMPLO 11: Cargar Imagen
// ========================================

async function subirFoto() {
    const { value: file } = await Swal.fire({
        title: 'Cargar Foto de Perfil',
        input: 'file',
        inputAttributes: {
            accept: 'image/jpeg, image/png, image/gif',
            'aria-label': 'Selecciona tu foto de perfil'
        },
        showCancelButton: true
    });

    if (file) {
        const formData = new FormData();
        formData.append('foto', file);

        const response = await fetch('api/upload_foto.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Foto Subida', 'Tu foto de perfil fue actualizada');
        } else {
            showError('Error', 'No se pudo subir la foto');
        }
    }
}


// ========================================
// EJEMPLO 12: Mostrar Loading y Cerrar
// ========================================

async function procesarPago() {
    Swal.fire({
        title: 'Procesando Pago...',
        html: 'Por favor espera mientras procesamos tu pago',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Simular espera
    await new Promise(resolve => setTimeout(resolve, 3000));

    Swal.close();
    showSuccess('Pago Procesado', 'Tu pago fue procesado exitosamente');
}


// ========================================
// USO EN HTML
// ========================================

/*
<!-- Botón que dispara la acción -->
<button class="btn btn-primary" onclick="guardarGasto()">
    Guardar Gasto
</button>

<!-- Botón peligroso -->
<button class="btn btn-danger" onclick="confirmAction(
    '¿Eliminar?',
    'Esta acción no se puede deshacer',
    () => {
        console.log('Eliminado');
    }
)">
    Eliminar
</button>

<!-- Toast notification -->
<button class="btn btn-secondary" onclick="mostrarToast('Operación completada', 'success')">
    Mostrar Toast
</button>
*/

console.log('✅ Ejemplos de uso cargados correctamente');
console.log('Funciones disponibles:');
console.log('- showSuccess(title, message, timer)');
console.log('- showError(title, message)');
console.log('- showWarning(title, message)');
console.log('- showInfo(title, message)');
console.log('- confirmAction(title, message, callback)');
console.log('- validarEmail(email)');
console.log('- validarPassword(password)');
console.log('- mostrarToast(mensaje, tipo)');
