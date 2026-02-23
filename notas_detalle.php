<?php
// 1. CONFIGURACIÓN DE SEGURIDAD PARA SESIONES
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); 

session_start();

// 2. CONTROL DE ACCESO
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['usuario_id'];
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// --- 3. CARGA DE NOTAS FILTRADAS POR USUARIO ---
// En lugar de usar el modelo que trae todo, hacemos la consulta directa por seguridad
$stmt = $db->prepare("SELECT * FROM examenes WHERE usuario_id = ? AND nota_sacada IS NOT NULL ORDER BY fecha DESC");
$stmt->execute([$user_id]);
$todasLasNotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separamos las notas en dos grupos (Exámenes y Trabajos/Tareas)
$examenes = array_filter($todasLasNotas, function($n) { 
    return $n['tipo'] == 'Examen'; 
});
$trabajos = array_filter($todasLasNotas, function($n) { 
    return ($n['tipo'] == 'Trabajo' || $n['tipo'] == 'Tarea'); 
});

// Función para calcular promedio por grupo
function calcularMedia($lista) {
    if (count($lista) == 0) return 0;
    // Usamos 'nota_sacada' que es el nombre de tu columna en la tabla examenes
    $suma = array_sum(array_column($lista, 'nota_sacada'));
    return round($suma / count($lista), 2);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Notas | UniMa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-nota { border: none; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card-nota:hover { transform: translateY(-5px); }
        .grade-badge { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 15px; font-weight: 800; font-size: 1.2rem; }
        .nav-pills .nav-link.active { background-color: #4338ca; border-radius: 50px; }
        .nav-link { color: #64748b; font-weight: 600; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold mb-0">Mis Calificaciones 🎓</h1>
            <p class="text-muted">Resumen de tu rendimiento académico</p>
        </div>
        <a href="index.php" class="btn btn-outline-dark rounded-pill px-4">Volver</a>
    </div>

    <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4" id="pills-examenes-tab" data-bs-toggle="pill" data-bs-target="#pills-examenes" type="button">Exámenes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4" id="pills-trabajos-tab" data-bs-toggle="pill" data-bs-target="#pills-trabajos" type="button">Trabajos / Tareas</button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-examenes" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 text-center mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-pill fs-6">Media de Exámenes: <?= calcularMedia($examenes) ?></span>
                </div>
                <?php if(empty($examenes)): ?>
                    <p class="text-center text-muted">Aún no tienes notas de exámenes registradas.</p>
                <?php endif; ?>
                <?php foreach($examenes as $e): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-nota p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($e['materia']) ?></h6>
                                <p class="text-muted small mb-0"><?= $e['fecha'] ?></p>
                            </div>
                            <div class="grade-badge <?= $e['nota_sacada'] >= 5 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                <?= $e['nota_sacada'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-trabajos" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 text-center mb-3">
                    <span class="badge bg-warning bg-opacity-10 text-warning px-4 py-2 rounded-pill fs-6">Media de Trabajos: <?= calcularMedia($trabajos) ?></span>
                </div>
                <?php if(empty($trabajos)): ?>
                    <p class="text-center text-muted">Aún no tienes notas de trabajos registradas.</p>
                <?php endif; ?>
                <?php foreach($trabajos as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-nota p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($t['materia']) ?></h6>
                                <p class="text-muted small mb-0"><?= $t['fecha'] ?></p>
                            </div>
                            <div class="grade-badge <?= $t['nota_sacada'] >= 5 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                <?= $t['nota_sacada'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>