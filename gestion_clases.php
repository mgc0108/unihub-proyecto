<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['usuario_id']; // <--- Aquí guardamos quién eres
require_once 'config/database.php';
$db = (new Database())->getConnection();

$edit_clase = null;
if(isset($_GET['edit'])) {
    // Añadimos AND usuario_id para que nadie edite clases de otros
    $stmt = $db->prepare("SELECT * FROM horarios WHERE id = ? AND usuario_id = ?"); // <--- CAMBIO
    $stmt->execute([$_GET['edit'], $user_id]); // <--- CAMBIO
    $edit_clase = $stmt->fetch(PDO::FETCH_ASSOC);
}

if(isset($_POST['save_clase'])) {
    if(!empty($_POST['id'])) {
        // Al actualizar, verificamos que la clase sea tuya
        $stmt = $db->prepare("UPDATE horarios SET dia_semana=?, materia=?, hora_inicio=?, hora_fin=?, aula=? WHERE id=? AND usuario_id=?"); // <--- CAMBIO
        $stmt->execute([$_POST['dia'], $_POST['materia'], $_POST['hora'], $_POST['hora_fin'], $_POST['aula'], $_POST['id'], $user_id]); // <--- CAMBIO
    } else {
        // Al insertar, guardamos tu $user_id
        $stmt = $db->prepare("INSERT INTO horarios (dia_semana, materia, hora_inicio, hora_fin, aula, usuario_id) VALUES (?, ?, ?, ?, ?, ?)"); // <--- CAMBIO
        $stmt->execute([$_POST['dia'], $_POST['materia'], $_POST['hora'], $_POST['hora_fin'], $_POST['aula'], $user_id]); // <--- CAMBIO
    }
    header("Location: gestion_clases.php"); exit;
}

if(isset($_GET['del'])) { 
    // Al borrar, solo permitimos si el usuario_id coincide
    $stmt = $db->prepare("DELETE FROM horarios WHERE id = ? AND usuario_id = ?"); // <--- CAMBIO
    $stmt->execute([(int)$_GET['del'], $user_id]); // <--- CAMBIO
    header("Location: gestion_clases.php"); exit; 
}

// IMPORTANTE: Para listar las clases en la tabla, falta la consulta SELECT.
// Asegúrate de que donde muestras la lista de clases diga esto:
// $clases = $db->query("SELECT * FROM horarios WHERE usuario_id = $user_id ORDER BY dia_semana, hora_inicio")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>UniMa | Gestión Horario</title>
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded-4 shadow-sm" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><?= $edit_clase ? '✏️ Modificar Clase' : '📅 Añadir al Horario' ?></h3>
            <a href="index.php" class="btn btn-outline-dark btn-sm rounded-pill px-4">Volver a UniMa</a>
        </div>
        
        <form method="POST" class="row g-3 mb-5 p-3 bg-light rounded-4">
            <input type="hidden" name="id" value="<?= $edit_clase['id'] ?? '' ?>">
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Día</label>
                <select name="dia" class="form-select rounded-3">
                    <?php foreach(['Lunes','Martes','Miércoles','Jueves','Viernes'] as $d) : ?>
                        <option <?= ($edit_clase['dia_semana'] ?? '') == $d ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="small fw-bold text-muted">Materia</label><input type="text" name="materia" class="form-control rounded-3" value="<?= $edit_clase['materia'] ?? '' ?>" required></div>
            <div class="col-md-2"><label class="small fw-bold text-muted">Inicio</label><input type="time" name="hora" class="form-control rounded-3" value="<?= $edit_clase['hora_inicio'] ?? '' ?>" required></div>
            <div class="col-md-2"><label class="small fw-bold text-muted">Fin</label><input type="time" name="hora_fin" class="form-control rounded-3" value="<?= $edit_clase['hora_fin'] ?? '' ?>" required></div>
            <div class="col-md-1"><label class="small fw-bold text-muted">Aula</label><input type="text" name="aula" class="form-control rounded-3" value="<?= $edit_clase['aula'] ?? '' ?>"></div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="save_clase" class="btn btn-primary w-100 rounded-3 shadow-sm"><?= $edit_clase ? 'Actualizar' : 'Añadir' ?></button>
            </div>
        </form>

        <h5 class="fw-bold mb-3 text-muted">Tu Horario Semanal</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark"><tr><th>Día</th><th>Horario</th><th>Materia</th><th>Aula</th><th class="text-center">Acciones</th></tr></thead>
                <tbody>
                    <?php 
                    $res = $db->query("SELECT * FROM horarios ORDER BY FIELD(dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes'), hora_inicio ASC");
                    while($row = $res->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><b><?=$row['dia_semana']?></b></td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?=substr($row['hora_inicio'],0,5)?> - <?=substr($row['hora_fin'],0,5)?>
                            </span>
                        </td>
                        <td><?=$row['materia']?></td>
                        <td><span class="badge bg-secondary opacity-75"><?=$row['aula']?></span></td>
                        <td class="text-center">
                            <a href="?edit=<?=$row['id']?>" class="btn btn-sm btn-warning rounded-pill px-3">Editar</a>
                            <a href="?del=<?=$row['id']?>" class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="return confirm('¿Borrar?')">✕</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>