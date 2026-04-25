<?php include "_headers.php"; include "../config/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $usuario_id=(int)($_GET['usuario_id'] ?? 0); $curso_id=(int)($_GET['curso_id'] ?? 0);
    $datos=[]; $vistos=[];
    $r=$conn->query("SELECT video_id FROM progreso WHERE usuario_id=$usuario_id AND curso_id=$curso_id AND visto=1 ORDER BY id ASC");
    while($row=$r->fetch_assoc()){ $vistos[]=(int)$row['video_id']; }
    echo json_encode(['vistos'=>$vistos]);
    exit;
}

$usuario_id=(int)($_POST['usuario_id'] ?? 0); $curso_id=(int)($_POST['curso_id'] ?? 0); $video_id=(int)($_POST['video_id'] ?? 0);
$existe=$conn->query("SELECT id FROM progreso WHERE usuario_id=$usuario_id AND video_id=$video_id")->num_rows;
if($existe==0){ $conn->query("INSERT INTO progreso(usuario_id,curso_id,video_id,visto) VALUES($usuario_id,$curso_id,$video_id,1)"); $conn->query("UPDATE videos SET visualizaciones=visualizaciones+1 WHERE id=$video_id"); }
echo json_encode(['mensaje'=>'Progreso guardado']);
?>
