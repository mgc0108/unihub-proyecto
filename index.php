<?php
// Reporte de errores para ver qué pasa si falla
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/examen.php';

$database = new Database();
$db = $database->getConnection();

// --- ACCIONES ---
if (isset($_GET['delete_ex'])) {
    $db->query("DELETE FROM examenes WHERE id = " . (int)$_GET['delete_ex']);
    header("Location: index.php"); exit();
}

if (isset($_POST['add_examen'])) {
    $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, hora, tipo) VALUES (?, ?, '00:00', 'Tarea')");
    $stmt->execute([$_POST['materia'], $_POST['fecha']]);
    header("Location: index.php"); exit();
}

// --- CARGA SEGURA DE DATOS ---
$dias_esp = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy_nombre = $dias_esp[date('l')];

// 1. Clases (con seguridad si falla el modelo)
try {
    $horarioModel = new Horario($db);
    $clasesHoy = $horarioModel->obtenerHoy();
} catch (Error $e) { $clasesHoy = []; }

// 2. Exámenes (con seguridad si falla el modelo)
try {
    $examenModel = new Examen($db);
    $proximosExamenes = $examenModel->obtenerProximos();
} catch (Error $e) { $proximosExamenes = []; }

// 3. Menú
$menuHoy = $db->query("SELECT * FROM menus WHERE dia_semana = '$hoy_nombre'")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub | UJI - La Vall</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .card-custom { border: none; border-radius: 24px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 25px; margin-bottom: 20px; }
        .fw-800 { font-weight: 800; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 5px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; display: inline-block; margin: 2px; }
    </style>
</head>
<body>
<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-800 mb-0">UniHub 🚀</h1>
        <span class="badge bg-dark rounded-pill"><?= $hoy_nombre ?>, <?= date('d/m') ?></span>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card-custom" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary mb-3">🚌 BUS 360: LA VALL ↔ UJI</h6>
                <div class="row small">
                    <div class="col-6">
                        <span class="text-muted d-block mb-1 fw-bold">➡️ Ida:</span>
                        <?php $ida = ['06:40','07:50','09:10','13:45','15:00']; foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 border-start">
                        <span class="text-muted d-block mb-1 fw-bold">⬅️ Vuelta:</span>
                        <?php $vta = ['12:49','14:53','17:03','19:38','21:39']; foreach($vta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Agenda de hoy</h5>
                <?php foreach($clasesHoy as $c): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="fw-bold me-3 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div><div class="fw-bold"><?= $c['materia'] ?></div><small class="text-muted">Aula <?= $c['aula'] ?></small></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white">
                <small class="text-uppercase fw-bold opacity-75">Hoy se come:</small>
                <h5 class="fw-800 mb-1"><?= $menuHoy ? $menuHoy['plato_principal'] : "Menú no cargado" ?></h5>
                <small class="opacity-75">Postre: <?= $menuHoy ? $menuHoy['postre'] : "-" ?></small>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-800 mb-0">🎯 Tareas</h5>
                    <button class="btn btn-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#addModal">+</button>
                </div>
                <?php foreach($proximosExamenes as $e): ?>
                    <div class="p-3 bg-light rounded-4 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small"><?= $e['materia'] ?></div>
                            <small class="text-primary fw-bold">Faltan <?= $e['dias_restantes'] ?> días</small>
                        </div>
                        <a href="?delete_ex=<?= $e['id'] ?>" class="text-danger fw-bold" onclick="return confirm('¿Borrar?')">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 p-4">
        <h5 class="fw-800 mb-3">Nuevo Registro</h5>
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