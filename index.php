<?php
// 1. Configuración de Errores y Modelos
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// --- LÓGICA DE INTERACCIÓN (ACCIONES) ---

// Añadir Clase (Desde el Modal)
if (isset($_POST['add_clase_directa'])) {
    $query = "INSERT INTO horarios (dia_semana, materia, hora_inicio, hora_fin, aula) 
              VALUES (:dia, :mat, :h_i, :h_f, :aula)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':dia' => $_POST['dia'], ':mat' => $_POST['materia'],
        ':h_i' => $_POST['h_inicio'], ':h_f' => $_POST['h_fin'], ':aula' => $_POST['aula']
    ]);
    header("Location: index.php"); exit();
}

// Eliminar Clase
if (isset($_GET['del_clase'])) {
    $db->query("DELETE FROM horarios WHERE id = " . (int)$_GET['del_clase']);
    header("Location: index.php"); exit();
}

// Gestionar Recordatorios
if (isset($_POST['add_rec'])) {
    $stmt = $db->prepare("INSERT INTO recordatorios (texto) VALUES (:txt)");
    $stmt->execute([':txt' => $_POST['texto']]);
    header("Location: index.php"); exit();
}
if (isset($_GET['del_rec'])) {
    $db->query("DELETE FROM recordatorios WHERE id = " . (int)$_GET['del_rec']);
    header("Location: index.php"); exit();
}

// Actualizar Examen (Progreso/Notas)
if (isset($_POST['update_examen'])) {
    $examenModel->actualizarProgreso($_POST['id'], $_POST['progreso'], $_POST['anotaciones'], $_POST['nota']);
    header("Location: index.php"); exit();
}

