let carrito = [];
let modalActual = {};
let modalBS;

document.addEventListener('DOMContentLoaded', function() {
    modalBS = new bootstrap.Modal(document.getElementById('modalPlatillo'));
});

function filtrar(cat, btn) {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.platillo-card').forEach(card => {
        if (cat === 'todos' || card.dataset.categoria === cat) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function abrirModal(card) {
    modalActual = {
        nombre: card.dataset.nombre,
        precio: parseFloat(card.dataset.precio),
        subcat: card.dataset.subcat,
        emoji: card.querySelector('.platillo-emoji').textContent
    };
    document.getElementById('modalNombre').textContent = modalActual.nombre;
    document.getElementById('modalSubcat').textContent = modalActual.subcat || card.dataset.categoria;
    document.getElementById('modalPrecio').textContent = '$' + modalActual.precio.toFixed(2);
    document.getElementById('modalEmoji').textContent = modalActual.emoji;
    document.getElementById('modalCantidad').textContent = '1';
    modalBS.show();
}

function cambiarCantidad(delta) {
    let el = document.getElementById('modalCantidad');
    let val = parseInt(el.textContent) + delta;
    if (val < 1) val = 1;
    el.textContent = val;
}

function agregarAlCarrito() {
    const cantidad = parseInt(document.getElementById('modalCantidad').textContent);
    const existente = carrito.find(i => i.nombre === modalActual.nombre);
    if (existente) {
        existente.cantidad += cantidad;
    } else {
        carrito.push({ ...modalActual, cantidad });
    }
    modalBS.hide();
    renderCarrito();

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: modalActual.nombre + ' agregado',
        showConfirmButton: false,
        timer: 1500
    });
}

function renderCarrito() {
    const container = document.getElementById('carritoItems');
    const vacio = document.getElementById('carritoVacio');
    const count = document.getElementById('carritoCount');
    const total = document.getElementById('totalCarrito');

    if (carrito.length === 0) {
        container.innerHTML = '';
        container.appendChild(vacio);
        vacio.style.display = 'flex';
        count.textContent = '0';
        total.textContent = '$0.00';
        return;
    }

    vacio.style.display = 'none';
    let html = '';
    let suma = 0;
    let totalItems = 0;

    carrito.forEach((item, i) => {
        const subtotal = item.precio * item.cantidad;
        suma += subtotal;
        totalItems += item.cantidad;
        html += `
        <div class="carrito-item">
            <div class="ci-emoji">${item.emoji}</div>
            <div class="ci-info">
                <div class="ci-nombre">${item.nombre}</div>
                <div class="ci-precio">$${item.precio.toFixed(2)} c/u</div>
            </div>
            <div class="ci-controles">
                <button onclick="ajustarCantidad(${i}, -1)">-</button>
                <span>${item.cantidad}</span>
                <button onclick="ajustarCantidad(${i}, 1)">+</button>
            </div>
            <div class="ci-subtotal">$${subtotal.toFixed(2)}</div>
            <button class="ci-quitar" onclick="quitarItem(${i})"><i class="bi bi-x"></i></button>
        </div>`;
    });

    container.innerHTML = html;
    count.textContent = totalItems;
    total.textContent = '$' + suma.toFixed(2);
}

function ajustarCantidad(i, delta) {
    carrito[i].cantidad += delta;
    if (carrito[i].cantidad <= 0) carrito.splice(i, 1);
    renderCarrito();
}

function quitarItem(i) {
    carrito.splice(i, 1);
    renderCarrito();
}

function toggleCampos() {
    const tipo = document.getElementById('tipoServicio').value;
    const camposCliente = document.getElementById('camposCliente');
    const camposDomicilio = document.getElementById('camposDomicilio');
    if (tipo === 'local') {
        camposCliente.style.display = 'none';
        camposDomicilio.style.display = 'none';
    } else {
        camposCliente.style.display = 'flex';
        camposDomicilio.style.display = tipo === 'domicilio' ? 'flex' : 'none';
    }
}

