<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

function pdfEscape($text)
{
    $text = utf8_decode($text);
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('(', '\\(', $text);
    $text = str_replace(')', '\\)', $text);
    return $text;
}

$usuario_id = (int) $_SESSION['id'];
$curso_id = (int) ($_GET['curso_id'] ?? 0);
$usuario = $conn->query("SELECT nombre FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
$curso = $conn->query("SELECT c.nombre, p.nombre profesor FROM cursos c INNER JOIN profesores p ON p.id = c.profesor_id WHERE c.id=$curso_id")->fetch_assoc();
$reconocimiento = $conn->query("SELECT * FROM reconocimientos WHERE usuario_id=$usuario_id AND curso_id=$curso_id LIMIT 1")->fetch_assoc();

if (!$usuario || !$curso || !$reconocimiento) {
    header("Location: inicio.php");
    exit;
}

$fecha = date('d/m/Y', strtotime($reconocimiento['fecha']));
$folio = sprintf('CERT-%04d-%04d-%s', $usuario_id, $curso_id, date('Ymd', strtotime($reconocimiento['fecha'])));
$nombreArchivo = 'certificado_curso_' . $curso_id . '.pdf';

$stream = "0.94 0.97 0.99 rg\n";
$stream .= "36 36 523 770 re f\n";
$stream .= "0.07 0.31 0.47 RG\n";
$stream .= "3 w\n";
$stream .= "44 44 507 754 re S\n";
$stream .= "0.12 0.54 0.44 RG\n";
$stream .= "1.2 w\n";
$stream .= "58 58 479 726 re S\n";
$stream .= "BT\n";
$stream .= "/F2 20 Tf\n";
$stream .= "0.07 0.31 0.47 rg\n";
$stream .= "170 760 Td\n";
$stream .= "(" . pdfEscape("PLATAFORMA EDUCATIVA") . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F1 16 Tf\n";
$stream .= "0.12 0.54 0.44 rg\n";
$stream .= "186 720 Td\n";
$stream .= "(" . pdfEscape("CERTIFICADO DE FINALIZACION") . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F1 18 Tf\n";
$stream .= "0.10 0.15 0.22 rg\n";
$stream .= "150 655 Td\n";
$stream .= "(" . pdfEscape("Se otorga el presente certificado a") . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F2 30 Tf\n";
$stream .= "0.04 0.19 0.31 rg\n";
$stream .= "110 605 Td\n";
$stream .= "(" . pdfEscape($usuario['nombre']) . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F1 16 Tf\n";
$stream .= "0.20 0.26 0.34 rg\n";
$stream .= "136 555 Td\n";
$stream .= "(" . pdfEscape("Por haber completado satisfactoriamente el curso") . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F2 24 Tf\n";
$stream .= "0.07 0.31 0.47 rg\n";
$stream .= "92 510 Td\n";
$stream .= "(" . pdfEscape($curso['nombre']) . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F1 15 Tf\n";
$stream .= "0.20 0.26 0.34 rg\n";
$stream .= "82 470 Td\n";
$stream .= "(" . pdfEscape("Impartido por " . $curso['profesor'] . " y registrado oficialmente en la plataforma.") . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F1 13 Tf\n";
$stream .= "0.07 0.31 0.47 rg\n";
$stream .= "110 390 Td\n";
$stream .= "(" . pdfEscape("Fecha de emision: " . $fecha) . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F1 13 Tf\n";
$stream .= "0.07 0.31 0.47 rg\n";
$stream .= "330 390 Td\n";
$stream .= "(" . pdfEscape("Folio: " . $folio) . ") Tj\n";
$stream .= "ET\n";
$stream .= "0.72 0.77 0.82 RG\n";
$stream .= "1 w\n";
$stream .= "100 290 m 240 290 l S\n";
$stream .= "320 290 m 460 290 l S\n";
$stream .= "BT\n";
$stream .= "/F1 12 Tf\n";
$stream .= "0.20 0.26 0.34 rg\n";
$stream .= "118 272 Td\n";
$stream .= "(" . pdfEscape("Direccion Academica") . ") Tj\n";
$stream .= "ET\n";
$stream .= "BT\n";
$stream .= "/F1 12 Tf\n";
$stream .= "0.20 0.26 0.34 rg\n";
$stream .= "345 272 Td\n";
$stream .= "(" . pdfEscape("Plataforma Educativa") . ") Tj\n";
$stream .= "ET\n";

$pdf = "%PDF-1.4\n";
$offsets = [];

$addObject = function ($content) use (&$pdf, &$offsets) {
    $offsets[] = strlen($pdf);
    $pdf .= (count($offsets)) . " 0 obj\n" . $content . "\nendobj\n";
};

$addObject("<< /Type /Catalog /Pages 2 0 R >>");
$addObject("<< /Type /Pages /Count 1 /Kids [3 0 R] >>");
$addObject("<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>");
$addObject("<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream");
$addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
$addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");

$xrefOffset = strlen($pdf);
$pdf .= "xref\n0 " . (count($offsets) + 1) . "\n";
$pdf .= "0000000000 65535 f \n";
foreach ($offsets as $offset) {
    $pdf .= sprintf("%010d 00000 n \n", $offset);
}
$pdf .= "trailer\n<< /Size " . (count($offsets) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
?>
