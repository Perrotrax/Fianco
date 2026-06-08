// ========================================
// Expense Manager Application
// ========================================

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
    let descripcion =
    document.getElementById(
    "descripcion").value;

    let monto =
    parseFloat(
    document.getElementById(
    "monto").value);

    saldo -= monto;

    document.getElementById(
    "saldo").innerHTML =
    "$" + saldo;

    document.getElementById(
    "movimientos").innerHTML +=

    `
    <div class="movimiento">

        <span>
        ${descripcion}
        </span>

        <span>
        -$${monto}
        </span>

    </div>
    `;
}

// ========================================
// Login Functions
// ========================================

// Login functionality placeholder
function login() {
    // Add login logic here
}

// ========================================
// Registration Functions
// ========================================

function registrar(){

    let usuario = {

        nombre:
        document.getElementById("nombre").value,

        correo:
        document.getElementById("correo").value,

        password:
        document.getElementById("password").value
    };

    localStorage.setItem(
        "usuario",
        JSON.stringify(usuario)
    );

    alert("Usuario registrado");

    location.href="index.html";
}
