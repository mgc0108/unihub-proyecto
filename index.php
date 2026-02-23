<?php
// 1. ESCUDO CONTRA ERRORES (Para ver el fallo real si ocurre)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
$db = (new Database())->getConnection();

// 2. LÓGICA DE GUARDADO (Todo en uno para evitar Error 500)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['materia'])) {
        $tipo = $_POST['tipo'] ?? 'Tarea';
        $materia = $_POST['materia'];
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $n_est = !empty($_POST['nota_estimada']) ? $_POST['nota_estimada'] : NULL;
        $n_sac = !empty($_POST['nota_sacada']) ? $_POST['nota_sacada'] : NULL;
        $anot = $_POST['anotaciones'] ?? '';

        $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, hora, tipo, nota_estimada, nota_sacada, anotaciones) VALUES (?, ?, '00:00', ?, ?, ?, ?)");
        $stmt->execute([$materia, $fecha, $tipo, $n_est, $n_sac, $anot]);
        header("Location: index.php"); exit;
    }
}

// Lógica de borrar y check
if(isset($_GET['toggle'])) { $db->query("UPDATE examenes SET completado = 1 - completado WHERE id = ".(int)$_GET['toggle']); header("Location: index.php"); exit; }
if(isset($_GET['del'])) { $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del']); header("Location: index.php"); exit; }

// 3. CARGA DE DATOS
$trad = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy = $trad[date('l')];

$clases = $db->query("SELECT * FROM horarios WHERE dia_semana = '$hoy' ORDER BY hora_inicio")->fetchAll(PDO::FETCH_ASSOC);
$menu = $db->query("SELECT * FROM menus WHERE dia_semana = '$hoy'")->fetch(PDO::FETCH_ASSOC);
$tareas = $db->query("SELECT * FROM examenes WHERE tipo = 'Tarea' ORDER BY completado ASC, fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
$exas = $db->query("SELECT * FROM examenes WHERE tipo = 'Examen' ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UniHub | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .card-u { background: white; border-radius: 24px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 20px; border: none; }
        .fw-800 { font-weight: 800; }
        .strikethrough { text-decoration: line-through; opacity: 0.5; }
        .check-btn { width: 22px; height: 22px; border: 2px solid #cbd5e1; border-radius: 6px; cursor: pointer; display: inline-block; }
        .check-btn.done { background: #22c55e; border-color: #22c55e; position: relative; }
        .check-btn.done::after { content: '✓'; color: white; position: absolute; left: 4px; top: -3px; font-weight: bold; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 4px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; margin: 2px; display: inline-block; }
    </style>
</head>
<body class="p-4">
<div class="container">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-800 mb-0">UniHub 🚀</h1>
        <a href="gestion_clases.php" class="btn btn-dark rounded-pill px-4">⚙️ Gestionar Clases</a>
    </header>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-u" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary mb-3">🚌 BUS 360: LA VALL ↔ UJI</h6>
                <div class="row small">
                    <div class="col-6"><span>➡️ Ida:</span><br>
                        <?php $ida=['06:40','07:50','09:10','11:10','13:45','15:00','17:40','20:40']; foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 border-start"><span>⬅️ Vuelta:</span><br>
                        <?php $vta=['07:39','10:14','12:49','14:53','17:03','19:38','21:39']; foreach($vta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                    </div>
                </div>
            </div>

            <div class="card-u">
                <h5 class="fw-800 mb-4 text-primary">Agenda de hoy (<?= $hoy ?>)</h5>
                <?php foreach($clases as $c): ?>
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-4">
                        <div class="fw-bold me-4 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div><div class="fw-bold"><?= $c['materia'] ?></div><small class="text-muted">Aula <?= $c['aula'] ?></small></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-u bg-success text-white">
                <small class="fw-bold opacity-75">MENÚ UJI HOY:</small>
                <h5 class="fw-800 mb-1"><?= $menu['plato_principal'] ?? 'Consulta el PDF' ?></h5>
                <small class="opacity-75">🍰 <?= $menu['postre'] ?? 'Fruta/Yogurt' ?></small>
            </div>

            <div class="card-u">
                <div class="d-flex justify-content-between mb-4"><h5 class="fw-800 mb-0">✅ Tareas</h5><button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addT">+</button></div>
                <?php foreach($tareas as $t): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <div><a href="?toggle=<?= $t['id'] ?>" class="check-btn me-2 <?= $t['completado']?'done':'' ?>"></a> <span class="<?= $t['completado']?'strikethrough':'' ?>"><?= $t['materia'] ?></span></div>
                        <a href="?del=<?= $t['id'] ?>" class="text-danger opacity-25">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-u">
                <div class="d-flex justify-content-between mb-4"><h5 class="fw-800 mb-0">📝 Exámenes</h5><button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addE">+</button></div>
                <?php foreach($exas as $e): ?>
                    <div class="p-3 border rounded-4 mb-3 position-relative">
                        <div class="fw-bold"><?= $e['materia'] ?></div>
                        <div class="row g-2 mt-2 small text-center">
                            <div class="col-6"><div class="bg-light p-1 rounded">Prev: <?= $e['nota_estimada'] ?></div></div>
                            <div class="col-6"><div class="bg-soft-success p-1 rounded border text-success">Real: <?= $e['nota_sacada'] ?: '-' ?></div></div>
                        </div>
                        <?php if($e['anotaciones']): ?><div class="mt-2 small text-muted"><i><?= $e['anotaciones'] ?></i></div><?php endif; ?>
                        <a href="?del=<?= $e['id'] ?>" class="position-absolute top-0 end-0 m-2 text-danger opacity-25">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addT" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4"><h5 class="fw-800 mb-3">Añadir Tarea</h5>
    <form method="POST"><input type="hidden" name="tipo" value="Tarea"><input type="text" name="materia" class="form-control mb-3" placeholder="¿Qué hay que hacer?" required><button type="submit" class="btn btn-primary w-100">Guardar</button></form>
</div></div></div>

<div class="modal fade" id="addE" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4"><h5 class="fw-800 mb-3">Nuevo Examen/Trabajo</h5>
    <form method="POST"><input type="hidden" name="tipo" value="Examen"><input type="text" name="materia" class="form-control mb-2" placeholder="Asignatura" required><input type="date" name="fecha" class="form-control mb-2">
    <div class="row mb-2"><div class="col-6"><input type="number" step="0.1" name="nota_estimada" class="form-control" placeholder="Nota prevista"></div><div class="col-6"><input type="number" step="0.1" name="nota_sacada" class="form-control" placeholder="Nota real"></div></div>
    <textarea name="anotaciones" class="form-control mb-3" placeholder="Notas..."></textarea><button type="submit" class="btn btn-primary w-100">Guardar</button></form>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>