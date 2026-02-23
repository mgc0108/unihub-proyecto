<?php
// 1. Configuración de errores y Modelos
require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/menu.php';
require_once 'src/Models/examen.php';

$database = new Database();
$db = $database->getConnection();

$horarioModel = new Horario($db);
$examenModel = new Examen($db);
$menuModel = new Menu($db);

// --- LÓGICA DE ACCIONES (POST y GET) ---

// A. Gestionar Recordatorios (Check y Eliminar)
if (isset($_GET['check_rec'])) {
    $id = (int)$_GET['check_rec'];
    $st = (int)$_GET['st'];
    $db->query("UPDATE recordatorios SET completado = $st WHERE id = $id");
    header("Location: index.php"); exit();
}

// B. Añadir Examen o Trabajo
if (isset($_POST['add_examen'])) {
    $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, hora, tipo, nota_estimada, progreso, anotaciones) VALUES (:mat, :fec, '00:00', 'Tarea', :n_e, 0, '')");
    $stmt->execute([
        ':mat' => $_POST['materia'], 
        ':fec' => $_POST['fecha'], 
        ':n_e' => $_POST['nota_estimada']
    ]);
    header("Location: index.php"); exit();
}

// C. Actualizar o Eliminar Examen/Trabajo
if (isset($_POST['update_examen_pro'])) {
    if (isset($_POST['eliminar_ex'])) {
        $db->query("DELETE FROM examenes WHERE id = " . (int)$_POST['id']);
    } else {
        $stmt = $db->prepare("UPDATE examenes SET progreso = :prog, anotaciones = :anot, nota_estimada = :n_e, nota_sacada = :n_s WHERE id = :id");
        $stmt->execute([
            ':prog' => $_POST['progreso'], ':anot' => $_POST['anotaciones'], 
            ':n_e' => $_POST['nota_estimada'], ':n_s' => $_POST['nota_sacada'], ':id' => $_POST['id']
        ]);
    }
    header("Location: index.php"); exit();
}

// D. Gestionar Horario Semanal
if (isset($_POST['add_clase_directa'])) {
    $stmt = $db->prepare("INSERT INTO horarios (dia_semana, materia, hora_inicio, aula) VALUES (:dia, :mat, :h_i, :aula)");
    $stmt->execute([':dia' => $_POST['dia'], ':mat' => $_POST['materia'], ':h_i' => $_POST['h_inicio'], ':aula' => $_POST['aula']]);
    header("Location: index.php"); exit();
}
if (isset($_GET['del_clase'])) {
    $db->query("DELETE FROM horarios WHERE id = " . (int)$_GET['del_clase']);
    header("Location: index.php"); exit();
}

