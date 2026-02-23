<?php
// 1. Errores y Configuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
// Usamos minúsculas para evitar líos con el servidor Linux de Clever Cloud
require_once 'src/Models/horario.php';
require_once 'src/Models/nota.php';
require_once 'src/Models/menu.php';
require_once 'src/Models/examen.php';

// 2. Inicializar conexión y Modelos
$database = new Database();
$db = $database->getConnection();

$horarioModel = new Horario($db);
$notaModel = new Nota($db);
$menuModel = new Menu($db);
$examenModel = new Examen($db);

// 3. PROCESAR ACCIONES (POST y GET)

// Guardar Nota
if (isset($_POST['btnGuardarNota'])) {
    $notaModel->guardar($_POST['materia'], $_POST['tipo'], $_POST['calificacion'], $_POST['porcentaje'], $_POST['fecha']);
    header("Location: index.php"); exit();
}

// Guardar Examen
if (isset($_POST['btnGuardarExamen'])) {
    $examenModel->guardar($_POST['materia'], $_POST['fecha'], $_POST['hora'], $_POST['tipo']);
    header("Location: index.php"); exit();
}

// ELIMINAR (Nuevo sistema)
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

// 5. Cálculo de Promedio General
$promedio = 0;
if (count($notas) > 0) {
    $suma = array_sum(array_column($notas, 'calificacion'));
    $promedio = round($suma / count($notas), 2);
}

