<?php include "_headers.php"; include "../config/conexion.php";
$usuario_id=(int)($_GET['usuario_id'] ?? 0);
if ($usuario_id <= 0) {
    echo json_encode(['error'=>'Usuario no valido']);
    exit;
}

$usuario=$conn->query("SELECT id,nombre,correo,tema,imagen FROM usuarios WHERE id=$usuario_id LIMIT 1")->fetch_assoc();
if (!$usuario) {
    echo json_encode(['error'=>'Usuario no encontrado']);
    exit;
}

$baseUrl = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
) ? 'https://' : 'http://';
$baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$imagen = $usuario['imagen'] ?? 'default.png';
$imagen_url = $imagen === 'default.png' ? '' : $baseUrl . $basePath . '/' . ltrim($imagen, '/');

echo json_encode([
    'id'=>(int)$usuario['id'],
    'nombre'=>$usuario['nombre'],
    'correo'=>$usuario['correo'],
    'tema'=>$usuario['tema'] ?? 'claro',
    'imagen'=>$imagen,
    'imagen_url'=>$imagen_url
]);
?>
