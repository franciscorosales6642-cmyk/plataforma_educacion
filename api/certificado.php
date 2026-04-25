<?php
include "_headers.php";
include "../config/conexion.php";

$usuario_id = (int) ($_GET['usuario_id'] ?? 0);
$curso_id = (int) ($_GET['curso_id'] ?? 0);

if ($usuario_id <= 0 || $curso_id <= 0) {
    echo json_encode(['error' => 'Parametros incompletos']);
    exit;
}

$sql = "
    SELECT
        u.id usuario_id,
        u.nombre usuario,
        c.id curso_id,
        c.nombre curso,
        p.nombre profesor,
        r.fecha,
        r.archivo
    FROM reconocimientos r
    INNER JOIN usuarios u ON u.id = r.usuario_id
    INNER JOIN cursos c ON c.id = r.curso_id
    INNER JOIN profesores p ON p.id = c.profesor_id
    WHERE r.usuario_id = $usuario_id AND r.curso_id = $curso_id
    LIMIT 1
";

$certificado = $conn->query($sql)->fetch_assoc();

if (!$certificado) {
    echo json_encode(['error' => 'Certificado no disponible para este curso']);
    exit;
}

$fecha = date('d/m/Y', strtotime($certificado['fecha']));
$folio = sprintf(
    'CERT-%04d-%04d-%s',
    (int) $certificado['usuario_id'],
    (int) $certificado['curso_id'],
    date('Ymd', strtotime($certificado['fecha']))
);

$baseUrl = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
) ? 'https://' : 'http://';
$baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl .= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

echo json_encode([
    'usuario_id' => (int) $certificado['usuario_id'],
    'curso_id' => (int) $certificado['curso_id'],
    'usuario' => $certificado['usuario'],
    'curso' => $certificado['curso'],
    'profesor' => $certificado['profesor'],
    'fecha' => $fecha,
    'folio' => $folio,
    'archivo' => $certificado['archivo'],
    'url_pdf' => $baseUrl . '/certificado_pdf.php?usuario_id=' . (int) $certificado['usuario_id'] . '&curso_id=' . (int) $certificado['curso_id'],
    'pdf_url' => $baseUrl . '/certificado_pdf.php?usuario_id=' . (int) $certificado['usuario_id'] . '&curso_id=' . (int) $certificado['curso_id']
]);
?>
