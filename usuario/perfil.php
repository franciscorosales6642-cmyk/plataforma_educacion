<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$usuario_id = (int) $_SESSION['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $conn->real_escape_string($_POST['password']);
    $tema = $conn->real_escape_string($_POST['tema']);
    $usuarioActual = $conn->query("SELECT imagen FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
    $imagenPerfil = $usuarioActual['imagen'] ?? 'default.png';

    $archivoSubido = $_FILES['foto_galeria'] ?? null;
    if (!empty($_FILES['foto_camara']['name'])) {
        $archivoSubido = $_FILES['foto_camara'];
    }

    if ($archivoSubido && !empty($archivoSubido['name'])) {
        $permitidas = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        $mime = mime_content_type($archivoSubido['tmp_name']);
        $tamanoMaximo = 5 * 1024 * 1024;

        if (!isset($permitidas[$mime])) {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_mensaje'] = 'La foto debe ser JPG, PNG o WEBP.';
            header("Location: perfil.php");
            exit;
        }

        if ((int) $archivoSubido['size'] > $tamanoMaximo) {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_mensaje'] = 'La foto no debe superar los 5 MB.';
            header("Location: perfil.php");
            exit;
        }

        $nuevoNombre = 'perfil_' . $usuario_id . '_' . time() . '.' . $permitidas[$mime];
        $rutaRelativa = 'uploads/perfiles/' . $nuevoNombre;
        $rutaDestino = dirname(__DIR__) . DIRECTORY_SEPARATOR . $rutaRelativa;

        if (!move_uploaded_file($archivoSubido['tmp_name'], $rutaDestino)) {
            $_SESSION['flash_tipo'] = 'error';
            $_SESSION['flash_mensaje'] = 'No se pudo guardar la foto de perfil.';
            header("Location: perfil.php");
            exit;
        }

        if ($imagenPerfil !== 'default.png') {
            $rutaAnterior = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagenPerfil);
            if (is_file($rutaAnterior)) {
                @unlink($rutaAnterior);
            }
        }

        $imagenPerfil = $conn->real_escape_string($rutaRelativa);
    }

    if ($conn->query("UPDATE usuarios SET password='$password', tema='$tema', imagen='$imagenPerfil' WHERE id=$usuario_id")) {
        $_SESSION['flash_tipo'] = 'success';
        $_SESSION['flash_mensaje'] = 'Perfil actualizado correctamente.';
    } else {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'No se pudo actualizar el perfil.';
    }
    header("Location: perfil.php");
    exit;
}

$usuario = $conn->query("SELECT * FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
$flashTipo = $_SESSION['flash_tipo'] ?? '';
$flashMensaje = $_SESSION['flash_mensaje'] ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="<?= $usuario['tema'] == 'oscuro' ? 'oscuro' : '' ?>">
    <div class="container admin-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Cuenta</span>
                <h1>Configuracion de Perfil</h1>
                <p class="muted-text">Actualiza tu contrasena y el tema visual de la plataforma.</p>
            </div>
            <a class="btn btn-light" href="inicio.php">Volver</a>
        </section>

        <?php if ($flashMensaje) { ?>
        <div class="alert <?= $flashTipo === 'success' ? 'alert-success' : 'alert-error' ?>"><?= htmlspecialchars($flashMensaje) ?></div>
        <?php } ?>

        <section class="card admin-card">
            <form method="POST" class="admin-form" enctype="multipart/form-data">
                <div class="profile-editor">
                    <div class="profile-avatar-panel">
                        <?php
                        $rutaImagen = '../' . $usuario['imagen'];
                        $mostrarImagen = $usuario['imagen'] !== 'default.png' && is_file(dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $usuario['imagen']));
                        ?>
                        <div class="profile-avatar-shell">
                            <?php if ($mostrarImagen) { ?>
                            <img class="profile-avatar-image" src="<?= htmlspecialchars($rutaImagen) ?>" alt="Foto de perfil">
                            <?php } else { ?>
                            <div class="profile-avatar-placeholder"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
                            <?php } ?>
                        </div>
                        <p class="muted-text">Sube una foto desde tu galeria o abre la camara desde tu telefono para tomar una nueva.</p>
                        <div class="profile-upload-actions">
                            <label class="btn btn-light-solid profile-upload-btn">
                                Elegir de galeria
                                <input type="file" name="foto_galeria" accept="image/jpeg,image/png,image/webp,image/*">
                            </label>
                            <label class="btn btn-light-solid profile-upload-btn">
                                Tomar foto
                                <input type="file" name="foto_camara" accept="image/*" capture="user">
                            </label>
                        </div>
                    </div>

                    <div class="profile-settings-panel">
                        <input type="password" name="password" placeholder="Nueva contrasena" value="<?= htmlspecialchars($usuario['password']) ?>" required>
                        <select name="tema">
                            <option value="claro" <?= $usuario['tema'] == 'claro' ? 'selected' : '' ?>>Tema Claro</option>
                            <option value="oscuro" <?= $usuario['tema'] == 'oscuro' ? 'selected' : '' ?>>Tema Oscuro</option>
                        </select>
                        <button type="submit">Guardar cambios</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
