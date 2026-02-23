<?php
// 1. Mostrar errores para depurar si algo más falla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

// 2. Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// --- ACCIONES DIRECTAS ---

// Eliminar Examen/Tarea
if (isset($_GET['delete_ex'])) {
    $id = (int)$_GET['delete_ex'];
    $db->query("DELETE FROM examenes WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Añadir Examen/Tarea
if (isset($_POST['add_examen'])) {
    $materia = $_POST['materia'];
    $fecha = $_POST['fecha'];
    $stmt = $db->prepare("INSERT INTO examenes (materia, fecha, hora, tipo) VALUES (?, ?, '00:00', 'Tarea')");
    $stmt->execute([$materia, $fecha]);
    header("Location: index.php");
    exit();
}

// --- OBTENCIÓN DE DATOS (Consultas directas para evitar errores de clase) ---

// Traducir día actual a español
$dias_esp = [
    'Monday'    => 'Lunes',
    'Tuesday'   => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday'  => 'Jueves',
    'Friday'    => 'Viernes',
    'Saturday'  => 'Sábado',
    'Sunday'    => 'Domingo'
];
$hoy_nombre = $dias_esp[date('l')];

// 1. Obtener Menú de hoy
$menuHoy = null;
try {
    $stmt_m = $db->query("SELECT * FROM menus WHERE dia_semana = '$hoy_nombre' LIMIT 1");
    $menuHoy = $stmt_m->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* Tabla no existe aún */ }

// 2. Obtener Clases de hoy (de la tabla 'horarios')
$clasesHoy = [];
try {
    $stmt_h = $db->query("SELECT * FROM horarios WHERE dia_semana = '$hoy_nombre' ORDER BY hora_inicio ASC");
    $clasesHoy = $stmt_h->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* Tabla no existe aún */ }

// 3. Obtener Exámenes/Tareas próximos
$proximosExamenes = [];
try {
    $stmt_e = $db->query("SELECT *, DATEDIFF(fecha, CURDATE()) as dias_restantes FROM examenes WHERE fecha >= CURDATE() ORDER BY fecha ASC");
    $proximosExamenes = $stmt_e->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* Tabla no existe aún */ }

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
                <h6 class="fw-800 text-primary mb-3">🚌 TRANSPORTE LA VALL - UJI</h6>
                <div class="row">
                    <div class="col-6 small">
                        <span class="text-muted d-block mb-1 fw-bold">➡️ Ida (La Vall):</span>
                        <?php $ida = ['06:40','07:50','09:10','13:45','15:00']; foreach($ida as $h) echo "<span class='bus-badge'>$h</span>"; ?>
                    </div>
                    <div class="col-6 border-start small">
                        <span class="text-muted d-block mb-1 fw-bold">⬅️ Vuelta (UJI):</span>
                        <?php $vta = ['12:49','14:53','17:03','19:38','21:39']; foreach($vta as $h) echo "<span class='bus-badge text-danger'>$h</span>"; ?>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h5 class="fw-800 mb-4">Clases de hoy</h5>
                <?php if($clasesHoy): foreach($clasesHoy as $c): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="fw-bold me-3 text-primary"><?= substr($c['hora_inicio'],0,5) ?></div>
                        <div>
                            <div class="fw-bold"><?= $c['materia'] ?></div>
                            <small class="text-muted">Aula: <?= $c['aula'] ?></small>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small">No hay clases registradas para hoy.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom bg-success text-white">
                <small class="text-uppercase fw-bold opacity-75">Hoy se come en la UJI:</small>
                <h5 class="fw-800 mt-1 mb-0"><?= $menuHoy ? $menuHoy['plato_principal'] : "Menú no cargado" ?></h5>
                <small class="opacity-75"><?= $menuHoy ? "Postre: " . $menuHoy['postre'] : "Consulta en cafetería" ?></small>
            </div>

            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-800 mb-0">🎯 Tareas y Exámenes</h5>
                    <button class="btn btn-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#addModal">+</button>
                </div>
                
                <?php if($proximosExamenes): foreach($proximosExamenes as $e): ?>
                    <div class="p-3 bg-light rounded-4 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small"><?= $e['materia'] ?></div>
                            <small class="text-primary fw-bold">Faltan <?= $e['dias_restantes'] ?> días</small>
                        </div>
                        <a href="?delete_ex=<?= $e['id'] ?>" class="btn btn-sm text-danger fw-bold" onclick="return confirm('¿Borrar?')">✕</a>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted small text-center">¡Estás al día! No hay tareas próximas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-800 mb-3">Nuevo Registro</h5>
            <form method="POST">
                <input type="text" name="materia" class="form-control mb-2" placeholder="Asignatura / Tarea" required>
                <input type="date" name="fecha" class="form-control mb-3" required>
                <button type="submit" name="add_examen" class="btn btn-primary w-100 rounded-pill">Guardar</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>