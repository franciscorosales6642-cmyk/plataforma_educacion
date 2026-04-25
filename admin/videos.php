<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "ADMIN") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$videoEditar = null;

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    if ($conn->query("DELETE FROM videos WHERE id=$id")) {
        $_SESSION['flash_tipo'] = 'success';
        $_SESSION['flash_mensaje'] = 'Video eliminado correctamente.';
    } else {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'No se pudo eliminar el video.';
    }
    header("Location: videos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $video_id = (int) ($_POST["video_id"] ?? 0);
    $curso_id = (int) $_POST["curso_id"];
    $titulo = $conn->real_escape_string($_POST["titulo"]);
    $url_video = $conn->real_escape_string($_POST["url_video"]);
    $orden = (int) $_POST["orden"];

    if ($video_id > 0) {
        if ($conn->query("UPDATE videos SET curso_id=$curso_id, titulo='$titulo', url_video='$url_video', orden=$orden WHERE id=$video_id")) {
            $_SESSION['flash_tipo'] = 'success';
            $_SESSION['flash_mensaje'] = 'Video actualizado correctamente.';
        } else {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_mensaje'] = 'No se pudo actualizar el video.';
        }
    } else {
        if ($conn->query("INSERT INTO videos(curso_id, titulo, url_video, orden) VALUES($curso_id,'$titulo','$url_video',$orden)")) {
            $_SESSION['flash_tipo'] = 'success';
            $_SESSION['flash_mensaje'] = 'Video guardado correctamente.';
        } else {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_mensaje'] = 'No se pudo guardar el video.';
        }
    }
    header("Location: videos.php");
    exit;
}

if (isset($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];
    $videoEditar = $conn->query("SELECT * FROM videos WHERE id=$idEditar LIMIT 1")->fetch_assoc();
    if (!$videoEditar) {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'El video que intentas editar no existe.';
        header("Location: videos.php");
        exit;
    }
}

$cursos = $conn->query("SELECT * FROM cursos");
$videos = $conn->query("SELECT v.*, c.nombre curso FROM videos v INNER JOIN cursos c ON v.curso_id=c.id ORDER BY c.nombre,v.orden");
$flashTipo = $_SESSION['flash_tipo'] ?? '';
$flashMensaje = $_SESSION['flash_mensaje'] ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="admin-body">
    <div class="container admin-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Administracion</span>
                <h1>Gestion de Videos</h1>
                <p class="muted-text">Carga materiales audiovisuales y controla el orden de reproduccion por curso.</p>
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
                        <span class="eyebrow"><?= $videoEditar ? 'Edicion de video' : 'Nuevo video' ?></span>
                        <h2><?= $videoEditar ? 'Editar video' : 'Alta de videos' ?></h2>
                    </div>
                </div>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="video_id" value="<?= (int) ($videoEditar['id'] ?? 0) ?>">
                    <?php if ($videoEditar) { ?>
                    <div class="alert alert-success">Estas editando el video <strong><?= htmlspecialchars($videoEditar['titulo']) ?></strong>. Ajusta los campos y guarda los cambios.</div>
                    <?php } ?>
                    <select name="curso_id" required>
                        <option value="">Seleccionar curso</option>
                        <?php while ($c = $cursos->fetch_assoc()) { ?>
                        <option value="<?= $c['id'] ?>" <?= (int) ($videoEditar['curso_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php } ?>
                    </select>
                    <input name="titulo" placeholder="Titulo del video" value="<?= htmlspecialchars($videoEditar['titulo'] ?? '') ?>" required>
                    <input name="url_video" placeholder="URL de YouTube o ruta local del video" value="<?= htmlspecialchars($videoEditar['url_video'] ?? '') ?>" required>
                    <input type="number" name="orden" placeholder="Orden" value="<?= htmlspecialchars((string) ($videoEditar['orden'] ?? '')) ?>" required>
                    <div class="form-actions">
                        <button type="submit"><?= $videoEditar ? 'Actualizar video' : 'Guardar video' ?></button>
                        <?php if ($videoEditar) { ?>
                        <a class="btn btn-light-solid" href="videos.php">Cancelar edicion</a>
                        <?php } ?>
                    </div>
                </form>
            </section>

            <section class="card admin-card">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">Biblioteca</span>
                        <h2>Videos registrados</h2>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>Curso</th>
                            <th>Titulo</th>
                            <th>URL</th>
                            <th>Orden</th>
                            <th>Vistas</th>
                            <th>Accion</th>
                        </tr>
                        <?php while ($v = $videos->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($v['curso']) ?></td>
                            <td><?= htmlspecialchars($v['titulo']) ?></td>
                            <td class="url-cell"><?= htmlspecialchars($v['url_video']) ?></td>
                            <td><?= $v['orden'] ?></td>
                            <td><?= $v['visualizaciones'] ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-light-solid" href="?editar=<?= $v['id'] ?>">Editar</a>
                                    <a class="btn btn-danger" href="?eliminar=<?= $v['id'] ?>">Eliminar</a>
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
