<?php
// 1. CONFIGURACIÓN DE SEGURIDAD PARA SESIONES
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); 

session_start();

// 2. CONTROL DE ACCESO
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit(); 
}

// Si llega aquí es porque SÍ hay sesión
$user_id = $_SESSION['usuario_id'];
require_once 'config/database.php';
$db = (new Database())->getConnection();

// --- 3. LÓGICA DE PROCESAMIENTO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['materia'])) {
        $tipo = $_POST['tipo'] ?? 'Examen';
        $materia = $_POST['materia'];
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $anot = $_POST['anotaciones'] ?? '';
        $n_est = !empty($_POST['nota_estimada']) ? $_POST['nota_estimada'] : NULL;
        $n_sac = !empty($_POST['nota_sacada']) ? $_POST['nota_sacada'] : NULL;

        $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, tipo, anotaciones, nota_estimada, nota_sacada, usuario_id, completado) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$materia, $fecha, $tipo, $anot, $n_est, $n_sac, $user_id]);
        
        header("Location: index.php"); 
        exit();
    }
}

// --- 4. ACCIONES RÁPIDAS (SEGURIZADAS) ---
if(isset($_GET['toggle'])) { 
    $stmt = $db->prepare("UPDATE examenes SET completado = 1 - completado WHERE id = ? AND usuario_id = ?");
    $stmt->execute([(int)$_GET['toggle'], $user_id]);
    header("Location: index.php"); 
    exit(); 
}

if(isset($_GET['del'])) { 
    $stmt = $db->prepare("DELETE FROM examenes WHERE id = ? AND usuario_id = ?");
    $stmt->execute([(int)$_GET['del'], $user_id]);
    header("Location: index.php"); 
    exit(); 
}

// --- 5. CARGA DE DATOS FILTRADOS POR USUARIO ---
$dias_trad = [
    'Monday'    => 'Lunes',
    'Tuesday'   => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday'  => 'Jueves',
    'Friday'    => 'Viernes',
    'Saturday'  => 'Sábado',
    'Sunday'    => 'Domingo'
];
$hoy_nom = $dias_trad[date('l')];

$stmt_clases = $db->prepare("SELECT * FROM horarios WHERE dia_semana = ? AND usuario_id = ? ORDER BY hora_inicio ASC");
$stmt_clases->execute([$hoy_nom, $user_id]);
$clases = $stmt_clases->fetchAll(PDO::FETCH_ASSOC);

$stmt_tareas = $db->prepare("SELECT * FROM examenes WHERE tipo = 'Tarea' AND usuario_id = ? ORDER BY completado ASC");
$stmt_tareas->execute([$user_id]);
$tareas = $stmt_tareas->fetchAll(PDO::FETCH_ASSOC);

