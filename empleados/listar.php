<?php
session_start();
include '../conexion.php';
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') { header('Location: ../login.php'); exit(); }

$exito = $_GET['exito'] ?? 0;
$empleados = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY idUsuario DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../estilos.css">
</head>
<body>
<nav class="navbar-ukiyo">
    <a class="navbar-brand-ukiyo" href="../index.php"><div class="brand-icon"><img src="../assets/logo.png" alt="Ukiyo" style="width:36px; height:36px; object-fit:contain;"></div><div class="brand-text"><span class="brand-name">UKIYO</span><span class="brand-sub">Restaurante Japonés</span></div></a>
    <div class="nav-right"><div class="usuario-badge"><div class="avatar"><?php echo strtoupper(substr($_SESSION['nombre'],0,1)); ?></div><span class="d-none d-md-inline"><?php echo $_SESSION['nombre']; ?></span></div><a href="../cerrarSesion.php" class="btn-salir"><i class="bi bi-box-arrow-right"></i> Salir</a></div>
</nav>
<div class="inner-content">
    <div class="section-header">
        <a href="../index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Inicio</a>
        <span class="section-title">Empleados</span>
        <a href="registrar.php" class="btn-ukiyo ms-auto"><i class="bi bi-plus-lg"></i> Nuevo empleado</a>
    </div>
    <div class="divider-rojo"></div>

    <?php if ($exito == 1): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle"></i> Empleado registrado correctamente.</div>
    <?php elseif ($exito == 2): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle"></i> Empleado actualizado correctamente.</div>
    <?php endif; ?>

    <div class="card-ukiyo">
        <div class="card-header-ukiyo"><i class="bi bi-people"></i> Empleados registrados</div>
        <div class="card-body-ukiyo">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaEmpleados">
                    <thead><tr><th>#</th><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php while ($emp = mysqli_fetch_assoc($empleados)): ?>
                        <tr>
                            <td><?php echo $emp['idUsuario']; ?></td>
                            <td><?php echo $emp['nombre']; ?></td>
                            <td><?php echo $emp['usuario']; ?></td>
                            <td><?php echo $emp['rol'] == 'admin' ? '<span class="badge-admin">Admin</span>' : '<span class="badge-empleado">Empleado</span>'; ?></td>
                            <td>
                                <a href="editar.php?id=<?php echo $emp['idUsuario']; ?>" class="btn-accion btn-editar"><i class="bi bi-pencil"></i></a>
                                <button class="btn-accion btn-eliminar" onclick="eliminar(<?php echo $emp['idUsuario']; ?>)"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() { $('#tablaEmpleados').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }, pageLength: 10 }); });
function eliminar(id) {
    Swal.fire({ title: 'Eliminar empleado', text: 'Esta accion no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#C0392B', cancelButtonColor: '#555', confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar' }).then(result => {
        if (result.isConfirmed) {
            $.ajax({ type: 'POST', url: 'eliminar.php', data: { id }, dataType: 'json', success: function(r) {
                if (r.success) { Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false }); setTimeout(() => location.reload(), 1500); }
                else { Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje, confirmButtonColor: '#C0392B' }); }
            }});
        }
    });
}
</script>
</body></html>