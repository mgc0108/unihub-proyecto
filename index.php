<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

// --- ACCIONES ---
if(isset($_GET['toggle'])) {
    $db->query("UPDATE examenes SET completado = 1 - completado WHERE id = ".(int)$_GET['toggle']);
    header("Location: index.php"); exit;
}
if(isset($_GET['del_ex'])) {
    $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del_ex']);
    header("Location: index.php"); exit;
}

// --- DATOS ---
$hoy_ing = date('l');
$trad = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy = $trad[$hoy_ing];

$clases = $db->query("SELECT * FROM horarios WHERE dia_semana = '$hoy' ORDER BY hora_inicio")->fetchAll(PDO::FETCH_ASSOC);
$menu = $db->query("SELECT * FROM menus WHERE dia_semana = '$hoy'")->fetch(PDO::FETCH_ASSOC);
$tareas = $db->query("SELECT * FROM examenes WHERE tipo = 'Tarea' ORDER BY completado ASC, fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
$examenes = $db->query("SELECT * FROM examenes WHERE tipo = 'Examen' ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
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
        .card-custom { background: white; border-radius: 24px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 24px; border: none; }
        .fw-800 { font-weight: 800; }
        .strikethrough { text-decoration: line-through; opacity: 0.5; }
        .check-btn { width: 22px; height: 22px; border: 2px solid #cbd5e1; border-radius: 6px; cursor: pointer; display: inline-block; transition: 0.2s; vertical-align: middle; }
        .check-btn.done { background: #22c55e; border-color: #22c55e; position: relative; }
        .check-btn.done::after { content: '✓'; color: white; position: absolute; left: 4px; top: -3px; font-weight: bold; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 4px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; margin: 2px; display: inline-block; border: 1px solid #e0e7ff; }
    </style>
</head>
<body class="p-4">
<div class="container">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div><h1 class="fw-800 mb-0">UniHub 🚀</h1><p class="text-muted small mb-0">Dashboard Universitario</p></div>
        <a href="gestion_clases.php" class="btn btn-dark rounded-pill px-4">⚙️ Gestionar Horario</a>
    </header>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-custom" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary mb-3">🚌 BUS 360: LA VALL ↔ UJI (Completo)</h6>
                <div class="row small">
                    <div class="col-6"><span class="text-muted d-block mb-1">➡️ Ida:</span>
                        <?php $ida = ['06:40','07:50','09:10','11:10','13:45','15:00','17:40','20:40']; foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 border-start"><span class="text-muted d-block mb-1">⬅️ Vuelta:</span>
                        <?php $vta = ['07:39','10:14','12:49','14:53','17:03','19:38','21:39']; foreach($vta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4 text-primary">Agenda de hoy (<?= $hoy ?>)</h5>
                <?php if($clases): foreach($clases as $c): ?>
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-4">
                        <div class="fw-bold me-4 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div><div class="fw-bold"><?= $c['materia'] ?></div><small class="text-muted">Aula <?= $c['aula'] ?></small></div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small">No hay clases registradas para hoy.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-custom bg-success text-white">
                <div class="d-flex justify-content-between mb-2">
                    <small class="fw-bold opacity-75">Hoy se come:</small>
                    <a href="https://ujiapps.uji.es/ade/rest/storage/P4LFVSDOBJDUDE3EQFGR21LJEBWR1W31" target="_blank" class="btn btn-sm btn-light py-0 px-2 rounded-pill" style="font-size:10px">Ver Menú Completo 🔗</a>
                </div>
                <h5 class="fw-800 mb-1"><?= $menu['plato_principal'] ?? 'Menú no cargado' ?></h5>
                <small class="opacity-75">🍰 <?= $menu['postre'] ?? '-' ?></small>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between mb-4"><h5 class="fw-800 mb-0">✅ Recordatorios</h5><button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addTarea">+</button></div>
                <?php foreach($tareas as $t): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <a href="?toggle=<?= $t['id'] ?>" class="check-btn me-3 <?= $t['completado']?'done':'' ?>"></a>
                            <span class="<?= $t['completado']?'strikethrough':'' ?>"><?= $t['materia'] ?></span>
                        </div>
                        <a href="?del_ex=<?= $t['id'] ?>" class="text-danger opacity-25">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between mb-4"><h5 class="fw-800 mb-0">📝 Exámenes / Notas</h5><button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addEx">+</button></div>
                <?php foreach($examenes as $e): ?>
                    <div class="p-3 border rounded-4 mb-3 position-relative">
                        <div class="fw-bold"><?= $e['materia'] ?> <small class="text-muted">(<?= $e['fecha'] ?>)</small></div>
                        <div class="row g-2 mt-2 small">
                            <div class="col-6"><span class="badge bg-light text-dark w-100 p-2">Previsto: <?= $e['nota_estimada'] ?></span></div>
                            <div class="col-6"><span class="badge bg-soft-success text-success w-100 p-2 border">Real: <?= $e['nota_sacada'] ?: '-' ?></span></div>
                        </div>
                        <?php if($e['anotaciones']): ?><div class="mt-2 p-2 bg-light rounded text-muted small"><i><?= $e['anotaciones'] ?></i></div><?php endif; ?>
                        <a href="?del_ex=<?= $e['id'] ?>" class="position-absolute top-0 end-0 m-2 text-danger opacity-25">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addTarea" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4 rounded-4 shadow border-0">
    <h5 class="fw-800 mb-3">Nuevo Recordatorio</h5>
    <form action="procesar.php" method="POST"><input type="hidden" name="tipo" value="Tarea"><input type="text" name="materia" class="form-control mb-3" placeholder="¿Qué tienes que llevar o hacer?" required><button type="submit" class="btn btn-primary w-100">Guardar</button></form>
</div></div></div>

<div class="modal fade" id="addEx" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4 rounded-4 shadow border-0">
    <h5 class="fw-800 mb-3">Añadir Examen / Trabajo</h5>
    <form action="procesar.php" method="POST"><input type="hidden" name="tipo" value="Examen"><input type="text" name="materia" class="form-control mb-2" placeholder="Asignatura" required><input type="date" name="fecha" class="form-control mb-2" required>
        <div class="row mb-2"><div class="col-6"><input type="number" step="0.1" name="nota_estimada" class="form-control" placeholder="Nota prevista"></div><div class="col-6"><input type="number" step="0.1" name="nota_sacada" class="form-control" placeholder="Nota real"></div></div>
        <textarea name="anotaciones" class="form-control mb-3" placeholder="Anotaciones / Temario..."></textarea><button type="submit" class="btn btn-primary w-100">Guardar</button>
    </form>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>