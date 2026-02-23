<?php
// 1. Configuración de Errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/nota.php';
require_once 'src/Models/menu.php';
require_once 'src/Models/examen.php';

// 2. Conexión y Modelos
$database = new Database();
$db = $database->getConnection();

$horarioModel = new Horario($db);
$notaModel = new Nota($db);
$menuModel = new Menu($db);
$examenModel = new Examen($db);

// 3. Procesar Acciones
if (isset($_POST['btnGuardarNota'])) {
    $notaModel->guardar($_POST['materia'], $_POST['tipo'], $_POST['calificacion'], $_POST['porcentaje'], $_POST['fecha']);
    header("Location: index.php"); exit();
}

if (isset($_POST['btnGuardarExamen'])) {
    $examenModel->guardar($_POST['materia'], $_POST['fecha'], $_POST['hora'], $_POST['tipo']);
    header("Location: index.php"); exit();
}

if (isset($_GET['eliminar_nota'])) {
    $notaModel->eliminar($_GET['eliminar_nota']);
    header("Location: index.php"); exit();
}

if (isset($_GET['eliminar_examen'])) {
    $examenModel->eliminar($_GET['eliminar_examen']);
    header("Location: index.php"); exit();
}

// 4. Carga de datos
$clases = $horarioModel->obtenerSemana();
$notas = $notaModel->obtenerTodas();
$menuHoy = $menuModel->obtenerMenuHoy();
$proximosExamenes = $examenModel->obtenerProximos();

$promedio = (count($notas) > 0) ? round(array_sum(array_column($notas, 'calificacion')) / count($notas), 2) : 0;
$dias_esp = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy_texto = $dias_esp[date('l')];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub | Tu Asistente Campus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fa; color: #1a1c1e; }
        .dashboard-card { border: none; border-radius: 24px; transition: 0.3s; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .main-container { background: white; border-radius: 28px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); margin-bottom: 20px; }
        .icon-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 15px; }
        .bg-horario { background: #e0e7ff; color: #4338ca; }
        .bg-notas { background: #fef3c7; color: #b45309; }
        .bg-menu { background: #dcfce7; color: #15803d; }
        .bg-bus { background: #f1f5f9; color: #475569; }
        .timeline-item { border-left: 2px solid #e2e8f0; position: relative; padding-left: 20px; margin-bottom: 20px; }
        .timeline-item::before { content: ''; position: absolute; left: -7px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #4338ca; }
        .btn-add-mini { position: absolute; top: 15px; right: 15px; width: 30px; height: 30px; border-radius: 50%; border: none; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .bus-badge { background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 5px 10px; border-radius: 10px; font-size: 0.8rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-800 mb-0" style="letter-spacing: -1px;">UniHub 🚀</h1>
            <p class="text-muted small fw-600">Dashboard / <?= $hoy_texto ?>, <?= date('d M') ?></p>
        </div>
        <div class="text-end">
            <a href="horario_semanal.php" class="btn btn-dark rounded-pill px-4 shadow-sm">📅 Ver Semana</a>
        </div>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-3 h-100">
                <div class="icon-box bg-horario">🕒</div>
                <h6 class="fw-bold mb-0">Hoy</h6>
                <small class="text-muted"><?= count($clases) ?> clases</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-3 h-100 position-relative">
                <button class="btn-add-mini bg-warning" data-bs-toggle="modal" data-bs-target="#modalNota">+</button>
                <div class="icon-box bg-notas">📝</div>
                <h6 class="fw-bold mb-0">Promedio</h6>
                <span class="text-warning fw-800 fs-5"><?= $promedio ?></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-3 h-100">
                <div class="icon-box bg-menu">🍲</div>
                <h6 class="fw-bold mb-0">Menú Bar</h6>
                <small class="text-success text-truncate"><?= $menuHoy ? $menuHoy['plato_principal'] : "No disponible" ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-3 h-100 position-relative">
                <button class="btn-add-mini bg-danger" data-bs-toggle="modal" data-bs-target="#modalExamen">+</button>
                <div class="icon-box bg-danger text-white">🎯</div>
                <h6 class="fw-bold mb-0">Exámenes</h6>
                <small class="text-danger fw-bold"><?= count($proximosExamenes) ?> pendientes</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="main-container bg-white border border-primary border-opacity-10">
                <h6 class="fw-800 text-primary mb-3">🚌 Bus Castellón ↔ UJI (TRAM/L11)</h6>
                <div class="d-flex flex-wrap gap-2">
                    <span class="bus-badge">07:30</span>
                    <span class="bus-badge">08:00</span>
                    <span class="bus-badge">08:30</span>
                    <span class="bus-badge">09:00</span>
                    <span class="bus-badge">09:30</span>
                    <span class="bus-badge">10:00</span>
                    <span class="bus-badge text-primary">...cada 30'</span>
                </div>
            </div>

            <div class="main-container">
                <h5 class="fw-800 mb-4">Agenda para hoy</h5>
                <?php if($clases): foreach($clases as $c): ?>
                    <div class="timeline-item">
                        <span class="badge bg-light text-primary mb-1"><?= substr($c['hora_inicio'], 0, 5) ?></span>
                        <h6 class="fw-bold mb-0"><?= $c['materia'] ?></h6>
                        <small class="text-muted">Aula <?= $c['aula'] ?></small>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted py-4 text-center">No hay clases hoy. ¡A descansar!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="main-container bg-light border-0">
                <h5 class="fw-800 mb-3">📌 Llevar a clase</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check1">
                    <label class="form-check-label small fw-600" for="check1">Llevar tijeras y pegamento</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check2">
                    <label class="form-check-label small fw-600" for="check2">Pendrive con el proyecto</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check3">
                    <label class="form-check-label small fw-600" for="check3">Cargador del portátil</label>
                </div>
            </div>

            <div class="main-container">
                <h5 class="fw-800 mb-4">Próximos Exámenes 🎯</h5>
                <?php foreach($proximosExamenes as $e): ?>
                <div class="p-3 rounded-4 mb-2 bg-<?= ($e['dias_restantes'] <= 2) ? 'danger-subtle' : 'light' ?> d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold small"><?= $e['materia'] ?></div>
                        <small class="text-muted"><?= date('d M', strtotime($e['fecha'])) ?></small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-white text-dark border rounded-pill mb-1">Faltan <?= $e['dias_restantes'] ?>d</span><br>
                        <a href="?eliminar_examen=<?= $e['id'] ?>" class="text-danger small text-decoration-none" onclick="return confirm('¿Eliminar?')">🗑️</a>
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