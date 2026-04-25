<?php include "_headers.php"; include "../config/conexion.php";
$usuario_id=(int)($_POST['usuario_id'] ?? 0);
$password=$conn->real_escape_string($_POST['password'] ?? '');
$tema=$conn->real_escape_string($_POST['tema'] ?? 'claro');

if ($usuario_id <= 0) {
    echo json_encode(['error'=>'Usuario no valido']);
    exit;
}

$usuarioActual=$conn->query("SELECT nombre,correo,tema,imagen FROM usuarios WHERE id=$usuario_id LIMIT 1")->fetch_assoc();
if (!$usuarioActual) {
    echo json_encode(['error'=>'Usuario no encontrado']);
    exit;
}

$imagenPerfil = $usuarioActual['imagen'] ?? 'default.png';
$archivoSubido = $_FILES['imagen'] ?? null;

if ($archivoSubido && !empty($archivoSubido['name'])) {
    $permitidas = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    $mime = mime_content_type($archivoSubido['tmp_name']);
    $tamanoMaximo = 5 * 1024 * 1024;

    if (!isset($permitidas[$mime])) {
        echo json_encode(['error'=>'La imagen debe ser JPG, PNG o WEBP']);
        exit;
    }

    if ((int) $archivoSubido['size'] > $tamanoMaximo) {
        echo json_encode(['error'=>'La imagen no debe superar los 5 MB']);
        exit;
    }

    $nuevoNombre = 'perfil_' . $usuario_id . '_' . time() . '.' . $permitidas[$mime];
    $rutaRelativa = 'uploads/perfiles/' . $nuevoNombre;
    $rutaDestino = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);

    if (!move_uploaded_file($archivoSubido['tmp_name'], $rutaDestino)) {
        echo json_encode(['error'=>'No se pudo guardar la imagen']);
        exit;
    }

    if ($imagenPerfil !== 'default.png') {
        $rutaAnterior = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagenPerfil);
        if (is_file($rutaAnterior)) {
            @unlink($rutaAnterior);
        }
    }

    $imagenPerfil = $rutaRelativa;
}

$campos = ["tema='$tema'", "imagen='".$conn->real_escape_string($imagenPerfil)."'"];
if ($password !== '') {
    $campos[] = "password='$password'";
}

if (!$conn->query("UPDATE usuarios SET ".implode(',', $campos)." WHERE id=$usuario_id")) {
    echo json_encode(['error'=>'No se pudo actualizar el perfil']);
    exit;
}

$baseUrl = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
) ? 'https://' : 'http://';
$baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$imagen_url = $imagenPerfil === 'default.png' ? '' : $baseUrl . $basePath . '/' . ltrim($imagenPerfil, '/');

echo json_encode([
    'mensaje'=>'Perfil actualizado correctamente',
    'id'=>$usuario_id,
    'nombre'=>$usuarioActual['nombre'],
    'correo'=>$usuarioActual['correo'],
    'tema'=>$tema,
    'imagen'=>$imagenPerfil,
    'imagen_url'=>$imagen_url
]);
?>