$stmt_examenes = $db->prepare("SELECT * FROM examenes WHERE tipo IN ('Examen', 'Trabajo') AND fecha >= CURDATE() AND usuario_id = ? ORDER BY fecha ASC");
$stmt_examenes->execute([$user_id]);
$examenes = $stmt_examenes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMa | Gestión Universitaria</title>
    <link rel="apple-touch-icon" href="icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .card-u { background: white; border-radius: 24px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 20px; border: none; }
        .fw-800 { font-weight: 800; }
        .text-primary-u { color: #4338ca !important; }
        .hub-link { background: #4338ca; color: white; padding: 15px; border-radius: 15px; text-decoration: none; font-weight: 700; display: block; text-align: center; transition: 0.2s; }
        .hub-link:hover { background: #3730a3; color: white; transform: translateY(-2px); }
        .cd-card { background: #1e293b; color: white; border-radius: 18px; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .timer-unit { text-align: center; background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 8px; min-width: 50px; margin-left: 5px; }
        .timer-val { display: block; font-size: 1rem; font-weight: 800; }
        .timer-label { font-size: 0.5rem; text-transform: uppercase; opacity: 0.6; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 3px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; margin: 2px; display: inline-block; }
    </style>
</head>
<body class="p-3 p-md-4">
<div class="container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-800 text-primary-u mb-0">UniMa 🚀</h1>
            <p class="text-muted small mb-0">¡Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Estudiante') ?>!</p>
        </div>
        <a href="logout.php" class="btn btn-light rounded-pill shadow-sm text-danger fw-bold">
            <i class="bi bi-box-arrow-right"></i> Salir
        </a>
    </div>

    <div class="row mb-4 text-center">
        <div class="col-12">
            <a href="https://share.google/JlpEPhzMBl4ICS4uU" target="_blank" class="hub-link shadow-sm">🎓 ENTRAR AL AULA VIRTUAL</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-u" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary-u mb-3">🚌 BUS 360: LA VALL ↔ UJI</h6>
                <div class="row small">
                    <div class="col-6 text-center">
                        <span class="fw-bold d-block mb-1 text-muted small">IDA: LA VALL → UJI</span>
                        <?php $ida = ['06:40', '07:50', '09:10', '09:50', '10:50', '11:50', '12:50', '13:45', '15:00', '15:50', '16:30', '17:40', '18:30', '19:45', '20:40']; 
                        foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 text-center border-start">
                        <span class="fw-bold d-block mb-1 text-muted small">VUELTA: UJI → LA VALL</span>
                        <?php $vta = ['07:50', '09:05', '10:25', '11:15', '12:05', '13:05', '14:05', '15:00', '16:15', '17:05', '17:45', '19:00', '19:50', '21:00', '22:00']; 
                        foreach($vta as $h) echo "<span class='bus-badge' style='background:#fff1f2; color:#ef4444;'>$h</span>"; ?>
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
                        <h6 class="fw-800 mb-0" style="font-size: 0.9rem;"><?= htmlspecialchars($ex['materia']) ?></h6>
                        <small class="opacity-75" style="font-size: 0.7rem;"><?= $ex['tipo'] ?> - <?= date('d/m', strtotime($ex['fecha'])) ?></small>
                    </div>
                    <div class="d-flex countdown-engine" data-date="<?= $ex['fecha'] ?>T09:00:00">
                        <div class="timer-unit"><span class="timer-val days">-</span><span class="timer-label">d</span></div>
                        <div class="timer-unit"><span class="timer-val hours">-</span><span class="timer-label">h</span></div>
                        <div class="timer-unit"><span class="timer-val mins">-</span><span class="timer-label">m</span></div>
                        <div class="timer-unit text-warning"><span class="timer-val secs">-</span><span class="timer-label">s</span></div>
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
                        <div><div class="fw-bold small"><?= htmlspecialchars($c['materia']) ?></div><small class="text-muted small">Aula <?= htmlspecialchars($c['aula']) ?></small></div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small">Sin clases hoy.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
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
                            <span class="<?= $t['completado']?'text-decoration-line-through opacity-50':'' ?>"><?= htmlspecialchars($t['materia']) ?></span>
                        </div>
                        <a href="?del=<?= $t['id'] ?>" class="text-danger opacity-25" onclick="return confirm('¿Borrar tarea?')">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-grid gap-2 mt-4">
                <a href="gestion_clases.php" class="btn btn-dark rounded-pill py-2 fw-bold">⚙️ Horario</a>
                <a href="gestion_evaluables.php" class="btn btn-outline-primary rounded-pill py-2 fw-bold">📊 Historial de Notas</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addE" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4 border-0 shadow">
    <h5 class="fw-800 mb-3 text-center">Nuevo Examen/Trabajo</h5>
    <form method="POST">
        <select name="tipo" class="form-select mb-2 rounded-pill"><option value="Examen">Examen</option><option value="Trabajo">Trabajo</option></select>
        <input type="text" name="materia" class="form-control mb-2 rounded-pill" placeholder="Asignatura" required>
        <input type="date" name="fecha" class="form-control mb-2 rounded-pill" required>
        <div class="row g-2 mb-2">
            <div class="col-6"><input type="number" step="0.01" name="nota_estimada" class="form-control rounded-pill" placeholder="Nota prevista"></div>
            <div class="col-6"><input type="number" step="0.01" name="nota_sacada" class="form-control rounded-pill" placeholder="Nota real"></div>
        </div>
        <textarea name="anotaciones" class="form-control mb-3 rounded-4" placeholder="Anotaciones extra..."></textarea>
        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Guardar</button>
    </form>
</div></div></div>

<div class="modal fade" id="addT" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4 border-0 shadow">
    <h5 class="fw-800 mb-3 text-center">Nueva Tarea</h5>
    <form method="POST"><input type="hidden" name="tipo" value="Tarea"><input type="text" name="materia" class="form-control mb-3 rounded-pill" placeholder="¿Qué hay que hacer?" required><button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Añadir</button></form>
</div></div></div>

<script>
    function updateCountdowns() {
        const timers = document.querySelectorAll('.countdown-engine');
        timers.forEach(timer => {
            const targetStr = timer.getAttribute('data-date');
            const target = new Date(targetStr).getTime();
            const now = new Date().getTime();
            const diff = target - now;
            if (diff > 0) {
                timer.querySelector('.days').innerText = Math.floor(diff / (1000 * 60 * 60 * 24));
                timer.querySelector('.hours').innerText = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                timer.querySelector('.mins').innerText = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                timer.querySelector('.secs').innerText = Math.floor((diff % (1000 * 60)) / 1000);
            } else { 
                timer.innerHTML = "<small class='badge bg-success'>¡Es hoy!</small>"; 
            }
        });
    }
    setInterval(updateCountdowns, 1000); 
    updateCountdowns();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>