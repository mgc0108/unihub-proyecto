<?php
require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/examen.php';

$database = new Database();
$db = $database->getConnection();
$horarioModel = new Horario($db);
$examenModel = new Examen($db);

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

// --- DATOS ---
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub | La Vall - UJI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .card-custom { border: none; border-radius: 24px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 25px; margin-bottom: 20px; transition: 0.3s; }
        .card-custom:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 6px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; display: inline-block; margin: 3px; border: 1px solid #e0e7ff; }
        .fw-800 { font-weight: 800; }
        .btn-primary { background: #4338ca; border: none; padding: 10px 20px; border-radius: 15px; font-weight: 600; }
        .agenda-time { min-width: 60px; font-weight: 700; color: #4338ca; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-800 mb-0">UniHub 🚀</h1>
            <p class="text-muted small mb-0">Gestión universitaria La Vall - UJI</p>
        </div>
        <div class="d-flex gap-2">
            <a href="horario_semanal.php" class="btn btn-outline-dark btn-sm rounded-pill">📅 Calendario</a>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card-custom" style="border-top: 5px solid #4338ca;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-800 text-primary mb-0">🚌 BUS 360: LA VALL ↔ UJI</h6>
                    <span class="badge bg-light text-primary border">Lectivos</span>
                </div>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted d-block mb-2 fw-bold">➡️ IDA:</small>
                        <div class="d-flex flex-wrap">
                            <?php $ida = ['06:40','07:50','09:10','13:45','15:00','17:40']; foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                    <div class="col-6 border-start">
                        <small class="text-muted d-block mb-2 fw-bold">⬅️ VUELTA:</small>
                        <div class="d-flex flex-wrap">
                            <?php $vta = ['12:49','14:53','17:03','19:38','21:39']; foreach($vta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Agenda de hoy (<?= $hoy_nombre ?>)</h5>
                <?php if($clasesHoy): foreach($clasesHoy as $c): ?>
                    <div class="d-flex align-items-center p-3 mb-2 bg-light rounded-4">
                        <div class="agenda-time"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div class="ms-3">
                            <div class="fw-bold mb-0"><?= $c['materia'] ?></div>
                            <div class="text-muted small">📍 Aula <?= $c['aula'] ?></div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="text-center py-4"><p class="text-muted mb-0">Sin clases para hoy. ✨</p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-uppercase fw-bold opacity-75">Hoy en el menú:</small>
                    <span class="badge bg-white text-success rounded-pill px-3">UJI</span>
                </div>
                <h5 class="fw-800 mb-1"><?= $menuHoy ? $menuHoy['plato_principal'] : "Cargando platos..." ?></h5>
                <p class="mb-0 opacity-75 small">🍰 Postre: <?= $menuHoy ? $menuHoy['postre'] : "Fruta/Yogurt" ?></p>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-800 mb-0">🎯 Entregas</h5>
                    <button class="btn btn-primary btn-sm rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#addExamen">+</button>
                </div>
                <?php if($proximosExamenes): foreach($proximosExamenes as $e): ?>
                    <div class="p-3 border rounded-4 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small"><?= $e['materia'] ?></div>
                            <div class="badge bg-soft-primary text-primary" style="font-size: 0.65rem;">Faltan <?= $e['dias_restantes'] ?> días</div>
                        </div>
                        <a href="?delete_ex=<?= $e['id'] ?>" class="text-danger opacity-50 hover-opacity-100 text-decoration-none px-2" onclick="return confirm('¿Eliminar?')">✕</a>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small text-center py-3">Todo al día. No hay tareas próximas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addExamen" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 p-4 border-0">
        <h5 class="fw-800 mb-3">Nueva Tarea/Examen</h5>
        <form method="POST">
            <div class="mb-3"><label class="small fw-bold">Asignatura</label><input type="text" name="materia" class="form-control rounded-3" required></div>
            <div class="mb-4"><label class="small fw-bold">Fecha</label><input type="date" name="fecha" class="form-control rounded-3" required></div>
            <button type="submit" name="add_examen" class="btn btn-primary w-100 rounded-pill">Guardar Recordatorio</button>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>