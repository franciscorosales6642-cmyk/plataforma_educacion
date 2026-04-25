<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "ADMIN") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$cursoEditar = null;

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    if ($conn->query("DELETE FROM cursos WHERE id=$id")) {
        $_SESSION['flash_tipo'] = 'success';
        $_SESSION['flash_mensaje'] = 'Curso eliminado correctamente.';
    } else {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'No se pudo eliminar el curso.';
    }
    header("Location: cursos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $curso_id = (int) ($_POST["curso_id"] ?? 0);
    $nombre = $conn->real_escape_string($_POST["nombre"]);
    $profesor_id = (int) $_POST["profesor_id"];
    $descripcion = $conn->real_escape_string($_POST["descripcion"]);

    if ($curso_id > 0) {
        if ($conn->query("UPDATE cursos SET nombre='$nombre', profesor_id=$profesor_id, descripcion='$descripcion' WHERE id=$curso_id")) {
            $_SESSION['flash_tipo'] = 'success';
            $_SESSION['flash_mensaje'] = 'Curso actualizado correctamente.';
        } else {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_mensaje'] = 'No se pudo actualizar el curso.';
        }
    } else {
        if ($conn->query("INSERT INTO cursos(nombre, profesor_id, descripcion) VALUES('$nombre',$profesor_id,'$descripcion')")) {
            $_SESSION['flash_tipo'] = 'success';
            $_SESSION['flash_mensaje'] = 'Curso guardado correctamente.';
        } else {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_mensaje'] = 'No se pudo guardar el curso.';
        }
    }
    header("Location: cursos.php");
    exit;
}

if (isset($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];
    $cursoEditar = $conn->query("SELECT * FROM cursos WHERE id=$idEditar LIMIT 1")->fetch_assoc();
    if (!$cursoEditar) {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'El curso que intentas editar no existe.';
        header("Location: cursos.php");
        exit;
    }
}

$profesores = $conn->query("SELECT * FROM profesores");
$cursos = $conn->query("SELECT c.*, p.nombre profesor FROM cursos c INNER JOIN profesores p ON c.profesor_id=p.id ORDER BY c.id DESC");
$flashTipo = $_SESSION['flash_tipo'] ?? '';
$flashMensaje = $_SESSION['flash_mensaje'] ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="admin-body">
    <div class="container admin-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Administracion</span>
                <h1>Gestion de Cursos</h1>
                <p class="muted-text">Organiza la oferta academica y asigna cada curso a su profesor.</p>
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
                        <span class="eyebrow"><?= $cursoEditar ? 'Edicion de curso' : 'Nuevo curso' ?></span>
                        <h2><?= $cursoEditar ? 'Actualizar curso' : 'Alta de cursos' ?></h2>
                    </div>
                </div>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="curso_id" value="<?= (int) ($cursoEditar['id'] ?? 0) ?>">
                    <?php if ($cursoEditar) { ?>
                    <div class="alert alert-success">Estas editando el curso <strong><?= htmlspecialchars($cursoEditar['nombre']) ?></strong>. Modifica los datos y guarda los cambios.</div>
                    <?php } ?>
                    <input name="nombre" placeholder="Nombre del curso" value="<?= htmlspecialchars($cursoEditar['nombre'] ?? '') ?>" required>
                    <select name="profesor_id" required>
                        <option value="">Seleccionar profesor</option>
                        <?php while ($p = $profesores->fetch_assoc()) { ?>
                        <option value="<?= $p['id'] ?>" <?= (int) ($cursoEditar['profesor_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php } ?>
                    </select>
                    <textarea name="descripcion" placeholder="Descripcion del curso"><?= htmlspecialchars($cursoEditar['descripcion'] ?? '') ?></textarea>
                    <div class="form-actions">
                        <button type="submit"><?= $cursoEditar ? 'Guardar cambios' : 'Guardar curso' ?></button>
                        <?php if ($cursoEditar) { ?>
                        <a class="btn btn-light-solid" href="cursos.php">Cancelar edicion</a>
                        <?php } ?>
                    </div>
                </form>
            </section>

            <section class="card admin-card">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">Catalogo</span>
                        <h2>Cursos registrados</h2>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Curso</th>
                            <th>Profesor</th>
                            <th>Descripcion</th>
                            <th>Accion</th>
                        </tr>
                        <?php while ($c = $cursos->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                            <td><?= htmlspecialchars($c['profesor']) ?></td>
                            <td><?= htmlspecialchars($c['descripcion']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-light-solid" href="?editar=<?= $c['id'] ?>">Editar</a>
                                    <a class="btn btn-danger" href="?eliminar=<?= $c['id'] ?>">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
