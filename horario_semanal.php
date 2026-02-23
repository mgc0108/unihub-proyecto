<?php
// 1. SEGURIDAD
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); 
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['usuario_id'];
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtenemos solo las clases del usuario logueado
$stmt = $db->prepare("SELECT * FROM horarios WHERE usuario_id = ? ORDER BY hora_inicio ASC");
$stmt->execute([$user_id]);
$todas_las_clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Completo | UniHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
        .grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; padding: 20px; }
        .dia-col { background: white; border-radius: 20px; padding: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); min-height: 400px; }
        .dia-titulo { font-weight: 800; color: #1e293b; text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .clase-card { background: #f8fafc; border-left: 4px solid #4338ca; border-radius: 12px; padding: 10px; margin-bottom: 10px; }
        .clase-materia { font-weight: 700; font-size: 0.9rem; display: block; color: #1e293b; }
        .clase-info { font-size: 0.75rem; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center px-3 mb-4">
            <h2 class="fw-bold mb-0">Mi Semana 📅</h2>
            <a href="index.php" class="btn btn-outline-dark rounded-pill">Volver</a>
        </div>

        <div class="grid-container">
            <?php foreach($dias as $dia): ?>
                <div class="dia-col">
                    <h6 class="dia-titulo"><?= $dia ?></h6>
                    <?php 
                    $hay_clase = false;
                    foreach($todas_las_clases as $c): 
                        if($c['dia_semana'] == $dia): 
                            $hay_clase = true;
                    ?>
                        <div class="clase-card">
                            <span class="clase-materia"><?= htmlspecialchars($c['materia']) ?></span>
                            <span class="clase-info">🕒 <?= substr($c['hora_inicio'], 0, 5) ?> | 📍 <?= htmlspecialchars($c['aula']) ?></span>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    if(!$hay_clase): ?>
                        <p class="text-center text-muted small mt-4">Libre 🙌</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>