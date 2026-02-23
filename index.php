<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// --- LÓGICA DE PROCESAMIENTO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. ACTUALIZAR NOTA REAL DE EXAMEN EXISTENTE
    if (isset($_POST['update_nota'])) {
        $stmt = $db->prepare("UPDATE examenes SET nota_sacada = ? WHERE id = ?");
        $stmt->execute([$_POST['nota_sacada'], $_POST['examen_id']]);
        header("Location: index.php"); exit;
    }
    
    // 2. REGISTRO NUEVO (TAREA O EXAMEN)
    if (isset($_POST['materia'])) {
        $tipo = (isset($_POST['tipo']) && $_POST['tipo'] === 'Examen') ? 'Examen' : 'Tarea';
        $materia = $_POST['materia'];
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $n_est = ($_POST['nota_estimada'] !== '' && $_POST['nota_estimada'] !== null) ? $_POST['nota_estimada'] : NULL;
        $anot = $_POST['anotaciones'] ?? '';

        try {
            $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, hora, tipo, nota_estimada, anotaciones, completado) VALUES (?, ?, '00:00', ?, ?, ?, 0)");
            $stmt->execute([$materia, $fecha, $tipo, $n_est, $anot]);
            header("Location: index.php"); exit;
        } catch (PDOException $e) { die("Error en UniMa: " . $e->getMessage()); }
    }
}

// --- ACCIONES RÁPIDAS (GET) ---
if(isset($_GET['toggle'])) { $db->query("UPDATE examenes SET completado = 1 - completado WHERE id = ".(int)$_GET['toggle']); header("Location: index.php"); exit; }
if(isset($_GET['del'])) { $db->query("DELETE FROM examenes WHERE id = ".(int)$_GET['del']); header("Location: index.php"); exit; }

// --- CARGA DE DATOS ---
$dias_trad = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$hoy = $dias_trad[date('l')];

