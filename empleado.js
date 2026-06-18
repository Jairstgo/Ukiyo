let carrito = [];
let modalActual = {};
let modalBS;

document.addEventListener('DOMContentLoaded', function() {
    modalBS = new bootstrap.Modal(document.getElementById('modalPlatillo'));
});

let categoriaActual = 'todos';

function filtrar(cat, btn) {
    categoriaActual = cat;
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.platillo-card').forEach(card => {
        if (cat === 'todos' || card.dataset.categoria === cat) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    const subBar = document.getElementById('subfiltrosBar');
    const subBotones = document.querySelectorAll('.subfiltro-btn[data-cat]');
    let tieneSubcats = false;

    subBotones.forEach(b => {
        if (cat !== 'todos' && b.dataset.cat === cat) {
            b.style.display = '';
            tieneSubcats = true;
        } else {
            b.style.display = 'none';
        }
    });

    const btnTodosSub = subBar.querySelector('.subfiltro-btn:not([data-cat])');

    if (tieneSubcats) {
        subBar.style.display = 'flex';
        document.querySelectorAll('.subfiltro-btn').forEach(b => b.classList.remove('active'));
        btnTodosSub.classList.add('active');
    } else {
        subBar.style.display = 'none';
    }
}

function filtrarSub(subcat, btn) {
    document.querySelectorAll('.subfiltro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.platillo-card').forEach(card => {
        const perteneceCategoria = categoriaActual === 'todos' || card.dataset.categoria === categoriaActual;
        const perteneceSubcat = subcat === 'todos' || card.dataset.subcat === subcat;

        if (perteneceCategoria && perteneceSubcat) {
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
        imagen: card.dataset.imagen || '',
        permiteToppings: card.dataset.toppings === '1'
    };
    document.getElementById('modalNombre').textContent = modalActual.nombre;
    document.getElementById('modalSubcat').textContent = modalActual.subcat || card.dataset.categoria;
    document.getElementById('modalPrecio').textContent = '$' + modalActual.precio.toFixed(2);
    document.getElementById('modalCantidad').textContent = '1';

    const imgEl = document.getElementById('modalImagen');
    const iconoEl = document.getElementById('modalIcono');
    if (modalActual.imagen) {
        imgEl.src = 'uploads/platillos/' + modalActual.imagen;
        imgEl.style.display = 'block';
        iconoEl.style.display = 'none';
    } else {
        imgEl.style.display = 'none';
        iconoEl.style.display = 'block';
    }

    const toppingsSection = document.getElementById('modalToppingsSection');
    const toppingsList = document.getElementById('modalToppingsList');

    if (modalActual.permiteToppings && TOPPINGS_DISPONIBLES.length > 0) {
        let html = '';
        TOPPINGS_DISPONIBLES.forEach(t => {
            html += `
            <div class="form-check d-flex justify-content-between align-items-center mb-2">
                <div>
                    <input class="form-check-input topping-check" type="checkbox" value="${t.idTopping}" data-nombre="${t.nombre}" data-precio="${t.precio}" id="top${t.idTopping}">
                    <label class="form-check-label" for="top${t.idTopping}" style="font-size:13px;">${t.nombre}</label>
                </div>
                <span style="font-size:12px; color:#C0392B; font-weight:700;">+$${parseFloat(t.precio).toFixed(2)}</span>
            </div>`;
        });
        toppingsList.innerHTML = html;
        toppingsSection.style.display = 'block';
    } else {
        toppingsList.innerHTML = '';
        toppingsSection.style.display = 'none';
    }

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

    const toppingsSeleccionados = [];
    document.querySelectorAll('.topping-check:checked').forEach(chk => {
        toppingsSeleccionados.push({
            id: chk.value,
            nombre: chk.dataset.nombre,
            precio: parseFloat(chk.dataset.precio)
        });
    });

    carrito.push({
        nombre: modalActual.nombre,
        precio: modalActual.precio,
        imagen: modalActual.imagen,
        cantidad,
        toppings: toppingsSeleccionados
    });

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

function precioUnitarioConToppings(item) {
    let extra = 0;
    if (item.toppings && item.toppings.length > 0) {
        item.toppings.forEach(t => extra += parseFloat(t.precio));
    }
    return item.precio + extra;
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
        const precioUnitario = precioUnitarioConToppings(item);
        const subtotal = precioUnitario * item.cantidad;
        suma += subtotal;
        totalItems += item.cantidad;

        let toppingsHtml = '';
        if (item.toppings && item.toppings.length > 0) {
            const nombres = item.toppings.map(t => t.nombre).join(', ');
            toppingsHtml = `<div class="ci-toppings">+ ${nombres}</div>`;
        }

        html += `
        <div class="carrito-item">
            <div class="ci-emoji">
                ${item.imagen ? `<img src="uploads/platillos/${item.imagen}" style="width:32px;height:32px;object-fit:cover;border-radius:6px;">` : '<i class="bi bi-egg-fried" style="font-size:20px;color:#ccc;"></i>'}
            </div>
            <div class="ci-info">
                <div class="ci-nombre">${item.nombre}</div>
                <div class="ci-precio">$${precioUnitario.toFixed(2)} c/u</div>
                ${toppingsHtml}
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
    container.appendChild(vacio); // re-insertar para que getElementById lo encuentre en la próxima llamada
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
        const precioUnitario = precioUnitarioConToppings(item);
        const sub = precioUnitario * item.cantidad;
        totalSuma += sub;

        let toppingsTicket = '';
        if (item.toppings && item.toppings.length > 0) {
            item.toppings.forEach(t => {
                toppingsTicket += `
        <div class="ticket-item" style="font-size:11px; color:#888;">
            <span>&nbsp;&nbsp;+ ${t.nombre}</span>
            <span>$${parseFloat(t.precio).toFixed(2)}</span>
        </div>`;
            });
        }

        itemsHtml += `
        <div class="ticket-item">
            <span>${item.nombre} x${item.cantidad}</span>
            <span>$${sub.toFixed(2)}</span>
        </div>${toppingsTicket}`;
    });

    const tipoLabels = { local: 'Local', llevar: 'Para llevar', domicilio: 'Domicilio' };
    const pagoLabels = { efectivo: 'Efectivo', transferencia: 'Transferencia' };

    let domicilioHtml = '';
    if (tipo === 'domicilio' && direccion) {
        domicilioHtml = `<div class="ticket-dato"><b>Direccion:</b> ${direccion}</div>`;
        if (telefono) domicilioHtml += `<div class="ticket-dato"><b>Tel:</b> ${telefono}</div>`;
    }

    document.getElementById('ticketContenido').innerHTML = `
        <div class="ticket-dato"><b>Folio:</b> <span id="ticketFolio">${folio}</span></div>
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

    // Guardar pedido en la BD y reemplazar folio temporal por el real
    $.ajax({
        url: 'registrarPedido.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            tipo: tipo,
            nombre_cliente: nombre,
            direccion: direccion,
            telefono: telefono,
            metodo_pago: pago,
            carrito: carrito
        }),
        success: function(resp) {
            if (resp.success) {
                const folioReal = 'UK-' + String(resp.idPedido).padStart(4, '0');
                document.getElementById('ticketFolio').textContent = folioReal;
            }
        }
    });
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