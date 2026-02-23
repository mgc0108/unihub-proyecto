<?php
require_once 'config/database.php';
require_once 'src/Models/horario.php';

$database = new Database();
$db = $database->getConnection();
$horarioModel = new Horario($db);
$todas_las_clases = $horarioModel->obtenerTodos(); // Asegúrate de tener este método

$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Semanal | UniHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .grid-horario { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
        .clase-card { background: #e0e7ff; border-radius: 10px; padding: 10px; font-size: 0.8rem; margin-bottom: 5px; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Vista Semanal 📅</h2>
        <a href="index.php" class="btn btn-dark">Cerrar</a>
    </div>
    <div class="grid-horario">
        <?php foreach($dias as $dia): ?>
            <div class="dia-col">
                <h6 class="text-center fw-bold bg-white p-2 rounded-3 shadow-sm"><?= $dia ?></h6>
                <?php foreach($todas_las_clases as $c): ?>
                    <?php if($c['dia_semana'] == $dia): ?>
                        <div class="clase-card shadow-sm border-start border-4 border-primary">
                            <strong><?= $c['materia'] ?></strong><br>
                            <span class="text-muted small"><?= substr($c['hora_inicio'], 0, 5) ?> - <?= $c['aula'] ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>