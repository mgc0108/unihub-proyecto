<?php
// --- SOLUCIÓN PARA BUCLES EN CLEVER CLOUD ---
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); 

session_start();

// Control de acceso
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit(); 
}

$user_id = $_SESSION['usuario_id'];
require_once 'config/database.php';
$db = (new Database())->getConnection();

// --- LÓGICA DE PROCESAMIENTO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['materia'])) {
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

// --- ACCIONES RÁPIDAS ---
if(isset($_GET['toggle'])) { 
    $stmt = $db->prepare("UPDATE examenes SET completado = 1 - completado WHERE id = ? AND usuario_id = ?");
    $stmt->execute([(int)$_GET['toggle'], $user_id]);
    header("Location: index.php"); exit(); 
}

if(isset($_GET['del'])) { 
    $stmt = $db->prepare("DELETE FROM examenes WHERE id = ? AND usuario_id = ?");
    $stmt->execute([(int)$_GET['del'], $user_id]);
    header("Location: index.php"); exit(); 
}

// --- CARGA DE DATOS ---
$dias_trad = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
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
    <title>UniMa | Mi Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .fw-800 { font-weight: 800; }
        .card { border: none; border-radius: 24px; transition: transform 0.2s ease; }
        .sidebar-item { background: white; border-radius: 20px; padding: 15px; margin-bottom: 12px; border-left: 5px solid #4338ca; }
        .exam-card { background: white; border-radius: 24px; padding: 20px; border: 1px solid #f1f5f9; position: relative; overflow: hidden; }
        .exam-card::before { content: ""; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: #4338ca; }
        .btn-primary { background: #4338ca; border: none; padding: 10px 25px; border-radius: 12px; }
        .countdown-box { background: #f1f5f9; border-radius: 15px; padding: 10px; text-align: center; min-width: 60px; }
        .countdown-num { display: block; font-size: 1.2rem; font-weight: 800; color: #4338ca; }
        .countdown-lab { font-size: 0.65rem; text-transform: uppercase; font-weight: 700; color: #64748b; }
        .task-row { display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 1px solid #f1f5f9; }
        .task-done { text-decoration: line-through; opacity: 0.5; }
    </style>
</head>
<body class="py-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-800 mb-0">¡Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Estudiante') ?>! 👋</h1>
            <p class="text-muted">Organiza tu día y domina tus estudios.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="bar.php" class="btn btn-white border rounded-pill shadow-sm">🍏 Comedor</a>
            <a href="logout.php" class="btn btn-outline-danger rounded-pill shadow-sm">Cerrar Sesión</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-800 mb-0">Clases de Hoy</h5>
                    <a href="gestion_clases.php" class="btn btn-sm btn-light rounded-circle">⚙️</a>
                </div>
                <?php if(empty($clases)): ?>
                    <p class="text-muted small">No hay clases hoy. ¡Tiempo libre! 🙌</p>
                <?php else: ?>
                    <?php foreach($clases as $c): ?>
                        <div class="sidebar-item shadow-sm">
                            <span class="d-block fw-bold"><?= htmlspecialchars($c['materia']) ?></span>
                            <small class="text-muted">🕒 <?= substr($c['hora_inicio'],0,5) ?> - <?= substr($c['hora_fin'],0,5) ?> | 📍 <?= htmlspecialchars($c['aula']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="horario_semanal.php" class="btn btn-light w-100 rounded-pill mt-2">Ver semana completa</a>
            </div>

            <div class="card p-4 shadow-sm">
                <h5 class="fw-800 mb-3">Lista de Tareas</h5>
                <?php foreach($tareas as $t): ?>
                    <div class="task-row">
                        <div class="<?= $t['completado'] ? 'task-done' : '' ?>">
                            <a href="?toggle=<?= $t['id'] ?>" class="text-decoration-none me-2">
                                <?= $t['completado'] ? '✅' : '⬜' ?>
                            </a>
                            <span class="small fw-bold"><?= htmlspecialchars($t['materia']) ?></span>
                        </div>
                        <a href="?del=<?= $t['id'] ?>" class="text-danger text-decoration-none small">✕</a>
                    </div>
                <?php endforeach; ?>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="tipo" value="Tarea">
                    <div class="input-group">
                        <input type="text" name="materia" class="form-control rounded-start-pill border-end-0" placeholder="Nueva tarea..." required>
                        <button type="submit" class="btn btn-primary rounded-end-pill px-3">＋</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-800 mb-0">Próximos Exámenes y Trabajos ⏳</h3>
                    <div class="d-flex gap-2">
                        <a href="gestion_evaluables.php" class="btn btn-sm btn-outline-dark rounded-pill">Gestionar</a>
                        <a href="notas_detalle.php" class="btn btn-sm btn-success rounded-pill">Mis Notas 🎓</a>
                    </div>
                </div>

                <div class="row g-3">
                    <?php if(empty($examenes)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted">No hay exámenes ni trabajos a la vista. <br>¡Buen trabajo manteniéndote al día!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($examenes as $e): ?>
                            <div class="col-md-6">
                                <div class="exam-card shadow-sm">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge rounded-pill <?= $e['tipo'] == 'Examen' ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                            <?= $e['tipo'] ?>
                                        </span>
                                        <small class="fw-bold text-muted"><?= $e['fecha'] ?></small>
                                    </div>
                                    <h5 class="fw-800 mb-3"><?= htmlspecialchars($e['materia']) ?></h5>
                                    
                                    <div class="d-flex gap-2 countdown-engine" data-date="<?= $e['fecha'] ?>">
                                        <div class="countdown-box">
                                            <span class="countdown-num days">00</span>
                                            <span class="countdown-lab">Días</span>
                                        </div>
                                        <div class="countdown-box">
                                            <span class="countdown-num hours">00</span>
                                            <span class="countdown-lab">Horas</span>
                                        </div>
                                        <div class="countdown-box">
                                            <span class="countdown-num mins">00</span>
                                            <span class="countdown-lab">Mins</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card p-4 shadow-sm bg-dark text-white">
                <h5 class="fw-bold mb-3">Añadir Evaluación</h5>
                <form method="POST" class="row g-2">
                    <div class="col-md-3">
                        <select name="tipo" class="form-select bg-dark text-white border-secondary rounded-3">
                            <option value="Examen">Examen</option>
                            <option value="Trabajo">Trabajo</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="materia" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="Materia" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="fecha" class="form-control bg-dark text-white border-secondary rounded-3" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3">Añadir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para la cuenta atrás en tiempo real
    function updateCountdowns() {
        const timers = document.querySelectorAll('.countdown-engine');
        timers.forEach(timer => {
            const targetStr = timer.getAttribute('data-date');
            const target = new Date(targetStr + "T00:00:00").getTime();
            const now = new Date().getTime();
            const diff = target - now;

            if (diff > 0) {
                timer.querySelector('.days').innerText = Math.floor(diff / (1000 * 60 * 60 * 24));
                timer.querySelector('.hours').innerText = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                timer.querySelector('.mins').innerText = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            } else { 
                timer.innerHTML = "<span class='badge bg-success w-100 py-3 rounded-3 fs-6'>¡Es hoy o ya pasó! 🚀</span>";
            }
        });
    }

    setInterval(updateCountdowns, 1000);
    updateCountdowns();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>