$clases = $db->query("SELECT * FROM horarios WHERE dia_semana = '$hoy' ORDER BY hora_inicio ASC")->fetchAll(PDO::FETCH_ASSOC);
$tareas = $db->query("SELECT * FROM examenes WHERE tipo = 'Tarea' ORDER BY completado ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$exas = $db->query("SELECT * FROM examenes WHERE tipo = 'Examen' ORDER BY fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMa | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .card-u { background: white; border-radius: 24px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 20px; border: none; }
        .fw-800 { font-weight: 800; }
        .strikethrough { text-decoration: line-through; opacity: 0.5; }
        .check-btn { width: 22px; height: 22px; border: 2px solid #cbd5e1; border-radius: 6px; cursor: pointer; display: inline-block; transition: 0.2s; }
        .check-btn.done { background: #22c55e; border-color: #22c55e; position: relative; }
        .check-btn.done::after { content: '✓'; color: white; position: absolute; left: 4px; top: -3px; font-weight: bold; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 4px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; margin: 2px; display: inline-block; }
        .text-primary { color: #4338ca !important; }
    </style>
</head>
<body class="p-4">
<div class="container">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-800 mb-0 text-primary" style="letter-spacing: -1px;">UniMa 🚀</h1>
            <p class="text-muted small fw-600 mb-0">UJI - Lucia P</p>
        </div>
        <a href="gestion_clases.php" class="btn btn-dark rounded-pill px-4 shadow-sm">⚙️ Gestionar Clases</a>
    </header>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-u" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-primary mb-3">🚌 BUS 360 (Horario PDF)</h6>
                <div class="row small text-center">
                    <div class="col-6"><span>➡️ Salida La Vall:</span><br>
                        <?php $ida=['06:40','07:50','09:10','11:10','13:45','15:00','17:40','20:40']; foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 border-start"><span>⬅️ Salida UJI:</span><br>
                        <?php $vta=['07:39','10:14','12:49','14:53','17:03','19:38','21:39']; foreach($vta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                    </div>
                </div>
            </div>

            <div class="card-u">
                <h5 class="fw-800 mb-4">Agenda de hoy (<?= $hoy ?>)</h5>
                <?php if($clases): foreach($clases as $c): ?>
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-4 border-start border-primary border-4">
                        <div class="fw-bold me-4 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div><div class="fw-bold"><?= $c['materia'] ?></div><small class="text-muted">📍 Aula <?= $c['aula'] ?></small></div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small">No hay clases hoy. ¡Aprovecha el tiempo!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-u bg-success text-white">
                <small class="fw-bold opacity-75">MENÚ CAFETERÍA UJI</small>
                <h5 class="fw-800 mb-3">Actualización Semanal</h5>
                <a href="https://ujiapps.uji.es/ade/rest/storage/P4LFVSDOBJDUDE3EQFGR21LJEBWR1W31" target="_blank" class="btn btn-light w-100 fw-bold rounded-pill text-success">📂 Abrir Menú PDF</a>
            </div>

            <div class="card-u">
                <div class="d-flex justify-content-between mb-4"><h5 class="fw-800 mb-0">✅ Tareas</h5><button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addT">+</button></div>
                <?php foreach($tareas as $t): ?>
                    <div class="d-flex justify-content-between mb-2 p-1 border-bottom border-light">
                        <div><a href="?toggle=<?= $t['id'] ?>" class="check-btn me-2 <?= $t['completado']?'done':'' ?>"></a> <span class="<?= $t['completado']?'strikethrough':'' ?>"><?= $t['materia'] ?></span></div>
                        <a href="?del=<?= $t['id'] ?>" class="text-danger opacity-25">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-u">
                <div class="d-flex justify-content-between mb-4"><h5 class="fw-800 mb-0">📝 Exámenes</h5><button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addE">+</button></div>
                <?php foreach($exas as $e): ?>
                    <div class="p-3 border rounded-4 mb-3 position-relative bg-light shadow-sm">
                        <div class="fw-bold small mb-2 text-uppercase text-muted" style="font-size: 0.7rem;"><?= $e['materia'] ?></div>
                        <div class="row g-2 small text-center align-items-center">
                            <div class="col-5">
                                <span class="d-block opacity-75" style="font-size: 0.6rem;">PREVISTA</span>
                                <b class="fs-6"><?= $e['nota_estimada'] ?? '-' ?></b>
                            </div>
                            <div class="col-2 text-muted">→</div>
                            <div class="col-5">
                                <span class="d-block opacity-75" style="font-size: 0.6rem;">REAL</span>
                                <?php if($e['nota_sacada'] !== null): ?>
                                    <b class="fs-6 text-success"><?= $e['nota_sacada'] ?></b>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" style="font-size: 0.6rem;" data-bs-toggle="modal" data-bs-target="#editNota<?= $e['id'] ?>">+ NOTA</button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="modal fade" id="editNota<?= $e['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-sm modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4 p-3">
                                    <h6 class="fw-800 mb-3">Nota Real: <?= $e['materia'] ?></h6>
                                    <form method="POST">
                                        <input type="hidden" name="examen_id" value="<?= $e['id'] ?>">
                                        <input type="number" step="0.1" name="nota_sacada" class="form-control mb-3" placeholder="Nota obtenida" required>
                                        <button type="submit" name="update_nota" class="btn btn-primary w-100 rounded-pill">Guardar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <a href="?del=<?= $e['id'] ?>" class="position-absolute top-0 end-0 m-2 text-danger opacity-25">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addT" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4 shadow border-0">
    <h5 class="fw-800">Nueva Tarea</h5><form method="POST"><input type="hidden" name="tipo" value="Tarea"><input type="text" name="materia" class="form-control mb-3" placeholder="¿Qué hay que hacer?" required><button type="submit" class="btn btn-primary w-100 rounded-pill">Añadir</button></form>
</div></div></div>

<div class="modal fade" id="addE" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4 rounded-4 shadow border-0">
    <h5 class="fw-800">Nuevo Examen/Trabajo</h5><form method="POST"><input type="hidden" name="tipo" value="Examen">
    <label class="small text-muted mb-1">Asignatura</label><input type="text" name="materia" class="form-control mb-2" required>
    <label class="small text-muted mb-1">Fecha</label><input type="date" name="fecha" class="form-control mb-2">
    <label class="small text-muted mb-1">Nota estimada</label><input type="number" step="0.1" name="nota_estimada" class="form-control mb-3" placeholder="Ej: 9.0">
    <textarea name="anotaciones" class="form-control mb-3" placeholder="Anotaciones (temario, etc)..."></textarea>
    <button type="submit" class="btn btn-primary w-100 rounded-pill">Registrar</button></form>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>