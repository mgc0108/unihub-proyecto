<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

// Lógica de Tareas (Check/Delete)
if(isset($_GET['check_task'])) { $db->query("UPDATE examenes SET progreso = 100 WHERE id = ".(int)$_GET['check_task']); header("Location: index.php"); }
if(isset($_GET['del_task'])) { $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del_task']); header("Location: index.php"); }

// Datos
$hoy = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes'][date('l')] ?? 'Lunes';
$clases = $db->query("SELECT * FROM horarios WHERE dia_semana = '$hoy' ORDER BY hora_inicio")->fetchAll(PDO::FETCH_ASSOC);
$tareas = $db->query("SELECT * FROM examenes WHERE tipo = 'Tarea' ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
$examenes = $db->query("SELECT * FROM examenes WHERE tipo = 'Examen' ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
$menu = $db->query("SELECT * FROM menus WHERE dia_semana = '$hoy'")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniHub Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-u { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .done { text-decoration: line-through; opacity: 0.5; }
    </style>
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between mb-4">
        <h1>UniHub 🚀</h1>
        <a href="gestion_clases.php" class="btn btn-dark rounded-pill">⚙️ Gestionar Horario</a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card-u bg-success text-white">
                <h6>🍴 Menú Hoy (<?=$hoy?>)</h6>
                <h5><?=$menu['plato_principal'] ?? 'No disponible'?></h5>
            </div>
            
            <div class="card-u">
                <h5>📅 Clases de hoy</h5>
                <?php foreach($clases as $c): ?>
                    <div class="d-flex border-bottom py-2">
                        <b class="me-3"><?=substr($c['hora_inicio'],0,5)?></b> <span><?=$c['materia']?> (Aula <?=$c['aula']?>)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card-u">
                <h5>✅ Recordatorios</h5>
                <?php foreach($tareas as $t): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="<?=$t['progreso']>=100?'done':''?>"><?=$t['materia']?></span>
                        <div>
                            <a href="?check_task=<?=$t['id']?>" class="text-success me-2">✔</a>
                            <a href="?del_task=<?=$t['id']?>" class="text-danger">✖</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-u">
                <h5>📝 Exámenes / Trabajos</h5>
                <?php foreach($examenes as $e): ?>
                    <div class="p-2 bg-light rounded mb-2 small">
                        <b><?=$e['materia']?></b> (<?=$e['fecha']?>)<br>
                        Pienso sacar: <?=$e['nota_estimada']?> | Real: <?=$e['nota_sacada'] ?? '-'?><br>
                        <i class="text-muted"><?=$e['anotaciones']?></i>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>