// 2. Carga de Datos
$clasesHoy = $horarioModel->obtenerSemana(); // Muestra las de hoy según tu lógica de modelo
$proximosExamenes = $examenModel->obtenerProximos();
$recs = $db->query("SELECT * FROM recordatorios ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$menuHoy = $menuModel->obtenerMenuHoy();
$hoy_texto = date('l'); 
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
        .card-custom { border: none; border-radius: 24px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 20px; }
        .bus-card { border-left: 6px solid #4338ca; }
        .bus-badge { background: #eef2ff; color: #4338ca; padding: 4px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; margin-bottom: 4px; display: inline-block; }
        .progress { height: 8px; border-radius: 10px; background: #f1f5f9; }
        .fw-800 { font-weight: 800; }
        .btn-add-mini { width: 30px; height: 30px; border-radius: 50%; border: none; background: #4338ca; color: white; line-height: 1; }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-800 mb-0">UniHub 🚀</h1>
            <span class="badge bg-white text-dark border rounded-pill small">La Vall d'Uixó</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalGestionarHorario">✏️ Editar Horario</button>
            <a href="horario_semanal.php" class="btn btn-dark btn-sm rounded-pill px-3">📅 Ver Semana</a>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card-custom bus-card mb-4">
                <h6 class="fw-800 text-uppercase small mb-3 text-primary">🚌 Bus 360: La Vall ↔ UJI</h6>
                <div class="row small">
                    <div class="col-6">
                        <span class="text-muted d-block mb-2 fw-bold">Salidas La Vall:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $h_v = ['06:40','07:50','09:10','09:50','10:50','11:50','12:50','13:45','15:00','15:50','16:30','17:40','18:30','19:45','20:40']; 
                            foreach($h_v as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block mb-2 fw-bold">Regresos UJI:</span>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $h_u = ['07:39','08:58','10:09','10:58','11:58','12:49','13:49','14:53','15:59','17:03','17:38','18:39','19:38','20:53','21:39']; 
                            foreach($h_u as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Agenda para hoy</h5>
                <?php if($clasesHoy): foreach($clasesHoy as $c): ?>
                    <div class="d-flex align-items-center mb-4 border-start border-3 border-primary ps-3">
                        <div class="me-3">
                            <div class="fw-800 text-primary mb-0"><?= substr($c['hora_inicio'],0,5) ?></div>
                            <small class="text-muted"><?= substr($c['hora_fin'],0,5) ?></small>
                        </div>
                        <div>
                            <div class="fw-bold"><?= $c['materia'] ?></div>
                            <span class="badge bg-light text-dark small">📍 Aula <?= $c['aula'] ?></span>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted text-center py-4">No tienes clases hoy. ¡Disfruta!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white mb-4 shadow-sm">
                <small class="text-uppercase fw-bold opacity-75 small">Hoy en el bar:</small>
                <h4 class="fw-800 mb-0"><?= $menuHoy ? $menuHoy['plato_principal'] : "Menú no cargado" ?></h4>
            </div>

            <div class="card-custom mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-800 mb-0">📌 No olvidar</h6>
                </div>
                <form method="POST" class="d-flex gap-2 mb-3">
                    <input type="text" name="texto" class="form-control form-control-sm rounded-pill px-3" placeholder="Llevar tijeras, pen..." required>
                    <button type="submit" name="add_rec" class="btn-add-mini">+</button>
                </form>
                <div class="rec-list">
                    <?php foreach($recs as $r): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 small border-bottom pb-1">
                            <span><?= $r['texto'] ?></span>
                            <a href="?del_rec=<?= $r['id'] ?>" class="text-danger text-decoration-none">✕</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card-custom">
                <h6 class="fw-800 mb-3">Exámenes y Tareas</h6>
                <?php foreach($proximosExamenes as $e): ?>
                    <div class="mb-3 p-3 bg-light rounded-4" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#editExamen<?= $e['id'] ?>">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold small"><?= $e['materia'] ?></span>
                            <span class="badge bg-white text-dark border small rounded-pill">Faltan <?= $e['dias_restantes'] ?>d</span>
                        </div>
                        <div class="progress mb-1">
                            <div class="progress-bar bg-primary" style="width: <?= $e['progreso'] ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 0.7rem;">
                            <span class="text-muted">Estudio: <?= $e['progreso'] ?>%</span>
                            <?php if($e['nota_sacada']): ?><span class="text-success fw-bold">Nota: <?= $e['nota_sacada'] ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="modal fade" id="editExamen<?= $e['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <form method="POST">
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <h5 class="fw-800 mb-4"><?= $e['materia'] ?></h5>
                                        
                                        <label class="small fw-bold">Progreso de estudio:</label>
                                        <input type="range" name="progreso" class="form-range" value="<?= $e['progreso'] ?>">
                                        
                                        <label class="small fw-bold mt-3 d-block">Anotaciones:</label>
                                        <textarea name="anotaciones" class="form-control form-control-sm mb-3" rows="3"><?= $e['anotaciones'] ?></textarea>
                                        
                                        <label class="small fw-bold">Calificación sacada:</label>
                                        <input type="number" step="0.1" name="nota" class="form-control form-control-sm" value="<?= $e['nota_sacada'] ?>">
                                        
                                        <button type="submit" name="update_examen" class="btn btn-primary w-100 rounded-pill mt-4">Guardar Progreso</button>
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
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-800 mb-0">✏️ Gestionar Mi Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" class="row g-2 mb-4 bg-light p-3 rounded-4">
                    <div class="col-md-3">
                        <select name="dia" class="form-select form-select-sm" required>
                            <option value="Lunes">Lunes</option><option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option><option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                        </select>
                    </div>
                    <div class="col-md-4"><input type="text" name="materia" class="form-control form-select-sm" placeholder="Asignatura" required></div>
                    <div class="col-md-2"><input type="time" name="h_inicio" class="form-control form-select-sm" required></div>
                    <div class="col-md-2"><input type="text" name="aula" class="form-control form-select-sm" placeholder="Aula"></div>
                    <input type="hidden" name="h_fin" value="00:00">
                    <div class="col-md-1"><button type="submit" name="add_clase_directa" class="btn btn-primary btn-sm w-100">+</button></div>
                </form>

                <div class="table-responsive" style="max-height: 300px;">
                    <table class="table table-sm small">
                        <thead><tr><th>Día</th><th>Materia</th><th>Hora</th><th></th></tr></thead>
                        <tbody>
                            <?php 
                            $todasClases = $db->query("SELECT * FROM horarios ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio")->fetchAll(PDO::FETCH_ASSOC);
                            foreach($todasClases as $tc): ?>
                            <tr>
                                <td><?= $tc['dia_semana'] ?></td>
                                <td class="fw-bold"><?= $tc['materia'] ?></td>
                                <td><?= substr($tc['hora_inicio'],0,5) ?></td>
                                <td class="text-end"><a href="?del_clase=<?= $tc['id'] ?>" class="text-danger" onclick="return confirm('¿Borrar?')">✕</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>