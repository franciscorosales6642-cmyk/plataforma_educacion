<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "ADMIN") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    if ($conn->query("DELETE FROM profesores WHERE id=$id")) {
        $_SESSION['flash_tipo'] = 'success';
        $_SESSION['flash_mensaje'] = 'Profesor eliminado correctamente.';
    } else {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'No se pudo eliminar el profesor.';
    }
    header("Location: profesores.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST["nombre"]);
    $correo = $conn->real_escape_string($_POST["correo"]);
    $especialidad = $conn->real_escape_string($_POST["especialidad"]);
    if ($conn->query("INSERT INTO profesores(nombre, correo, especialidad) VALUES('$nombre','$correo','$especialidad')")) {
        $_SESSION['flash_tipo'] = 'success';
        $_SESSION['flash_mensaje'] = 'Profesor guardado correctamente.';
    } else {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'No se pudo guardar el profesor.';
    }
    header("Location: profesores.php");
    exit;
}

$profesores = $conn->query("SELECT * FROM profesores ORDER BY id DESC");
$flashTipo = $_SESSION['flash_tipo'] ?? '';
$flashMensaje = $_SESSION['flash_mensaje'] ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesores</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="admin-body">
    <div class="container admin-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Administracion</span>
                <h1>Gestion de Profesores</h1>
                <p class="muted-text">Registra docentes y manten actualizado el equipo academico.</p>
            </div>
            <a class="btn btn-light" href="dashboard.php">Volver al panel</a>
        </section>

        <?php if ($flashMensaje) { ?>
        <div class="alert <?= $flashTipo === 'success' ? 'alert-success' : 'alert-error' ?>"><?= htmlspecialchars($flashMensaje) ?></div>
        <?php } ?>

        <div class="admin-grid">
            <section class="card admin-card">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">Nuevo registro</span>
                        <h2>Alta de profesores</h2>
                    </div>
                </div>
                <form method="POST" class="admin-form">
                    <input name="nombre" placeholder="Nombre completo" required>
                    <input type="email" name="correo" placeholder="Correo institucional" required>
                    <input name="especialidad" placeholder="Especialidad" required>
                    <button type="submit">Guardar profesor</button>
                </form>
            </section>

            <section class="card admin-card">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">Listado</span>
                        <h2>Profesores registrados</h2>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Especialidad</th>
                            <th>Accion</th>
                        </tr>
                        <?php while ($p = $profesores->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><?= htmlspecialchars($p['correo']) ?></td>
                            <td><?= htmlspecialchars($p['especialidad']) ?></td>
                            <td><a class="btn btn-danger" href="?eliminar=<?= $p['id'] ?>">Eliminar</a></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
