<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['usuario_id'];
require_once 'config/database.php';
require_once 'src/Models/Nota.php';

$database = new Database();
$db = $database->getConnection();
$notaModel = new Nota($db);

$todasLasNotas = $notaModel->obtenerTodas();

// Separamos las notas en dos grupos
$examenes = array_filter($todasLasNotas, function($n) { return $n['tipo'] == 'Examen'; });
$trabajos = array_filter($todasLasNotas, function($n) { return $n['tipo'] == 'Trabajo'; });

// Función para calcular promedio por grupo
function calcularMedia($lista) {
    if (count($lista) == 0) return 0;
    $suma = array_sum(array_column($lista, 'calificacion'));
    return round($suma / count($lista), 2);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Notas | UniHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; }
        .nav-pills .nav-link { border-radius: 12px; color: #64748b; font-weight: 600; padding: 12px 25px; }
        .nav-pills .nav-link.active { background-color: #f59e0b; color: white; }
        .card-nota { border: none; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; }
        .grade-badge { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="index.php" class="btn btn-dark rounded-pill px-4">← Volver</a>
        <h2 class="fw-bold m-0">Mis Calificaciones 🎓</h2>
    </div>

    <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-examenes-tab" data-bs-toggle="pill" data-bs-target="#pills-examenes" type="button">Exámenes (<?= count($examenes) ?>)</button>
        </li>
        <li class="nav-item ms-2" role="presentation">
            <button class="nav-link" id="pills-trabajos-tab" data-bs-toggle="pill" data-bs-target="#pills-trabajos" type="button">Trabajos (<?= count($trabajos) ?>)</button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-examenes" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 text-center mb-3">
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Media de Exámenes: <?= calcularMedia($examenes) ?></span>
                </div>
                <?php foreach($examenes as $e): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-nota p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><?= $e['materia'] ?></h6>
                                <p class="text-muted small mb-0"><?= $e['fecha'] ?> • Peso: <?= $e['porcentaje'] ?>%</p>
                            </div>
                            <div class="grade-badge <?= $e['calificacion'] >= 5 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                <?= $e['calificacion'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-trabajos" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 text-center mb-3">
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Media de Trabajos: <?= calcularMedia($trabajos) ?></span>
                </div>
                <?php foreach($trabajos as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-nota p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><?= $t['materia'] ?></h6>
                                <p class="text-muted small mb-0"><?= $t['fecha'] ?> • Peso: <?= $t['porcentaje'] ?>%</p>
                            </div>
                            <div class="grade-badge <?= $t['calificacion'] >= 5 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                <?= $t['calificacion'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>