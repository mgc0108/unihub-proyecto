<?php
require_once 'config/database.php';
require_once 'src/Models/horario.php';

$database = new Database();
$db = $database->getConnection();
$horarioModel = new Horario($db);

// Procesar el formulario para añadir una clase
if (isset($_POST['add_clase'])) {
    $dia = $_POST['dia'];
    $materia = $_POST['materia'];
    $h_inicio = $_POST['h_inicio'];
    $h_fin = $_POST['h_fin'];
    $aula = $_POST['aula'];
    
    $query = "INSERT INTO horario (dia_semana, materia, hora_inicio, hora_fin, aula) 
              VALUES ('$dia', '$materia', '$h_inicio', '$h_fin', '$aula')";
    $db->query($query);
    header("Location: editar_horario.php"); exit();
}

// Eliminar una clase
if (isset($_GET['del'])) {
    $db->query("DELETE FROM horario WHERE id = " . $_GET['del']);
    header("Location: editar_horario.php"); exit();
}

// Obtener todas las clases ordenadas por día y hora
$todas_clases = $db->query("SELECT * FROM horario ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Horario Semanal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .container { max-width: 900px; }
        .card-editor { border: none; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">✏️ Gestionar Mis Clases</h2>
            <a href="index.php" class="btn btn-outline-dark rounded-pill px-4">Volver al Inicio</a>
        </div>

        <div class="card card-editor p-4 mb-4">
            <h5 class="mb-3">Añadir nueva asignatura</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <select name="dia" class="form-select" required>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="materia" class="form-control" placeholder="Nombre asignatura" required>
                </div>
                <div class="col-md-2">
                    <input type="time" name="h_inicio" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <input type="time" name="h_fin" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="aula" class="form-control" placeholder="Aula">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="add_clase" class="btn btn-primary px-4 rounded-pill">Añadir al horario</button>
                </div>
            </form>
        </div>

        <div class="card card-editor p-4">
            <h5 class="mb-3">Clases configuradas</h5>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Asignatura</th>
                        <th>Horario</th>
                        <th>Aula</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($todas_clases as $cl): ?>
                    <tr>
                        <td class="fw-bold"><?= $cl['dia_semana'] ?></td>
                        <td><?= $cl['materia'] ?></td>
                        <td><?= substr($cl['hora_inicio'],0,5) ?> - <?= substr($cl['hora_fin'],0,5) ?></td>
                        <td><span class="badge bg-light text-dark"><?= $cl['aula'] ?></span></td>
                        <td><a href="?del=<?= $cl['id'] ?>" class="text-danger text-decoration-none">Eliminar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>