<?php
require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/examen.php';

$database = new Database();
$db = $database->getConnection();
$horarioModel = new Horario($db);
$examenModel = new Examen($db);

// Acciones
if (isset($_GET['delete_ex'])) {
    $db->query("DELETE FROM examenes WHERE id = " . (int)$_GET['delete_ex']);
    header("Location: index.php"); exit();
}

$clasesHoy = $horarioModel->obtenerHoy(); 
$proximosExamenes = $examenModel->obtenerProximos();
$dias_esp = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy_nombre = $dias_esp[date('l')];

$stmt_m = $db->prepare("SELECT * FROM menus WHERE dia_semana = ?");
$stmt_m->execute([$hoy_nombre]);
$menuHoy = $stmt_m->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniHub | La Vall - UJI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .card-custom { border: none; border-radius: 24px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 25px; margin-bottom: 20px; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 6px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; display: inline-block; margin: 2px; border: 1px solid #e0e7ff; }
        .fw-800 { font-weight: 800; }
        .btn-uji { background: #4338ca; color: white; border-radius: 15px; font-weight: 600; border: none; }
        .btn-uji:hover { background: #3730a3; color: white; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-800 mb-0">UniHub 🚀</h1>
        <div class="d-flex gap-2">
            <a href="gestion_clases.php" class="btn btn-outline-dark btn-sm rounded-pill">⚙️ Gestionar Clases</a>
            <span class="badge bg-dark rounded-pill py-2 px-3"><?= $hoy_nombre ?>, <?= date('d/m') ?></span>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card-custom" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary mb-3">🚌 BUS 360: LA VALL ↔ UJI (Horario Completo)</h6>
                <div class="row small">
                    <div class="col-6">
                        <span class="text-muted d-block mb-1 fw-bold">➡️ Ida (Salida La Vall):</span>
                        <?php $ida = ['06:40','07:50','09:10','11:10','13:45','15:00','17:40','20:40']; foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 border-start">
                        <span class="text-muted d-block mb-1 fw-bold text-danger">⬅️ Vuelta (Salida UJI):</span>
                        <?php $vta = ['07:39','10:14','12:49','14:53','17:03','19:38','21:39']; foreach($vta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Agenda de hoy</h5>
                <?php if($clasesHoy): foreach($clasesHoy as $c): ?>
                    <div class="d-flex align-items-center mb-3 p-2 bg-light rounded-4">
                        <div class="fw-bold me-3 text-primary ps-2"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div><div class="fw-bold"><?= $c['materia'] ?></div><small class="text-muted">Aula <?= $c['aula'] ?></small></div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small text-center">No tienes clases hoy. ¡A descansar! 🌴</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-uppercase fw-bold opacity-75">Hoy se come:</small>
                    <a href="menu_semanal.php" class="btn btn-sm btn-light py-0 px-2 rounded-pill" style="font-size: 0.7rem;">Ver semana 📅</a>
                </div>
                <h5 class="fw-800 mb-1"><?= $menuHoy ? $menuHoy['plato_principal'] : "Menú no disponible" ?></h5>
                <small class="opacity-75">Postre: <?= $menuHoy ? $menuHoy['postre'] : "-" ?></small>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-800 mb-0">🎯 Tareas y Recordatorios</h5>
                    <button class="btn btn-uji btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#addExamen">+</button>
                </div>
                <?php foreach($proximosExamenes as $e): ?>
                    <div class="p-3 border rounded-4 mb-2 d-flex justify-content-between align-items-center">
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

<div class="modal fade" id="addExamen" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 p-4">
        <h5 class="fw-800 mb-3">Nuevo Recordatorio</h5>
        <form action="index.php" method="POST">
            <input type="text" name="materia" class="form-control mb-2" placeholder="¿Qué tienes que hacer?" required>
            <input type="date" name="fecha" class="form-control mb-3" required>
            <button type="submit" name="add_examen" class="btn btn-uji w-100 p-2">Guardar</button>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>