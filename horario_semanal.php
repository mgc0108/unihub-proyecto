<?php
require_once 'config/database.php';
require_once 'src/Models/horario.php';

$database = new Database();
$db = $database->getConnection();
$horarioModel = new Horario($db);

// Obtenemos todas las clases de la base de datos
$todas_las_clases = $horarioModel->obtenerTodos(); 

$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Completo | UniHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
        .grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; padding: 20px; }
        .dia-col { background: white; border-radius: 20px; padding: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: fit-content; }
        .dia-titulo { color: #4338ca; font-weight: 800; text-align: center; margin-bottom: 15px; border-bottom: 2px solid #eef2ff; pb-2; }
        .clase-card { background: #f8faff; border-left: 4px solid #4338ca; border-radius: 10px; padding: 10px; margin-bottom: 10px; }
        .clase-materia { font-weight: 700; font-size: 0.9rem; display: block; }
        .clase-info { font-size: 0.75rem; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center px-3 mb-4">
            <h2 class="fw-bold mb-0">Mi Semana 📅</h2>
            <a href="index.php" class="btn btn-outline-dark rounded-pill">Volver</a>
        </div>

        <div class="grid-container">
            <?php foreach($dias as $dia): ?>
                <div class="dia-col">
                    <h6 class="dia-titulo"><?= $dia ?></h6>
                    <?php 
                    $hay_clase = false;
                    foreach($todas_las_clases as $c): 
                        if($c['dia_semana'] == $dia): 
                            $hay_clase = true;
                    ?>
                        <div class="clase-card">
                            <span class="clase-materia"><?= $c['materia'] ?></span>
                            <span class="clase-info">🕒 <?= substr($c['hora_inicio'], 0, 5) ?> | 📍 <?= $c['aula'] ?></span>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    if(!$hay_clase) echo '<p class="text-center text-muted small">Sin clases</p>';
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>