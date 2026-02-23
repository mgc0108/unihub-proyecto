<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// --- LÓGICA DE PROCESAMIENTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['materia'])) {
    $tipo = $_POST['tipo'] ?? 'Examen';
    $materia = $_POST['materia'];
    $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
    $anot = $_POST['anotaciones'] ?? '';

    $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, tipo, anotaciones, completado) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$materia, $fecha, $tipo, $anot]);
    header("Location: index.php"); exit;
}

if(isset($_GET['del'])) { $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del']); header("Location: index.php"); exit; }

// --- CARGA DE EXÁMENES PRÓXIMOS ---
$examenes = $db->query("SELECT * FROM examenes WHERE tipo IN ('Examen', 'Trabajo') AND fecha >= CURDATE() ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
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
        
        /* Enlaces rápidos mejorados */
        .hub-link { background: white; padding: 15px; border-radius: 18px; text-decoration: none; color: #1e293b; font-weight: 700; font-size: 0.85rem; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.3s; border: 1px solid #e2e8f0; text-align: center; height: 100%; }
        .hub-link:hover { transform: translateY(-5px); border-color: #4338ca; color: #4338ca; box-shadow: 0 10px 20px rgba(67, 56, 202, 0.1); }
        .hub-icon { font-size: 1.5rem; margin-bottom: 5px; }

        /* Countdown Cards */
        .cd-card { background: #1e293b; color: white; border-radius: 20px; padding: 20px; position: relative; overflow: hidden; }
        .cd-card::before { content: ""; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #4338ca; }
        .timer-unit { text-align: center; background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 10px; min-width: 55px; }
        .timer-val { display: block; font-size: 1.2rem; font-weight: 800; line-height: 1; }
        .timer-label { font-size: 0.6rem; text-transform: uppercase; opacity: 0.6; }
    </style>
</head>
<body class="p-4">
<div class="container">

    <h6 class="fw-800 text-muted mb-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Recursos Académicos</h6>
    <div class="row g-3 mb-5">
        <div class="col-6 col-md-3">
            <a href="https://aulavirtual.uji.es/" target="_blank" class="hub-link">
                <span class="hub-icon">🎓</span> Aula Virtual
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox" target="_blank" class="hub-link">
                <span class="hub-icon">📧</span> Gmail UJI
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="https://sia.uji.es/" target="_blank" class="hub-link">
                <span class="hub-icon">📑</span> SIA (Expediente)
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="https://www.uji.es/biblioteca/" target="_blank" class="hub-link">
                <span class="hub-icon">📚</span> Biblioteca
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-800 mb-0">⏳ Próximas Entregas</h4>
                <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addE">+ Nuevo Examen</button>
            </div>

            <?php if($examenes): foreach($examenes as $ex): ?>
                <div class="cd-card mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="badge bg-primary mb-2" style="font-size: 0.6rem;"><?= $ex['tipo'] ?></span>
                        <h5 class="fw-800 mb-0"><?= $ex['materia'] ?></h5>
                        <small class="opacity-75">📅 <?= date('d M, Y', strtotime($ex['fecha'])) ?></small>
                    </div>

                    <div class="d-flex gap-2 countdown-engine" data-date="<?= $ex['fecha'] ?> 09:00:00">
                        <div class="timer-unit"><span class="timer-val days">-</span><span class="timer-label">Días</span></div>
                        <div class="timer-unit"><span class="timer-val hours">-</span><span class="timer-label">Horas</span></div>
                        <div class="timer-unit"><span class="timer-val mins">-</span><span class="timer-label">Min</span></div>
                        <div class="timer-unit"><span class="timer-val secs text-primary-u">-</span><span class="timer-label">Seg</span></div>
                    </div>
                    
                    <a href="?del=<?= $ex['id'] ?>" class="btn btn-link text-white opacity-25 p-0 text-decoration-none" onclick="return confirm('¿Borrar examen?')">✕</a>
                </div>
            <?php endforeach; else: ?>
                <div class="card-u text-center py-5 border-dashed">
                    <p class="text-muted mb-0">No tienes exámenes registrados. ¡Relájate!</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card-u h-100">
                <h5 class="fw-800 mb-4 text-primary-u">📍 Hoy en clase</h5>
                <p class="text-muted small">Consulta tu horario en el panel de gestión.</p>
                <a href="gestion_clases.php" class="btn btn-dark w-100 rounded-pill mt-3">Editar Horario</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addE" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4 shadow border-0">
    <h5 class="fw-800 mb-3 text-center">Registrar Examen / Trabajo</h5>
    <form method="POST">
        <select name="tipo" class="form-select mb-3 rounded-pill"><option value="Examen">Examen</option><option value="Trabajo">Trabajo</option></select>
        <input type="text" name="materia" class="form-control mb-3 rounded-pill" placeholder="Nombre de la asignatura" required>
        <input type="date" name="fecha" class="form-control mb-3 rounded-pill" required>
        <textarea name="anotaciones" class="form-control mb-3 rounded-4" placeholder="Alguna nota extra..."></textarea>
        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">Guardar Registro</button>
    </form>
</div></div></div>

<script>
    // MOTOR DE CUENTA ATRÁS MÚLTIPLE
    function updateCountdowns() {
        const timers = document.querySelectorAll('.countdown-engine');
        
        timers.forEach(timer => {
            const targetDate = new Date(timer.getAttribute('data-date')).getTime();
            const now = new Date().getTime();
            const diff = targetDate - now;

            if (diff > 0) {
                timer.querySelector('.days').innerText = Math.floor(diff / (1000 * 60 * 60 * 24));
                timer.querySelector('.hours').innerText = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                timer.querySelector('.mins').innerText = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                timer.querySelector('.secs').innerText = Math.floor((diff % (1000 * 60)) / 1000);
            } else {
                timer.innerHTML = "<span class='badge bg-success'>¡Completado!</span>";
            }
        });
    }

    setInterval(updateCountdowns, 1000);
    updateCountdowns();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>