$dias_traducidos = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy_texto = $dias_traducidos[date('l')];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub | Smart Campus</title>
    
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/5400/5400584.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; color: #1a1c1e; }
        .dashboard-card { border: none; border-radius: 24px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.03); background: white; }
        .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .icon-box { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 15px; }
        
        .bg-horario { background-color: #e0e7ff; color: #4338ca; }
        .bg-notas { background-color: #fef3c7; color: #b45309; }
        .bg-menu { background-color: #dcfce7; color: #15803d; }
        .bg-examenes { background-color: #fee2e2; color: #b91c1c; }

        .main-container { background: white; border-radius: 28px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .btn-add-mini { position: absolute; top: 15px; right: 15px; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: none; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        /* Estilos Timeline Horario */
        .timeline-item { border-left: 2px dashed #dee2e6; position: relative; padding-left: 25px; margin-bottom: 25px; }
        .timeline-item::before { content: ''; position: absolute; left: -7px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #4338ca; border: 2px solid white; }
        
        .semaforo-card { border-left: 6px solid; border-radius: 15px; background: #fff; transition: 0.2s; }
        .semaforo-rojo { border-color: #dc3545; background-color: #fff5f5; }
        .semaforo-amarillo { border-color: #ffc107; background-color: #fffdf2; }
        .semaforo-verde { border-color: #198754; background-color: #f2fff6; }
        
        .btn-delete { color: #ced4da; transition: 0.2s; text-decoration: none; }
        .btn-delete:hover { color: #dc3545; }
    </style>
</head>
<body>
<div class="main-container mt-4">
    <h5 class="fw-bold mb-3">📌 No olvidar...</h5>
    <div class="list-group list-group-flush">
        <div class="list-group-item bg-transparent border-0 d-flex align-items-center">
            <input class="form-check-input me-3" type="checkbox">
            <span>Llevar tijeras y pegamento</span>
        </div>
    </div>
</div>
<div class="main-container bg-primary text-white mb-4">
    <h6 class="fw-bold mb-3">🚌 Próximos Buses (L11 / TRAM)</h6>
    <div class="d-flex overflow-auto gap-2 pb-2" style="scrollbar-width: none;">
        <span class="badge bg-white text-primary p-2">07:30</span>
        <span class="badge bg-white text-primary p-2">08:00</span>
        <span class="badge bg-white text-primary p-2">08:30</span>
        <span class="badge bg-white text-primary p-2">09:00</span>
        <span class="badge bg-white text-primary p-2">...cada 30 min</span>
    </div>
    <small class="opacity-75">Última salida: 22:00h</small>
</div>
<div class="container py-4">
    <header class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold h2">UniHub 🚀</h1>
            <p class="text-secondary small">Campus Digital | <strong><?php echo $hoy_texto; ?></strong></p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark border rounded-pill p-2 px-3 shadow-sm">v1.2 Live</span>
        </div>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-4 h-100">
                <div class="icon-box bg-horario">🕒</div>
                <h6 class="fw-bold mb-1">Horario</h6>
                <small class="text-muted"><?= count($clases) ?> clases hoy</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-4 h-100 border-bottom border-warning border-4">
                <button class="btn-add-mini bg-warning" data-bs-toggle="modal" data-bs-target="#modalNota">+</button>
                <div class="icon-box bg-notas">📝</div>
                <h6 class="fw-bold mb-1">Promedio</h6>
                <span class="fs-4 fw-bold text-warning"><?= $promedio ?></span>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-4 h-100">
                <div class="icon-box bg-menu">🍲</div>
                <h6 class="fw-bold mb-1">Menú Bar</h6>
                <small class="text-success text-truncate d-block"><?= $menuHoy ? $menuHoy['plato_principal'] : "No disponible" ?></small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card dashboard-card p-4 h-100 border-bottom border-danger border-4">
                <button class="btn-add-mini bg-danger" data-bs-toggle="modal" data-bs-target="#modalExamen">+</button>
                <div class="icon-box bg-examenes">🎯</div>
                <h6 class="fw-bold mb-1">Exámenes</h6>
                <small class="text-danger fw-bold"><?= count($proximosExamenes) ?> pendientes</small>
            </div>
        </div>
    </div>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Agenda del día</h5>
    <a href="horario_semanal.php" class="btn btn-sm btn-outline-primary rounded-pill">Ver semana completa →</a>
</div>
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="main-container h-100">
                <h5 class="fw-bold mb-4">Agenda del día</h5>
                <?php if($clases): ?>
                    <?php foreach($clases as $c): ?>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-light text-primary mb-1"><?= substr($c['hora_inicio'], 0, 5) ?></span>
                                <h6 class="fw-bold mb-0"><?= $c['materia'] ?></h6>
                                <small class="text-muted">Aula: <?= $c['aula'] ?> • <?= $c['dia_semana'] ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center py-5 text-muted">No hay clases registradas para hoy.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="main-container mb-4">
                <h5 class="fw-bold mb-4">Próximos Retos 🚩</h5>
                <?php foreach($proximosExamenes as $e): 
                    $clase_semaforo = ($e['dias_restantes'] <= 2) ? 'semaforo-rojo' : (($e['dias_restantes'] <= 7) ? 'semaforo-amarillo' : 'semaforo-verde');
                ?>
                <div class="p-3 mb-3 semaforo-card <?= $clase_semaforo ?> shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold"><?= $e['materia'] ?></div>
                            <small class="text-muted"><?= $e['tipo'] ?> • <?= date('d M', strtotime($e['fecha'])) ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-white text-dark border rounded-pill mb-2 d-block">
                                Faltan <?= $e['dias_restantes'] ?>d
                            </span>
                            <a href="?eliminar_examen=<?= $e['id'] ?>" class="btn-delete small" onclick="return confirm('¿Eliminar examen?')">🗑️</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="main-container">
                <h5 class="fw-bold mb-3">Notas Recientes</h5>
                <div class="list-group list-group-flush">
                    <?php foreach(array_slice($notas, 0, 4) as $n): ?>
                    <div class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;"><?= $n['materia'] ?></div>
                            <small class="text-muted"><?= $n['tipo'] ?> (<?= $n['porcentaje'] ?>%)</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-3 text-warning"><?= $n['calificacion'] ?></span>
                            <a href="?eliminar_nota=<?= $n['id'] ?>" class="btn-delete" onclick="return confirm('¿Eliminar nota?')">×</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 p-4 shadow" style="border-radius:28px">
        <h5 class="fw-bold mb-4 text-center">Registrar Calificación 🎓</h5>
        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold mb-1">Materia</label>
                <input type="text" name="materia" class="form-control bg-light border-0" placeholder="Ej: Programación" required>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="small fw-bold mb-1">Tipo</label>
                    <select name="tipo" class="form-select bg-light border-0"><option>Examen</option><option>Trabajo</option></select>
                </div>
                <div class="col-6">
                    <label class="small fw-bold mb-1">Nota</label>
                    <input type="number" step="0.1" name="calificacion" class="form-control bg-light border-0" placeholder="0.0" required>
                </div>
            </div>
            <div class="row g-2 mb-4">
                <div class="col-6"><label class="small fw-bold mb-1">Peso %</label><input type="number" name="porcentaje" class="form-control bg-light border-0" value="40"></div>
                <div class="col-6"><label class="small fw-bold mb-1">Fecha</label><input type="date" name="fecha" class="form-control bg-light border-0" value="<?= date('Y-m-d') ?>"></div>
            </div>
            <button type="submit" name="btnGuardarNota" class="btn btn-warning w-100 fw-bold py-3 text-white rounded-4 shadow">Guardar Nota</button>
        </form>
    </div></div>
</div>

<div class="modal fade" id="modalExamen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 p-4 shadow" style="border-radius:28px">
        <h5 class="fw-bold mb-4 text-center">Nuevo Recordatorio 🎯</h5>
        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold mb-1">Asignatura</label>
                <input type="text" name="materia" class="form-control bg-light border-0" required>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6"><label class="small fw-bold mb-1">Fecha</label><input type="date" name="fecha" class="form-control bg-light border-0" required></div>
                <div class="col-6"><label class="small fw-bold mb-1">Hora</label><input type="time" name="hora" class="form-control bg-light border-0"></div>
            </div>
            <div class="mb-4">
                <label class="small fw-bold mb-1">Categoría</label>
                <select name="tipo" class="form-select bg-light border-0"><option>Examen</option><option>Entrega</option></select>
            </div>
            <button type="submit" name="btnGuardarExamen" class="btn btn-danger w-100 fw-bold py-3 rounded-4 shadow">Añadir a la lista</button>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>