<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config/database.php';
require_once 'src/Models/Horario.php';
require_once 'src/Models/Nota.php';
require_once 'src/Models/Menu.php';
require_once 'src/Models/Examen.php';

// 1. Inicializar conexión y Modelos
$database = new Database();
$db = $database->getConnection();

$horarioModel = new Horario($db);
$notaModel = new Nota($db);
$menuModel = new Menu($db);
$examenModel = new Examen($db);

// 2. Procesar Formularios (Notas y Exámenes)
if (isset($_POST['btnGuardarNota'])) {
    $notaModel->guardar($_POST['materia'], $_POST['tipo'], $_POST['calificacion'], $_POST['porcentaje'], $_POST['fecha']);
    header("Location: index.php"); exit();
}
if (isset($_POST['btnGuardarExamen'])) {
    $examenModel->guardar($_POST['materia'], $_POST['fecha'], $_POST['hora'], $_POST['tipo']);
    header("Location: index.php"); exit();
}

// 3. Carga de datos
$clases = $horarioModel->obtenerSemana();
$notas = $notaModel->obtenerTodas();
$menuHoy = $menuModel->obtenerMenuHoy();
$proximosExamenes = $examenModel->obtenerProximos();

// 4. Cálculo de Promedio General
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1a1c1e; }
        .dashboard-card { border: none; border-radius: 20px; transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(0,0,0,0.02); cursor: pointer; position: relative; }
        .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 12px; }
        .bg-horario { background-color: #e0e7ff; color: #4338ca; }
        .bg-notas { background-color: #fef3c7; color: #b45309; }
        .bg-menu { background-color: #dcfce7; color: #15803d; }
        .bg-examenes { background-color: #fee2e2; color: #b91c1c; }
        .main-container { background: white; border-radius: 24px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 20px; }
        .btn-add-mini { position: absolute; top: 15px; right: 15px; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: none; transition: 0.2s; }
        .btn-add-mini:hover { transform: scale(1.1); }
        .semaforo-rojo { border-left: 5px solid #dc3545; }
        .semaforo-amarillo { border-left: 5px solid #ffc107; }
        .semaforo-verde { border-left: 5px solid #198754; }
    </style>
</head>
<body>

<div class="container py-5">
    <header class="mb-5 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold">UniHub 🚀</h1>
            <p class="text-secondary mb-0">Gestión Académica | <strong><?php echo $hoy_texto; ?></strong></p>
        </div>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-6 col-lg-3">
            <div class="card dashboard-card p-4 h-100">
                <div class="icon-box bg-horario">🕒</div>
                <h6 class="fw-bold mb-1">Horario</h6>
                <small class="text-muted"><?= count($clases) ?> clases registradas</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card dashboard-card p-4 h-100 border-start border-warning border-4" onclick="window.location.href='notas_detalle.php'">
                <button class="btn-add-mini bg-warning text-white" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#modalNota">+</button>
                <div class="icon-box bg-notas">📝</div>
                <h6 class="fw-bold mb-1">Notas</h6>
                <div class="d-flex align-items-center">
                    <span class="fs-5 fw-bold text-warning"><?= $promedio ?></span>
                    <small class="ms-2 text-muted small">ver detalle</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card dashboard-card p-4 h-100" onclick="window.location.href='bar.php'">
                <div class="icon-box bg-menu">🍲</div>
                <h6 class="fw-bold mb-1">Menú Bar</h6>
                <small class="text-success"><?= $menuHoy ? $menuHoy['plato_principal'] : "Ver semanal" ?></small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card dashboard-card p-4 h-100 border-start border-danger border-4">
                <button class="btn-add-mini bg-danger text-white" data-bs-toggle="modal" data-bs-target="#modalExamen">+</button>
                <div class="icon-box bg-examenes">🎯</div>
                <h6 class="fw-bold mb-1">Exámenes</h6>
                <small class="text-danger fw-bold"><?= count($proximosExamenes) ?> próximos</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="main-container h-100">
                <h5 class="fw-bold mb-4">Agenda de Clases</h5>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle">
                        <thead><tr class="small text-muted text-uppercase"><th>Día</th><th>Materia</th><th>Hora</th><th>Aula</th></tr></thead>
                        <tbody>
                            <?php foreach($clases as $c): ?>
                            <tr class="border-bottom">
                                <td class="fw-bold py-3"><?= $c['dia_semana'] ?></td>
                                <td><?= $c['materia'] ?></td>
                                <td><span class="badge bg-light text-dark border"><?= substr($c['hora_inicio'],0,5) ?></span></td>
                                <td class="text-primary fw-bold"><?= $c['aula'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="main-container h-100">
                <h5 class="fw-bold mb-4">Semáforo de Exámenes 🚩</h5>
                <?php if($proximosExamenes): ?>
                    <?php foreach($proximosExamenes as $e): 
                        $clase_semaforo = ($e['dias_restantes'] <= 2) ? 'semaforo-rojo' : (($e['dias_restantes'] <= 7) ? 'semaforo-amarillo' : 'semaforo-verde');
                    ?>
                    <div class="p-3 mb-3 rounded-4 bg-light <?= $clase_semaforo ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold mb-0"><?= $e['materia'] ?></div>
                                <small class="text-muted"><?= $e['fecha'] ?> • <?= $e['tipo'] ?></small>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill px-3">
                                <?= $e['dias_restantes'] == 0 ? 'Hoy' : 'Faltan '.$e['dias_restantes'].' d' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center py-5 text-muted">No hay exámenes próximos.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 p-4" style="border-radius:28px shadow-lg">
        <h5 class="fw-bold mb-4 text-center">Registrar Nota 🎓</h5>
        <form method="POST">
            <label class="small fw-bold mb-1">Materia</label>
            <input type="text" name="materia" class="form-control mb-3 bg-light border-0 rounded-3" placeholder="Ej: Programación" required>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="small fw-bold mb-1">Tipo</label>
                    <select name="tipo" class="form-select bg-light border-0 rounded-3"><option>Examen</option><option>Trabajo</option></select>
                </div>
                <div class="col-6">
                    <label class="small fw-bold mb-1">Calificación</label>
                    <input type="number" step="0.1" name="calificacion" class="form-control bg-light border-0 rounded-3" placeholder="0.0" required>
                </div>
            </div>
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="small fw-bold mb-1">Peso (%)</label>
                    <input type="number" name="porcentaje" class="form-control bg-light border-0 rounded-3" placeholder="40" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold mb-1">Fecha</label>
                    <input type="date" name="fecha" class="form-control bg-light border-0 rounded-3" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <button type="submit" name="btnGuardarNota" class="btn btn-warning w-100 fw-bold py-3 text-white rounded-4 shadow">Guardar en Expediente</button>
        </form>
    </div></div>
</div>

<div class="modal fade" id="modalExamen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 p-4" style="border-radius:28px shadow-lg">
        <h5 class="fw-bold mb-4 text-center">Nuevo Examen 🎯</h5>
        <form method="POST">
            <label class="small fw-bold mb-1">Asignatura</label>
            <input type="text" name="materia" class="form-control mb-3 bg-light border-0 rounded-3" placeholder="Materia" required>
            <div class="row g-2 mb-3">
                <div class="col-6"><label class="small fw-bold mb-1">Fecha</label><input type="date" name="fecha" class="form-control bg-light border-0 rounded-3" required></div>
                <div class="col-6"><label class="small fw-bold mb-1">Hora</label><input type="time" name="hora" class="form-control bg-light border-0 rounded-3"></div>
            </div>
            <label class="small fw-bold mb-1">Tipo de prueba</label>
            <select name="tipo" class="form-select mb-4 bg-light border-0 rounded-3">
                <option value="Examen">Examen</option>
                <option value="Entrega">Entrega de Trabajo</option>
            </select>
            <button type="submit" name="btnGuardarExamen" class="btn btn-danger w-100 fw-bold py-3 rounded-4 shadow">Añadir Recordatorio</button>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>