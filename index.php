<?php
// ... (Mantén tus require_once de siempre) ...
require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/nota.php';
require_once 'src/Models/menu.php';
require_once 'src/Models/examen.php';

$database = new Database();
$db = $database->getConnection();

$horarioModel = new Horario($db);
$examenModel = new Examen($db);
$menuModel = new Menu($db);

// --- LÓGICA DE RECORDATORIOS ---
if (isset($_POST['add_rec'])) {
    $txt = $_POST['texto'];
    $db->query("INSERT INTO recordatorios (texto) VALUES ('$txt')");
    header("Location: index.php"); exit();
}
if (isset($_GET['del_rec'])) {
    $db->query("DELETE FROM recordatorios WHERE id = ".$_GET['del_rec']);
    header("Location: index.php"); exit();
}

// --- LÓGICA DE EXÁMENES (ACTUALIZAR PROGRESO) ---
if (isset($_POST['update_examen'])) {
    $examenModel->actualizarProgreso($_POST['id'], $_POST['progreso'], $_POST['anotaciones'], $_POST['nota']);
    header("Location: index.php"); exit();
}

$clases = $horarioModel->obtenerSemana();
$proximosExamenes = $examenModel->obtenerProximos();
$recs = $db->query("SELECT * FROM recordatorios ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$menuHoy = $menuModel->obtenerMenuHoy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub | Tu Asistente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .card-custom { border: none; border-radius: 24px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 20px; }
        .bus-card { border-left: 6px solid #4338ca; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 4px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; margin-bottom: 4px; display: inline-block; }
        .progress { height: 8px; border-radius: 10px; background: #f1f5f9; }
        .rec-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-800 mb-0">UniHub 🚀</h1>
        <div class="d-flex gap-2">
            <a href="editar_horario.php" class="btn btn-outline-dark btn-sm rounded-pill">✏️ Editar Horario</a>
            <a href="horario_semanal.php" class="btn btn-dark btn-sm rounded-pill">📅 Ver Semana</a>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card-custom bus-card mb-4">
                <h6 class="fw-800 text-uppercase small mb-3">🚌 Bus 360: La Vall ↔ UJI (Lectivos)</h6>
                <div class="row small">
                    <div class="col-6">
                        <span class="text-muted d-block mb-2 fw-bold">Salidas La Vall:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $h_vall = ['06:40','07:50','09:10','09:50','10:50','11:50','12:50','13:45','15:00','15:50','16:30','17:40','18:30','19:45','20:40']; 
                            foreach($h_vall as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block mb-2 fw-bold">Regreso UJI:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $h_uji = ['07:39','08:58','10:09','10:58','11:58','12:49','13:49','14:53','15:59','17:03','17:38','18:39','19:38','20:53','21:39']; 
                            foreach($h_uji as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Clases para hoy</h5>
                <?php foreach($clases as $c): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="fw-bold me-3 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div>
                            <div class="fw-bold"><?= $c['materia'] ?></div>
                            <small class="text-muted">Aula <?= $c['aula'] ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white mb-4" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalMenu">
                <small class="text-uppercase fw-bold opacity-75">Hoy hay de comer:</small>
                <h4 class="fw-800 mb-0"><?= $menuHoy ? $menuHoy['plato_principal'] : "Ver menú semanal" ?></h4>
            </div>

            <div class="card-custom mb-4">
                <h6 class="fw-800 mb-3">📌 No olvidar</h6>
                <form method="POST" class="d-flex gap-2 mb-3">
                    <input type="text" name="texto" class="form-control form-control-sm rounded-pill" placeholder="Añadir algo..." required>
                    <button type="submit" name="add_rec" class="btn btn-primary btn-sm rounded-circle">+</button>
                </form>
                <?php foreach($recs as $r): ?>
                    <div class="rec-item">
                        <span class="small"><?= $r['texto'] ?></span>
                        <a href="?del_rec=<?= $r['id'] ?>" class="text-danger text-decoration-none small">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-custom">
                <h6 class="fw-800 mb-3">Exámenes y Trabajos</h6>
                <?php foreach($proximosExamenes as $e): ?>
                    <div class="mb-3 p-3 bg-light rounded-4" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#editExamen<?= $e['id'] ?>">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold small"><?= $e['materia'] ?></span>
                            <span class="badge bg-white text-dark border small rounded-pill">Faltan <?= $e['dias_restantes'] ?>d</span>
                        </div>
                        <div class="progress mb-1">
                            <div class="progress-bar bg-primary" style="width: <?= $e['progreso'] ?>%"></div>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">Progreso: <?= $e['progreso'] ?>%</small>
                    </div>

                    <div class="modal fade" id="editExamen<?= $e['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4">
                                <form method="POST">
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <h5 class="fw-800 mb-4"><?= $e['materia'] ?></h5>
                                        <label class="small fw-bold">Progreso de estudio (0-100%):</label>
                                        <input type="range" name="progreso" class="form-range" value="<?= $e['progreso'] ?>">
                                        
                                        <label class="small fw-bold mt-3">Anotaciones:</label>
                                        <textarea name="anotaciones" class="form-control small mb-3"><?= $e['anotaciones'] ?></textarea>
                                        
                                        <label class="small fw-bold">Nota sacada (si ya lo has hecho):</label>
                                        <input type="number" step="0.1" name="nota" class="form-control" value="<?= $e['nota_sacada'] ?>">
                                        
                                        <button type="submit" name="update_examen" class="btn btn-primary w-100 rounded-pill mt-4">Guardar Cambios</button>
                                    </div>
                                </form>
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