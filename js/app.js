// ========================================
// Expense Manager Application
// Con SweetAlert2 Integrado
// ========================================

// Cargar SweetAlert2 si no está cargado
if (typeof Swal === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js';
    document.head.appendChild(script);
    
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css';
    document.head.appendChild(link);
}

// ========================================
// Configuración de SweetAlert2
// ========================================
const swalConfig = {
    confirmButtonColor: '#6366f1',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Sí, continuar',
    cancelButtonText: 'Cancelar',
    customClass: {
        popup: 'swal-popup',
        confirmButton: 'swal-btn swal-btn-primary',
        cancelButton: 'swal-btn swal-btn-danger',
        title: 'swal-title',
        htmlContainer: 'swal-content'
    }
};

// ========================================
// Dashboard Functions
// ========================================

let saldo = 15000;

document.addEventListener('DOMContentLoaded', function() {
    const saldoElement = document.getElementById("saldo");
    if (saldoElement) {
        saldoElement.innerHTML = "$" + saldo;
    }
});

function agregarGasto(){
    let descripcion = document.getElementById("descripcion").value;
    let monto = parseFloat(document.getElementById("monto").value);

    // Validaciones
    if (!descripcion || descripcion.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingresa una descripción del gasto',
            ...swalConfig
        });
        return;
    }

    if (isNaN(monto) || monto <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'Por favor ingresa un monto válido',
            ...swalConfig
        });
        return;
    }

    if (monto > saldo) {
        Swal.fire({
            icon: 'error',
            title: 'Saldo insuficiente',
            text: `Tu saldo actual es $${saldo}. No puedes gastar más de lo que tienes.`,
            ...swalConfig
        });
        return;
    }

    saldo -= monto;

    document.getElementById("saldo").innerHTML = "$" + saldo;
    document.getElementById("movimientos").innerHTML += `
        <div class="movimiento">
            <span>${descripcion}</span>
            <span style="color: #ef4444;">-$${monto.toFixed(2)}</span>
        </div>
    `;

    // Limpiar formulario
    document.getElementById("descripcion").value = '';
    document.getElementById("monto").value = '';

    Swal.fire({
        icon: 'success',
        title: 'Gasto registrado',
        text: `Se registró un gasto de $${monto.toFixed(2)}`,
        timer: 1500,
        timerProgressBar: true,
        ...swalConfig
    });
}

// ========================================
// Login Functions
// ========================================

function login() {
    // Add login logic here
    console.log('Login function initialized');
}

// ========================================
// Registration Functions
// ========================================

function registrar(){
    let nombre = document.getElementById("nombre").value;
    let correo = document.getElementById("correo").value;
    let password = document.getElementById("password").value;

    // Validaciones
    if (!nombre || nombre.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Nombre requerido',
            text: 'Por favor ingresa tu nombre completo',
            ...swalConfig
        });
        return;
    }

    if (!correo || correo.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Correo requerido',
            text: 'Por favor ingresa tu correo electrónico',
            ...swalConfig
        });
        return;
    }

    if (!validarEmail(correo)) {
        Swal.fire({
            icon: 'warning',
            title: 'Correo inválido',
            text: 'Por favor ingresa un correo electrónico válido',
            ...swalConfig
        });
        return;
    }

    if (!password || password.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Contraseña requerida',
            text: 'Por favor ingresa una contraseña',
            ...swalConfig
        });
        return;
    }

    if (password.length < 6) {
        Swal.fire({
            icon: 'warning',
            title: 'Contraseña débil',
            text: 'La contraseña debe tener al menos 6 caracteres',
            ...swalConfig
        });
        return;
    }

    let usuario = {
        nombre: nombre,
        correo: correo,
        password: password
    };

    localStorage.setItem("usuario", JSON.stringify(usuario));

    Swal.fire({
        icon: 'success',
        title: 'Registro exitoso',
        text: '¡Tu cuenta ha sido creada correctamente!',
        timer: 2000,
        timerProgressBar: true,
        ...swalConfig
    }).then((result) => {
        if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
            location.href = "index.php";
        }
    });
}

// ========================================
// Funciones Utilitarias
// ========================================

function validarEmail(email) {
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regexEmail.test(email);
}

// Función para confirmar acciones
function confirmarAccion(titulo, mensaje, callback) {
    Swal.fire({
        icon: 'question',
        title: titulo,
        text: mensaje,
        showCancelButton: true,
        ...swalConfig
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

// Función para mostrar error
function mostrarError(titulo, mensaje) {
    Swal.fire({
        icon: 'error',
        title: titulo,
        text: mensaje,
        ...swalConfig
    });
}

// Función para mostrar éxito
function mostrarExito(titulo, mensaje) {
    Swal.fire({
        icon: 'success',
        title: titulo,
        text: mensaje,
        timer: 1500,
        timerProgressBar: true,
        ...swalConfig
    });
}

// Función para mostrar información
function mostrarInfo(titulo, mensaje) {
    Swal.fire({
        icon: 'info',
        title: titulo,
        text: mensaje,
        ...swalConfig
    });
}
