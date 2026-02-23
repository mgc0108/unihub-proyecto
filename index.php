<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// --- LÓGICA DE PROCESAMIENTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Registro de Tareas o Exámenes
    if (isset($_POST['materia'])) {
        $tipo = $_POST['tipo'] ?? 'Tarea';
        $materia = $_POST['materia'];
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $anot = $_POST['anotaciones'] ?? '';

        $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, tipo, anotaciones, completado) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$materia, $fecha, $tipo, $anot]);
        header("Location: index.php"); exit;
    }
}

// Acciones rápidas
if(isset($_GET['toggle'])) { $db->query("UPDATE examenes SET completado = 1 - completado WHERE id = ".(int)$_GET['toggle']); header("Location: index.php"); exit; }
if(isset($_GET['del'])) { $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del']); header("Location: index.php"); exit; }

// --- CARGA DE DATOS ---
$dias_trad = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy_nom = $dias_trad[date('l')];

$clases = $db->query("SELECT * FROM horarios WHERE dia_semana = '$hoy_nom' ORDER BY hora_inicio ASC")->fetchAll(PDO::FETCH_ASSOC);
$tareas = $db->query("SELECT * FROM examenes WHERE tipo = 'Tarea' ORDER BY completado ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$examenes = $db->query("SELECT * FROM examenes WHERE tipo IN ('Examen', 'Trabajo') AND fecha >= CURDATE() ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);

// Progreso semanal
$total_t = count($tareas);
$hechas_t = 0;
foreach($tareas as $t) { if($t['completado']) $hechas_t++; }
$progreso = ($total_t > 0) ? round(($hechas_t / $total_t) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMa | Lucia P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .card-u { background: white; border-radius: 24px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 20px; border: none; }
        .fw-800 { font-weight: 800; }
        .text-primary-u { color: #4338ca !important; }
        .strikethrough { text-decoration: line-through; opacity: 0.5; }
        
        /* Hub Enlaces */
        .hub-link { background: white; padding: 12px; border-radius: 15px; text-decoration: none; color: #1e293b; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; transition: 0.2s; }
        .hub-link:hover { border-color: #4338ca; color: #4338ca; transform: translateY(-2px); }

        /* Countdown */
        .cd-card { background: #1e293b; color: white; border-radius: 18px; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .timer-unit { text-align: center; background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 8px; min-width: 45px; margin-left: 5px; }
        .timer-val { display: block; font-size: 1rem; font-weight: 800; }
        .timer-label { font-size: 0.5rem; text-transform: uppercase; opacity: 0.6; }

        /* Bus Badges */
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 3px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; margin: 2px; display: inline-block; }
        .bus-badge-vta { background: #fff1f2; color: #ef4444; }

        /* Progress Bar */
        .progress { height: 8px; background: #e2e8f0; border-radius: 10px; }
        .progress-bar { background: #4338ca; }
    </style>
</head>
<body class="p-3 p-md-4">
<div class="container">
    
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3"><a href="https://aulavirtual.uji.es/" target="_blank" class="hub-link">🎓 Aula Virtual</a></div>
        <div class="col-6 col-md-3"><a href="https://mail.google.com/mail/u/0/#inbox" target="_blank" class="hub-link">📧 Gmail UJI</a></div>
        <div class="col-6 col-md-3"><a href="https://sia.uji.es/" target="_blank" class="hub-link">📑 SIA</a></div>
        <div class="col-6 col-md-3"><a href="https://www.uji.es/biblioteca/" target="_blank" class="hub-link">📚 Biblioteca</a></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            
            <div class="card-u" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary-u mb-3">🚌 BUS 360: LA VALL ↔ UJI</h6>
                <div class="row small">
                    <div class="col-6 text-center">
                        <span class="fw-bold d-block mb-1 text-muted">IDA</span>
                        <?php $ida = ['06:40', '07:50', '09:10', '09:50', '10:50', '11:50', '12:50', '13:45', '15:00', '15:50', '16:30', '17:40', '18:30', '19:45', '20:40']; 
                        foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 text-center border-start">
                        <span class="fw-bold d-block mb-1 text-muted">VUELTA</span>
                        <?php $vta = ['07:50', '09:05', '10:25', '11:15', '12:05', '13:05', '14:05', '15:00', '16:15', '17:05', '17:45', '19:00', '19:50', '21:00', '22:00']; 
                        foreach($vta as $h) echo "<span class='bus-badge bus-badge-vta'>$h</span>"; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-800 mb-0">⏳ Cuenta atrás Exámenes</h5>
                <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addE">+</button>
            </div>
            <?php foreach($examenes as $ex): ?>
                <div class="cd-card">
                    <div>
                        <h6 class="fw-800 mb-0" style="font-size: 0.9rem;"><?= $ex['materia'] ?></h6>
                        <small class="opacity-75" style="font-size: 0.7rem;"><?= $ex['tipo'] ?> - <?= date('d/m', strtotime($ex['fecha'])) ?></small>
                    </div>
                    <div class="d-flex countdown-engine" data-date="<?= $ex['fecha'] ?> 09:00:00">
                        <div class="timer-unit"><span class="timer-val days">-</span><span class="timer-label">d</span></div>
                        <div class="timer-unit"><span class="timer-val hours">-</span><span class="timer-label">h</span></div>
                        <div class="timer-unit"><span class="timer-val mins">-</span><span class="timer-label">m</span></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="card-u mt-4">
                <h5 class="fw-800 mb-4">Agenda de hoy (<?= $hoy_nom ?>)</h5>
                <?php if($clases): foreach($clases as $c): ?>
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-4 border-start border-primary border-4" style="border-color: #4338ca !important;">
                        <div class="me-3 text-center">
                            <div class="fw-bold text-primary-u small"><?= substr($c['hora_inicio'],0,5) ?></div>
                            <div class="text-muted" style="font-size: 0.6rem;"><?= substr($c['hora_fin'],0,5) ?></div>
                        </div>
                        <div><div class="fw-bold small"><?= $c['materia'] ?></div><small class="text-muted">Aula <?= $c['aula'] ?></small></div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small">Sin clases hoy.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-u">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-800 mb-0">📊 Mi Progreso</h6>
                    <span class="fw-bold text-primary-u small"><?= $progreso ?>%</span>
                </div>
                <div class="progress mb-2"><div class="progress-bar" style="width: <?= $progreso ?>%"></div></div>
                <small class="text-muted" style="font-size: 0.7rem;"><?= $hechas_t ?> de <?= $total_t ?> tareas completadas</small>
            </div>

            <div class="card-u bg-success text-white">
                <h6 class="fw-800 mb-2">🍽️ Menú Cafetería</h6>
                <a href="https://ujiapps.uji.es/ade/rest/storage/P4LFVSDOBJDUDE3EQFGR21LJEBWR1W31" target="_blank" class="btn btn-light btn-sm w-100 fw-bold rounded-pill text-success">Ver PDF de la semana</a>
            </div>

            <div class="card-u">
                <div class="d-flex justify-content-between mb-3"><h6 class="fw-800 mb-0">✅ Tareas</h6><button class="btn btn-sm btn-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#addT">+</button></div>
                <?php foreach($tareas as $t): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <div>
                            <a href="?toggle=<?= $t['id'] ?>" class="btn btn-sm p-0 me-2"><?= $t['completado']?'🟢':'⚪' ?></a>
                            <span class="<?= $t['completado']?'strikethrough':'' ?>"><?= $t['materia'] ?></span>
                        </div>
                        <a href="?del=<?= $t['id'] ?>" class="text-danger opacity-25">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-grid gap-2">
                <a href="gestion_clases.php" class="btn btn-dark rounded-pill py-2 fw-bold">⚙️ Gestionar Clases</a>
                <a href="gestion_evaluables.php" class="btn btn-outline-primary rounded-pill py-2 fw-bold">📈 Ver Notas Reales</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addE" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4 border-0 shadow">
    <h5 class="fw-800 mb-3 text-center">Nuevo Examen/Trabajo</h5>
    <form method="POST">
        <select name="tipo" class="form-select mb-2 rounded-pill"><option value="Examen">Examen</option><option value="Trabajo">Trabajo</option></select>
        <input type="text" name="materia" class="form-control mb-2 rounded-pill" placeholder="Asignatura" required>
        <input type="date" name="fecha" class="form-control mb-3 rounded-pill" required>
        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Guardar</button>
    </form>
</div></div></div>

<div class="modal fade" id="addT" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4 border-0 shadow">
    <h5 class="fw-800 mb-3 text-center">Añadir Tarea</h5>
    <form method="POST"><input type="hidden" name="tipo" value="Tarea"><input type="text" name="materia" class="form-control mb-3 rounded-pill" placeholder="¿Qué hay que hacer?" required><button type="submit" class="btn btn-primary w-100 rounded-pill">Añadir</button></form>
</div></div></div>

<script>
    function updateCountdowns() {
        const timers = document.querySelectorAll('.countdown-engine');
        timers.forEach(timer => {
            const target = new Date(timer.getAttribute('data-date')).getTime();
            const now = new Date().getTime();
            const diff = target - now;
            if (diff > 0) {
                timer.querySelector('.days').innerText = Math.floor(diff / (1000 * 60 * 60 * 24));
                timer.querySelector('.hours').innerText = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                timer.querySelector('.mins').innerText = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            } else { timer.innerHTML = "<small>¡Hoy!</small>"; }
        });
    }
    setInterval(updateCountdowns, 1000); updateCountdowns();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>