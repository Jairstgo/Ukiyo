<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$exito = $_GET['exito'] ?? 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['nueva_categoria'])) {
        $nombre = trim($_POST['nombre_categoria']);
        if (empty($nombre)) {
            $error = 'El nombre de la categoria es obligatorio.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO categorias (nombre) VALUES (?)");
            mysqli_stmt_bind_param($stmt, 's', $nombre);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: categorias.php?exito=1');
                exit();
            } else {
                $error = 'Esa categoria ya existe.';
            }
        }
    }

    if (isset($_POST['nueva_subcategoria'])) {
        $nombre = trim($_POST['nombre_subcategoria']);
        $idCategoria = $_POST['idCategoria'];
        $permite_toppings = isset($_POST['permite_toppings']) ? 1 : 0;
        if (empty($nombre) || empty($idCategoria)) {
            $error = 'Nombre y categoria son obligatorios.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO subcategorias (idCategoria, nombre, permite_toppings) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isi', $idCategoria, $nombre, $permite_toppings);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: categorias.php?exito=2');
                exit();
            } else {
                $error = 'Error al registrar la subcategoria.';
            }
        }
    }
}

$categorias = mysqli_query($conn, "SELECT * FROM categorias ORDER BY nombre");
$subcategorias = mysqli_query($conn, "SELECT s.*, c.nombre as categoria FROM subcategorias s JOIN categorias c ON s.idCategoria = c.idCategoria ORDER BY c.nombre, s.nombre");
$categorias_select = mysqli_query($conn, "SELECT * FROM categorias ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Categorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../estilos.css">
</head>
<body>

<nav class="navbar-ukiyo">
    <a class="navbar-brand-ukiyo" href="../index.php">
        <div class="brand-icon"><img src="../assets/logo.png" alt="Ukiyo" style="width:36px; height:36px; object-fit:contain;"></div>
        <div class="brand-text">
            <span class="brand-name">UKIYO</span>
            <span class="brand-sub">Restaurante Japonés</span>
        </div>
    </a>
    <div class="nav-right">
        <div class="usuario-badge">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?></div>
            <span class="d-none d-md-inline"><?php echo $_SESSION['nombre']; ?></span>
        </div>
        <a href="../cerrarSesion.php" class="btn-salir"><i class="bi bi-box-arrow-right"></i> Salir</a>
    </div>
</nav>

<div class="inner-content">
    <div class="section-header">
        <a href="listar.php" class="btn-back"><i class="bi bi-arrow-left"></i> Platillos</a>
        <span class="section-title">Categorías y subcategorías</span>
    </div>
    <div class="divider-rojo"></div>

    <?php if ($exito == 1): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle"></i> Categoría registrada correctamente.</div>
    <?php elseif ($exito == 2): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle"></i> Subcategoría registrada correctamente.</div>
    <?php elseif ($exito == 3): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle"></i> Eliminado correctamente.</div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-md-6">
            <div class="card-ukiyo">
                <div class="card-header-ukiyo"><i class="bi bi-plus-circle"></i> Nueva categoría</div>
                <div class="card-body-ukiyo">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la categoría</label>
                            <input type="text" name="nombre_categoria" class="form-control" placeholder="Ej: Entradas, Sushi, Bebidas..." required>
                        </div>
                        <button type="submit" name="nueva_categoria" class="btn-ukiyo"><i class="bi bi-plus-lg"></i> Agregar categoría</button>
                    </form>
                </div>
            </div>

            <div class="card-ukiyo mt-4">
                <div class="card-header-ukiyo"><i class="bi bi-list-ul"></i> Categorías registradas</div>
                <div class="card-body-ukiyo">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tablaCategorias">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($cat = mysqli_fetch_assoc($categorias)): ?>
                                <tr>
                                    <td><?php echo $cat['idCategoria']; ?></td>
                                    <td><?php echo $cat['nombre']; ?></td>
                                    <td>
                                        <button class="btn-accion btn-eliminar" onclick="eliminarCategoria(<?php echo $cat['idCategoria']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-ukiyo">
                <div class="card-header-ukiyo"><i class="bi bi-plus-circle"></i> Nueva subcategoría</div>
                <div class="card-body-ukiyo">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="idCategoria" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php while ($cat = mysqli_fetch_assoc($categorias_select)): ?>
                                <option value="<?php echo $cat['idCategoria']; ?>"><?php echo $cat['nombre']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de la subcategoría</label>
                            <input type="text" name="nombre_subcategoria" class="form-control" placeholder="Ej: Rollos clásicos, Yakisoba..." required>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="permite_toppings" id="permiteToppingsNuevo">
                            <label class="form-check-label" for="permiteToppingsNuevo" style="font-size:13px;">Permite toppings extra</label>
                        </div>
                        <button type="submit" name="nueva_subcategoria" class="btn-ukiyo"><i class="bi bi-plus-lg"></i> Agregar subcategoría</button>
                    </form>
                </div>
            </div>

            <div class="card-ukiyo mt-4">
                <div class="card-header-ukiyo"><i class="bi bi-list-ul"></i> Subcategorías registradas</div>
                <div class="card-body-ukiyo">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tablaSubcategorias">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subcategoría</th>
                                    <th>Categoría</th>
                                    <th>Toppings</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($sub = mysqli_fetch_assoc($subcategorias)): ?>
                                <tr>
                                    <td><?php echo $sub['idSubcategoria']; ?></td>
                                    <td><?php echo $sub['nombre']; ?></td>
                                    <td><?php echo $sub['categoria']; ?></td>
                                    <td>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" <?php echo $sub['permite_toppings'] ? 'checked' : ''; ?> onchange="toggleToppings(<?php echo $sub['idSubcategoria']; ?>, this)">
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn-accion btn-eliminar" onclick="eliminarSubcategoria(<?php echo $sub['idSubcategoria']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#tablaCategorias').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }, pageLength: 5, searching: false });
    $('#tablaSubcategorias').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }, pageLength: 5, searching: false });
});

function eliminarCategoria(id) {
    Swal.fire({
        title: 'Eliminar categoria',
        text: 'Se eliminaran tambien sus subcategorias y platillos asociados.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C0392B',
        cancelButtonColor: '#555',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: 'eliminarCategoria.php',
                data: { id },
                dataType: 'json',
                success: function(r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje, confirmButtonColor: '#C0392B' });
                    }
                }
            });
        }
    });
}

function toggleToppings(id, checkbox) {
    const valor = checkbox.checked ? 1 : 0;
    $.ajax({
        type: 'POST',
        url: 'permitetoppings.php',
        data: { id, valor },
        dataType: 'json',
        success: function(r) {
            if (!r.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje, confirmButtonColor: '#C0392B' });
                checkbox.checked = !checkbox.checked;
            }
        }
    });
}

function eliminarSubcategoria(id) {
    Swal.fire({
        title: 'Eliminar subcategoria',
        text: 'Esta accion no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C0392B',
        cancelButtonColor: '#555',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: 'eliminarSubcategoria.php',
                data: { id },
                dataType: 'json',
                success: function(r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje, confirmButtonColor: '#C0392B' });
                    }
                }
            });
        }
    });
}
</script>
</body>
</html>