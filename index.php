<?php
// ... (Mantén tus includes y conexión al principio) ...
require_once 'config/database.php';
require_once 'src/Models/horario.php';
require_once 'src/Models/menu.php';
require_once 'src/Models/examen.php';

$database = new Database();
$db = $database->getConnection();
$horarioModel = new Horario($db);
$examenModel = new Examen($db);
$menuModel = new Menu($db);

// --- LÓGICA DE ACCIONES ---

// Recordatorios: Check y Borrar
if (isset($_GET['check_rec'])) {
    $id = $_GET['check_rec'];
    $estado = $_GET['st'];
    $db->query("UPDATE recordatorios SET completado = $estado WHERE id = $id");
    header("Location: index.php"); exit();
}

// Exámenes: Añadir y Borrar
if (isset($_POST['add_examen'])) {
    $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, hora, tipo, nota_estimada) VALUES (:mat, :fec, '00:00', 'Examen', :n_e)");
    $stmt->execute([':mat' => $_POST['materia'], ':fec' => $_POST['fecha'], ':n_e' => $_POST['nota_estimada']]);
    header("Location: index.php"); exit();
}

// Actualizar Examen (Progreso, Notas y Borrar)
if (isset($_POST['update_examen_pro'])) {
    if (isset($_POST['eliminar_ex'])) {
        $db->query("DELETE FROM examenes WHERE id = " . $_POST['id']);
    } else {
        $stmt = $db->prepare("UPDATE examenes SET progreso = :prog, anotaciones = :anot, nota_estimada = :n_e, nota_sacada = :n_s WHERE id = :id");
        $stmt->execute([
            ':prog' => $_POST['progreso'], ':anot' => $_POST['anotaciones'], 
            ':n_e' => $_POST['nota_estimada'], ':n_s' => $_POST['nota_sacada'], ':id' => $_POST['id']
        ]);
    }
    header("Location: index.php"); exit();
}

// ... (Carga de datos igual que antes) ...
$proximosExamenes = $examenModel->obtenerProximos();
$recs = $db->query("SELECT * FROM recordatorios ORDER BY completado ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$menuHoy = $menuModel->obtenerMenuHoy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHub PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        .card-custom { border: none; border-radius: 20px; background: white; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .rec-done { text-decoration: line-through; opacity: 0.5; }
        .bus-badge { background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
        .fw-800 { font-weight: 800; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-800 mb-0">UniHub 🚀</h1>
        <button class="btn btn-dark rounded-pill px-4 btn-sm" data-bs-toggle="modal" data-bs-target="#modalGestionarHorario">✏️ Editar Horario</button>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card-custom border-start border-primary border-5">
                <h6 class="fw-800 text-primary small mb-3">🚌 LÍNEA 360: LA VALL ↔ UJI</h6>
                <div class="row">
                    <div class="col-6">
                        <small class="fw-bold d-block mb-2">➡️ Salida La Vall:</small>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $ida = ['06:40','07:50','09:10','09:50','10:50','11:50','12:50','13:45','15:00','15:50','16:30','17:40','18:30','19:45','20:40']; 
                            foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                    <div class="col-6 border-start">
                        <small class="fw-bold d-block mb-2 text-danger">⬅️ Regreso UJI:</small>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $vuelta = ['07:39','08:58','10:09','10:58','11:58','12:49','13:49','14:53','15:59','17:03','17:38','18:39','19:38','20:53','21:39']; 
                            foreach($vuelta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800">Agenda de hoy</h5>
                </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-custom bg-success text-white" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalMenuSemanal">
                <small class="text-uppercase fw-bold opacity-75">Hoy hay:</small>
                <h5 class="fw-800 mb-1"><?= $menuHoy ? $menuHoy['plato_principal'] : "Pulsa para ver" ?></h5>
                <small>Ver menú de la semana →</small>
            </div>

            <div class="card-custom">
                <h6 class="fw-800 mb-3">📌 No olvidar</h6>
                <form method="POST" class="d-flex gap-2 mb-3">
                    <input type="text" name="texto" class="form-control form-control-sm" placeholder="Añadir..." required>
                    <button type="submit" name="add_rec" class="btn btn-primary btn-sm">+</button>
                </form>
                <?php foreach($recs as $r): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <a href="?check_rec=<?= $r['id'] ?>&st=<?= $r['completado'] ? '0' : '1' ?>" class="text-decoration-none me-2">
                                <?= $r['completado'] ? '✅' : '⬜' ?>
                            </a>
                            <span class="small <?= $r['completado'] ? 'rec-done' : '' ?>"><?= $r['texto'] ?></span>
                        </div>
                        <a href="?del_rec=<?= $r['id'] ?>" class="text-danger small">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-800 mb-0">🎯 Exámenes</h6>
                    <button class="btn btn-outline-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#modalAddExamen">+</button>
                </div>
                <?php foreach($proximosExamenes as $e): ?>
                    <div class="p-3 bg-light rounded-4 mb-2" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#editExamen<?= $e['id'] ?>">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold small"><?= $e['materia'] ?></span>
                            <span class="badge bg-white text-dark border small">D-<?= $e['dias_restantes'] ?></span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar" style="width: <?= $e['progreso'] ?>%"></div>
                        </div>
                    </div>

                    <div class="modal fade" id="editExamen<?= $e['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4">
                                <form method="POST" class="p-4">
                                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                    <h5 class="fw-800 mb-4"><?= $e['materia'] ?></h5>
                                    
                                    <label class="small fw-bold">Progreso:</label>
                                    <input type="range" name="progreso" class="form-range" value="<?= $e['progreso'] ?>">
                                    
                                    <div class="row mt-3 g-2">
                                        <div class="col-6">
                                            <label class="small fw-bold">Nota estimada:</label>
                                            <input type="number" step="0.1" name="nota_estimada" class="form-control" value="<?= $e['nota_estimada'] ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="small fw-bold">Nota final:</label>
                                            <input type="number" step="0.1" name="nota_sacada" class="form-control" value="<?= $e['nota_sacada'] ?>">
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex gap-2">
                                        <button type="submit" name="update_examen_pro" class="btn btn-primary w-100 rounded-pill">Guardar</button>
                                        <button type="submit" name="eliminar_ex" class="btn btn-outline-danger rounded-pill" onclick="return confirm('¿Borrar?')">🗑️</button>
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

<div class="modal fade" id="modalAddExamen" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 p-4">
            <h5 class="fw-800 mb-3">Nuevo Examen/Trabajo</h5>
            <form method="POST">
                <input type="text" name="materia" class="form-control mb-2" placeholder="Asignatura" required>
                <input type="date" name="fecha" class="form-control mb-2" required>
                <input type="number" step="0.1" name="nota_estimada" class="form-control mb-3" placeholder="Nota que esperas sacar">
                <button type="submit" name="add_examen" class="btn btn-primary w-100 rounded-pill">Añadir</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>