function limpiarCarrito() {
    Swal.fire({
        title: 'Cancelar pedido',
        text: 'Se eliminaran todos los platillos del pedido actual.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C0392B',
        cancelButtonColor: '#555',
        confirmButtonText: 'Si, cancelar',
        cancelButtonText: 'Volver'
    }).then(result => {
        if (result.isConfirmed) {
            carrito = [];
            renderCarrito();
            const nc = document.getElementById('nombreCliente');
            const dir = document.getElementById('direccion');
            const tel = document.getElementById('telefono');
            if (nc) nc.value = '';
            if (dir) dir.value = '';
            if (tel) tel.value = '';
        }
    });
}

function registrarPedido() {
    const tipo = document.getElementById('tipoServicio').value;
    const nombre = tipo !== 'local' ? document.getElementById('nombreCliente').value.trim() : 'Local';
    const pago = tipo !== 'local' ? document.getElementById('metodoPago').value : 'efectivo';

    if (tipo !== 'local' && !nombre) {
        Swal.fire({ icon: 'warning', title: 'Falta el nombre del cliente', confirmButtonColor: '#C0392B' });
        return;
    }
    if (carrito.length === 0) {
        Swal.fire({ icon: 'warning', title: 'El pedido esta vacio', confirmButtonColor: '#C0392B' });
        return;
    }

    const ahora = new Date();
    const fecha = ahora.toLocaleDateString('es-MX');
    const hora = ahora.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    const folio = 'UK-' + String(Math.floor(Math.random() * 9000) + 1000);
    const direccion = document.getElementById('direccion') ? document.getElementById('direccion').value : '';
    const telefono = document.getElementById('telefono') ? document.getElementById('telefono').value : '';

    let totalSuma = 0;
    let itemsHtml = '';
    carrito.forEach(item => {
        const sub = item.precio * item.cantidad;
        totalSuma += sub;
        itemsHtml += `
        <div class="ticket-item">
            <span>${item.emoji} ${item.nombre} x${item.cantidad}</span>
            <span>$${sub.toFixed(2)}</span>
        </div>`;
    });

    const tipoLabels = { local: 'Local', llevar: 'Para llevar', domicilio: 'Domicilio' };
    const pagoLabels = { efectivo: 'Efectivo', transferencia: 'Transferencia' };

    let domicilioHtml = '';
    if (tipo === 'domicilio' && direccion) {
        domicilioHtml = `<div class="ticket-dato"><b>Direccion:</b> ${direccion}</div>`;
        if (telefono) domicilioHtml += `<div class="ticket-dato"><b>Tel:</b> ${telefono}</div>`;
    }

    document.getElementById('ticketContenido').innerHTML = `
        <div class="ticket-dato"><b>Folio:</b> ${folio}</div>
        <div class="ticket-dato"><b>Fecha:</b> ${fecha} ${hora}</div>
        ${tipo !== 'local' ? `<div class="ticket-dato"><b>Cliente:</b> ${nombre}</div>` : ''}
        <div class="ticket-dato"><b>Tipo:</b> ${tipoLabels[tipo]}</div>
        ${tipo !== 'local' ? `<div class="ticket-dato"><b>Pago:</b> ${pagoLabels[pago]}</div>` : '<div class="ticket-dato"><b>Pago:</b> En caja</div>'}
        ${domicilioHtml}
        <div class="ticket-divider"></div>
        ${itemsHtml}
        <div class="ticket-divider"></div>
        <div class="ticket-total">
            <span>TOTAL</span>
            <span>$${totalSuma.toFixed(2)}</span>
        </div>`;

    document.getElementById('ticketOverlay').style.display = 'flex';
}

function cerrarTicket() {
    document.getElementById('ticketOverlay').style.display = 'none';
    carrito = [];
    renderCarrito();
    const nc = document.getElementById('nombreCliente');
    const dir = document.getElementById('direccion');
    const tel = document.getElementById('telefono');
    if (nc) nc.value = '';
    if (dir) dir.value = '';
    if (tel) tel.value = '';
    document.getElementById('tipoServicio').value = 'local';
    document.getElementById('camposCliente').style.display = 'none';
    document.getElementById('camposDomicilio').style.display = 'none';
}