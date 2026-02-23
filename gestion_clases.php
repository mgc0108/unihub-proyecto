<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

if(isset($_POST['add_clase'])) {
    $stmt = $db->prepare("INSERT INTO horarios (dia_semana, materia, hora_inicio, aula) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['dia'], $_POST['materia'], $_POST['hora'], $_POST['aula']]);
}
if(isset($_GET['del'])) {
    $db->query("DELETE FROM horarios WHERE id = " . (int)$_GET['del']);
}

$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Gestionar Horario Semanal</title>
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>⚙️ Gestión del Horario Semanal</h3>
            <a href="index.php" class="btn btn-outline-secondary">⬅️ Volver al Inicio</a>
        </div>
        
        <form method="POST" class="row g-3 mb-5 border p-3 rounded">
            <div class="col-md-3">
                <select name="dia" class="form-select"><?php foreach($dias as $d) echo "<option>$d</option>"; ?></select>
            </div>
            <div class="col-md-3">
                <input type="text" name="materia" class="form-control" placeholder="Asignatura" required>
            </div>
            <div class="col-md-3">
                <input type="time" name="hora" class="form-control" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="aula" class="form-control" placeholder="Aula">
            </div>
            <div class="col-md-1">
                <button type="submit" name="add_clase" class="btn btn-primary w-100">+</button>
            </div>
        </form>

        <?php foreach($dias as $d): ?>
            <h5 class="text-primary mt-3 border-bottom pb-2"><?= $d ?></h5>
            <div class="row mb-3">
                <?php 
                $res = $db->query("SELECT * FROM horarios WHERE dia_semana = '$d' ORDER BY hora_inicio");
                while($h = $res->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col-md-4 mb-2">
                        <div class="p-2 border rounded d-flex justify-content-between align-items-center bg-white">
                            <span><b><?= substr($h['hora_inicio'],0,5) ?></b> - <?= $h['materia'] ?></span>
                            <a href="?del=<?= $h['id'] ?>" class="text-danger text-decoration-none px-2">✕</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>