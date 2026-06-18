<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header('Location: login.php');
    exit();
}

$categorias = mysqli_query($conn, "SELECT * FROM categorias ORDER BY nombre");
$platillos = mysqli_query($conn, "SELECT p.*, c.nombre as categoria, s.nombre as subcategoria, COALESCE(s.permite_toppings, 0) as permite_toppings FROM platillos p JOIN categorias c ON p.idCategoria = c.idCategoria LEFT JOIN subcategorias s ON p.idSubcategoria = s.idSubcategoria WHERE p.disponible = 1 ORDER BY c.nombre, p.nombre");

$toppings_query = mysqli_query($conn, "SELECT * FROM toppings WHERE disponible = 1 ORDER BY nombre");
$lista_toppings = [];
while ($t = mysqli_fetch_assoc($toppings_query)) {
    $lista_toppings[] = $t;
}

$subcats_query = mysqli_query($conn, "SELECT s.*, c.nombre as categoria FROM subcategorias s JOIN categorias c ON s.idCategoria = c.idCategoria ORDER BY c.nombre, s.nombre");
$subcategorias_por_categoria = [];
while ($s = mysqli_fetch_assoc($subcats_query)) {
    $subcategorias_por_categoria[$s['categoria']][] = $s['nombre'];
}

$lista_platillos = [];
while ($p = mysqli_fetch_assoc($platillos)) {
    $lista_platillos[] = $p;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Panel Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos_empleado.css">
</head>
<body>

<nav class="navbar-ukiyo d-flex align-items-center justify-content-between px-3 px-md-4">
    <a class="navbar-brand-ukiyo" href="#">
        <div class="brand-icon"><img src="assets/logo.png" alt="Ukiyo" style="width:36px; height:36px; object-fit:contain;"></div>
        <div class="brand-text">
            <span class="brand-name">UKIYO</span>
            <span class="brand-sub">Restaurante Japonés</span>
        </div>
    </a>
    <div class="d-flex align-items-center gap-3">
        <div class="usuario-badge">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?></div>
            <span class="d-none d-md-inline" style="color:#ccc; font-size:13px;"><?php echo $_SESSION['nombre']; ?></span>
        </div>
        <a href="cerrarSesion.php" class="btn-salir"><i class="bi bi-box-arrow-right"></i> Salir</a>
    </div>
</nav>

<div class="emp-layout">

    <div class="catalogo-panel">

        <div class="filtros-bar">
            <button class="filtro-btn active" onclick="filtrar('todos', this)">Todos</button>
            <?php mysqli_data_seek($categorias, 0); while ($cat = mysqli_fetch_assoc($categorias)): ?>
            <button class="filtro-btn" onclick="filtrar('<?php echo $cat['nombre']; ?>', this)"><?php echo $cat['nombre']; ?></button>
            <?php endwhile; ?>
        </div>

        <div class="subfiltros-bar" id="subfiltrosBar" style="display:none;">
            <button class="subfiltro-btn active" onclick="filtrarSub('todos', this)">Todos</button>
            <?php foreach ($subcategorias_por_categoria as $catNombre => $subs): ?>
                <?php foreach ($subs as $subNombre): ?>
                <button class="subfiltro-btn" data-cat="<?php echo $catNombre; ?>" onclick="filtrarSub('<?php echo $subNombre; ?>', this)" style="display:none;"><?php echo $subNombre; ?></button>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <div class="platillos-grid" id="platillosGrid">
            <?php foreach ($lista_platillos as $p): ?>
            <div class="platillo-card"
                data-categoria="<?php echo $p['categoria']; ?>"
                data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>"
                data-precio="<?php echo $p['precio']; ?>"
                data-id="<?php echo $p['idPlatillo']; ?>"
                data-subcat="<?php echo $p['subcategoria'] ?? ''; ?>"
                data-imagen="<?php echo $p['imagen'] ?? ''; ?>"
                data-toppings="<?php echo $p['permite_toppings']; ?>"
                onclick="abrirModal(this)">
                <div class="platillo-cat-tag"><?php echo $p['subcategoria'] ?? $p['categoria']; ?></div>
                <?php if (!empty($p['imagen'])): ?>
                    <img src="uploads/platillos/<?php echo $p['imagen']; ?>" class="platillo-img" alt="<?php echo $p['nombre']; ?>">
                <?php else: ?>
                    <div class="platillo-img-placeholder"><i class="bi bi-egg-fried"></i></div>
                <?php endif; ?>
                <div class="platillo-nombre"><?php echo $p['nombre']; ?></div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($lista_platillos)): ?>
            <div style="grid-column: 1/-1; text-align:center; padding:40px; color:#aaa;">
                <i class="bi bi-egg-fried" style="font-size:40px;"></i>
                <p style="margin-top:12px;">No hay platillos disponibles</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="carrito-panel">
        <div class="carrito-header">
            <i class="bi bi-receipt"></i> Pedido actual
            <span class="carrito-count" id="carritoCount">0</span>
        </div>

        <div class="carrito-datos">
            <select class="carrito-select" id="tipoServicio" onchange="toggleCampos()">
                <option value="local">Local</option>
                <option value="llevar">Para llevar</option>
                <option value="domicilio">Domicilio</option>
            </select>
            <div id="camposCliente" style="display:none; flex-direction:column; gap:8px;">
                <input type="text" class="carrito-input" id="nombreCliente" placeholder="Nombre del cliente">
                <select class="carrito-select" id="metodoPago">
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia</option>
                </select>
                <div id="camposDomicilio" style="display:none; flex-direction:column; gap:8px;">
                    <input type="text" class="carrito-input" id="direccion" placeholder="Direccion de entrega">
                    <input type="text" class="carrito-input" id="telefono" placeholder="Telefono de contacto">
                </div>
            </div>
        </div>

        <div class="carrito-items" id="carritoItems">
            <div class="carrito-vacio" id="carritoVacio">
                <i class="bi bi-basket" style="font-size:32px; color:#ccc;"></i>
                <p style="color:#aaa; font-size:13px; margin-top:8px;">Sin platillos aun</p>
            </div>
        </div>

        <div class="carrito-footer">
            <div class="carrito-total">
                <span>Total</span>
                <span id="totalCarrito">$0.00</span>
            </div>
            <button class="btn-registrar" onclick="registrarPedido()">
                <i class="bi bi-printer"></i> Registrar y generar ticket
            </button>
            <button class="btn-limpiar" onclick="limpiarCarrito()">
                <i class="bi bi-trash"></i> Cancelar pedido
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPlatillo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-ukiyo">
            <div class="modal-header-ukiyo">
                <div id="modalImagenWrap" style="width:56px; height:56px; border-radius:10px; overflow:hidden; flex-shrink:0; background:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                    <img id="modalImagen" src="" alt="" style="width:100%; height:100%; object-fit:cover; display:none;">
                    <i class="bi bi-egg-fried" id="modalIcono" style="font-size:28px; color:#ccc;"></i>
                </div>
                <div>
                    <div class="modal-nombre" id="modalNombre"></div>
                    <div class="modal-subcat" id="modalSubcat"></div>
                </div>
                <button type="button" class="btn-close-ukiyo" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span style="font-size:22px; font-weight:800; color:#C0392B;" id="modalPrecio"></span>
                    <div class="cantidad-control">
                        <button onclick="cambiarCantidad(-1)">-</button>
                        <span id="modalCantidad">1</span>
                        <button onclick="cambiarCantidad(1)">+</button>
                    </div>
                </div>

                <div id="modalToppingsSection" style="display:none;">
                    <label class="form-label" style="font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#888; font-weight:700;">Toppings extra</label>
                    <div id="modalToppingsList" class="mb-3"></div>
                </div>

                <button class="btn-agregar-carrito" onclick="agregarAlCarrito()">
                    <i class="bi bi-plus-circle"></i> Agregar al pedido
                </button>
            </div>
        </div>
    </div>
</div>

<div id="ticketOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center;">
    <div class="ticket-container">
        <div class="ticket-header">
            <div style="font-size:22px; font-weight:900; letter-spacing:2px;">UKIYO</div>
            <div style="font-size:11px; color:#777; letter-spacing:1px;">RESTAURANTE JAPONÉS</div>
            <div class="ticket-divider"></div>
        </div>
        <div id="ticketContenido"></div>
        <div class="ticket-divider"></div>
        <div style="text-align:center; font-size:11px; color:#888; margin-top:8px;">Gracias por su visita</div>
        <div style="text-align:center; font-size:10px; color:#bbb; margin-top:4px;">Ukiyo - Restaurante Japonés</div>
        <div class="ticket-acciones">
            <button class="btn-ticket-imprimir" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
            <button class="btn-ticket-cerrar" onclick="cerrarTicket()"><i class="bi bi-x"></i> Cerrar</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
const TOPPINGS_DISPONIBLES = <?php echo json_encode($lista_toppings); ?>;
</script>
<script src="empleado.js"></script>

</body>
</html>