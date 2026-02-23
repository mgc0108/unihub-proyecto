<?php
require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/examen.php';

$database = new Database();
$db = $database->getConnection();
$horarioModel = new Horario($db);
$examenModel = new Examen($db);

// --- ACCIONES ---

// Eliminar Tarea/Examen
if (isset($_GET['delete_ex'])) {
    $db->query("DELETE FROM examenes WHERE id = " . (int)$_GET['delete_ex']);
    header("Location: index.php"); exit();
}

// Añadir Tarea/Examen (Solo materia y fecha)
if (isset($_POST['add_examen'])) {
    $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, hora, tipo) VALUES (?, ?, '00:00', 'Tarea')");
    $stmt->execute([$_POST['materia'], $_POST['fecha']]);
    header("Location: index.php"); exit();
}

// --- MENÚ DE HOY ---
$dias_esp = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy_nombre = $dias_esp[date('l')];

try {
    $stmt_m = $db->prepare("SELECT * FROM menus WHERE dia_semana = ?");
    $stmt_m->execute([$hoy_nombre]);
    $menuHoy = $stmt_m->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $menuHoy = false;
}

$clasesHoy = $horarioModel->obtenerHoy(); 
$proximosExamenes = $examenModel->obtenerProximos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub | La Vall - UJI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .card-custom { border: none; border-radius: 24px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 20px; margin-bottom: 20px; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 4px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; margin-bottom: 4px; display: inline-block; }
        .fw-800 { font-weight: 800; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-800 mb-0">UniHub 🚀</h1>
        <a href="horario_semanal.php" class="btn btn-dark btn-sm rounded-pill">📅 Horario Completo</a>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card-custom" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary small mb-3">🚌 BUS 360: LA VALL ↔ UJI</h6>
                <div class="row small">
                    <div class="col-6">
                        <span class="text-muted d-block mb-1 fw-bold">➡️ Ida:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $ida = ['06:40','07:50','09:10','13:45','15:00','17:40']; 
                            foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                    <div class="col-6 border-start">
                        <span class="text-muted d-block mb-1 fw-bold text-danger">⬅️ Vuelta:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $vuelta = ['12:49','14:53','17:03','19:38','21:39']; 
                            foreach($vuelta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Clases de hoy</h5>
                <?php if($clasesHoy): foreach($clasesHoy as $c): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="fw-bold me-3 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div><div class="fw-bold"><?= $c['materia'] ?></div><small class="text-muted">Aula <?= $c['aula'] ?></small></div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small">Día libre de clases.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white">
                <small class="text-uppercase fw-bold opacity-75">Hoy se come:</small>
                <h5 class="fw-800 mb-1"><?= $menuHoy ? $menuHoy['plato_principal'] : "Menú no disponible" ?></h5>
                <small class="opacity-75"><?= $menuHoy ? "Postre: " . $menuHoy['postre'] : "¡Buen provecho!" ?></small>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-800 mb-0">🎯 Próximas Entregas</h6>
                    <button class="btn btn-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#modalAddExamen">+</button>
                </div>
                <?php if($proximosExamenes): foreach($proximosExamenes as $e): ?>
                    <div class="p-3 bg-light rounded-4 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small"><?= $e['materia'] ?></div>
                            <small class="text-primary fw-bold" style="font-size: 0.7rem;">Faltan <?= $e['dias_restantes'] ?> días</small>
                        </div>
                        <a href="?delete_ex=<?= $e['id'] ?>" class="text-danger fw-bold text-decoration-none" onclick="return confirm('¿Borrar tarea?')">✕</a>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small text-center">Todo al día ✨</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddExamen" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 p-4 border-0 shadow">
        <h5 class="fw-800 mb-3">Añadir Tarea/Examen</h5>
        <form method="POST">
            <input type="text" name="materia" class="form-control mb-2" placeholder="Asignatura" required>
            <input type="date" name="fecha" class="form-control mb-3" required>
            <button type="submit" name="add_examen" class="btn btn-primary w-100 rounded-pill">Guardar</button>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>