// 2. CARGA DE DATOS PARA LA VISTA
$clasesHoy = $horarioModel->obtenerSemana(); 
$proximosExamenes = $examenModel->obtenerProximos();
$recs = $db->query("SELECT * FROM recordatorios ORDER BY completado ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$menuHoy = $menuModel->obtenerMenuHoy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub PRO | La Vall - UJI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .card-custom { border: none; border-radius: 24px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 20px; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 4px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; margin-bottom: 4px; display: inline-block; }
        .rec-done { text-decoration: line-through; opacity: 0.5; }
        .progress { height: 8px; border-radius: 10px; background: #f1f5f9; }
        .fw-800 { font-weight: 800; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-800 mb-0">UniHub 🚀</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalGestionarHorario">✏️ Editar Horario</button>
            <a href="horario_semanal.php" class="btn btn-dark btn-sm rounded-pill">📅 Ver Semana</a>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card-custom mb-4" style="border-left: 6px solid #4338ca;">
                <h6 class="fw-800 text-uppercase small mb-3 text-primary">🚌 Bus 360: La Vall ↔ UJI</h6>
                <div class="row small">
                    <div class="col-6">
                        <span class="text-muted d-block mb-2 fw-bold">➡️ Salidas La Vall:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $ida = ['06:40','07:50','09:10','09:50','10:50','11:50','12:50','13:45','15:00','15:50','16:30','17:40','18:30','19:45','20:40']; 
                            foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                    <div class="col-6 border-start">
                        <span class="text-muted d-block mb-2 fw-bold text-danger">⬅️ Regreso UJI:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $vuelta = ['07:39','08:58','10:09','10:58','11:58','12:49','13:49','14:53','15:59','17:03','17:38','18:39','19:38','20:53','21:39']; 
                            foreach($vuelta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Clases para hoy</h5>
                <?php if($clasesHoy): foreach($clasesHoy as $c): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="fw-bold me-3 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div>
                            <div class="fw-bold"><?= $c['materia'] ?></div>
                            <small class="text-muted">Aula <?= $c['aula'] ?></small>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small">No hay clases hoy. ¡A descansar!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white mb-4 shadow-sm" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalMenuSemanal">
                <small class="text-uppercase fw-bold opacity-75">Hoy en la UJI:</small>
                <h4 class="fw-800 mb-0"><?= $menuHoy ? $menuHoy['plato_principal'] : "Pulsa para ver menú" ?></h4>
                <small>Ver menú de la semana →</small>
            </div>

            <div class="card-custom mb-4">
                <h6 class="fw-800 mb-3">📌 No olvidar</h6>
                <form method="POST" class="d-flex gap-2 mb-3">
                    <input type="text" name="texto" class="form-control form-control-sm rounded-pill" placeholder="Llevar algo..." required>
                    <button type="submit" name="add_rec" class="btn btn-primary btn-sm rounded-circle">+</button>
                </form>
                <?php foreach($recs as $r): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <div>
                            <a href="?check_rec=<?= $r['id'] ?>&st=<?= $r['completado'] ? '0' : '1' ?>" class="text-decoration-none me-2">
                                <?= $r['completado'] ? '✅' : '⬜' ?>
                            </a>
                            <span class="<?= $r['completado'] ? 'rec-done' : '' ?>"><?= $r['texto'] ?></span>
                        </div>
                        <a href="?del_rec=<?= $r['id'] ?>" class="text-danger text-decoration-none">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-800 mb-0">🎯 Exámenes y Trabajos</h6>
                    <button class="btn btn-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#modalAddExamen">+</button>
                </div>
                <?php foreach($proximosExamenes as $e): ?>
                    <div class="mb-3 p-3 bg-light rounded-4" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#editExamen<?= $e['id'] ?>">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold small"><?= $e['materia'] ?></span>
                            <span class="badge bg-white text-dark border small">D-<?= $e['dias_restantes'] ?></span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-primary" style="width: <?= $e['progreso'] ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between small" style="font-size: 0.65rem;">
                            <span>Progreso: <?= $e['progreso'] ?>%</span>
                            <span>Est: <b><?= $e['nota_estimada'] ?: '-' ?></b> | Fin: <b><?= $e['nota_sacada'] ?: '-' ?></b></span>
                        </div>
                    </div>

                    <div class="modal fade" id="editExamen<?= $e['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <form method="POST" class="p-4">
                                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                    <h5 class="fw-800 mb-4"><?= $e['materia'] ?></h5>
                                    
                                    <label class="small fw-bold">Nivel de Progreso (%):</label>
                                    <input type="range" name="progreso" class="form-range" value="<?= $e['progreso'] ?>">
                                    
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <label class="small fw-bold text-muted">Nota Estimada:</label>
                                            <input type="number" step="0.1" name="nota_estimada" class="form-control" value="<?= $e['nota_estimada'] ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="small fw-bold text-primary">Nota Final:</label>
                                            <input type="number" step="0.1" name="nota_sacada" class="form-control" value="<?= $e['nota_sacada'] ?>">
                                        </div>
                                    </div>

                                    <label class="small fw-bold mt-3 d-block">Anotaciones (temas, dudas...):</label>
                                    <textarea name="anotaciones" class="form-control small" rows="3"><?= $e['anotaciones'] ?></textarea>
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" name="update_examen_pro" class="btn btn-primary w-100 rounded-pill">Actualizar Todo</button>
                                        <button type="submit" name="eliminar_ex" class="btn btn-outline-danger border-0" onclick="return confirm('¿Borrar definitivamente?')">🗑️</button>
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

<div class="modal fade" id="modalGestionarHorario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow p-4">
            <h5 class="fw-800 mb-4">Configurar Mi Horario Fijo</h5>
            <form method="POST" class="row g-2 mb-4 bg-light p-3 rounded-4">
                <div class="col-md-3"><select name="dia" class="form-select small"><option value="Lunes">Lunes</option><option value="Martes">Martes</option><option value="Miércoles">Miércoles</option><option value="Jueves">Jueves</option><option value="Viernes">Viernes</option></select></div>
                <div class="col-md-4"><input type="text" name="materia" class="form-control" placeholder="Asignatura" required></div>
                <div class="col-md-2"><input type="time" name="h_inicio" class="form-control" required></div>
                <div class="col-md-2"><input type="text" name="aula" class="form-control" placeholder="Aula"></div>
                <div class="col-md-1"><button type="submit" name="add_clase_directa" class="btn btn-primary w-100">+</button></div>
            </form>
            <div class="table-responsive" style="max-height: 250px;">
                <table class="table table-sm small">
                    <tbody>
                        <?php 
                        $horarioAll = $db->query("SELECT * FROM horarios ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($horarioAll as $t): ?>
                        <tr><td><?= $t['dia_semana'] ?></td><td class="fw-bold"><?= $t['materia'] ?></td><td><?= substr($t['hora_inicio'],0,5) ?></td><td><a href="?del_clase=<?= $t['id'] ?>" class="text-danger">✕</a></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddExamen" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 p-4 shadow border-0">
            <h5 class="fw-800 mb-3">Nuevo Examen o Trabajo</h5>
            <form method="POST">
                <input type="text" name="materia" class="form-control mb-2" placeholder="Nombre de la asignatura" required>
                <input type="date" name="fecha" class="form-control mb-2" required>
                <input type="number" step="0.1" name="nota_estimada" class="form-control mb-3" placeholder="Nota que esperas (estimada)">
                <button type="submit" name="add_examen" class="btn btn-primary w-100 rounded-pill">Crear Registro</button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMenuSemanal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 p-4 shadow border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-800 mb-0">🍴 Menú Semanal Cafetería</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="list-group list-group-flush">
                <?php 
                $menuAll = $db->query("SELECT * FROM menus ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')")->fetchAll(PDO::FETCH_ASSOC);
                if($menuAll): foreach($menuAll as $m): ?>
                    <div class="list-group-item px-0">
                        <small class="fw-bold text-primary"><?= $m['dia_semana'] ?></small>
                        <p class="mb-0 small fw-bold"><?= $m['plato_principal'] ?></p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;"><?= $m['postre'] ?></p>
                    </div>
                <?php endforeach; else: echo "<p class='small text-muted'>No hay menú cargado aún.</p>"; endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>