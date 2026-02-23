<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

if(isset($_POST['add_clase'])) {
    $stmt = $db->prepare("INSERT INTO horarios (dia_semana, materia, hora_inicio, aula) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['dia'], $_POST['materia'], $_POST['hora'], $_POST['aula']]);
    header("Location: gestion_clases.php");
}
$horario = $db->query("SELECT * FROM horarios ORDER BY FIELD(dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes'), hora_inicio")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container py-4" style="font-family: sans-serif;">
    <h3>⚙️ Gestionar Horario Semanal</h3>
    <form method="POST" class="mb-4">
        <select name="dia" required><option>Lunes</option><option>Martes</option><option>Miércoles</option><option>Jueves</option><option>Viernes</option></select>
        <input type="text" name="materia" placeholder="Asignatura" required>
        <input type="time" name="hora" required>
        <input type="text" name="aula" placeholder="Aula">
        <button type="submit" name="add_clase">Añadir</button>
    </form>
    <table border="1" width="100%">
        <tr><th>Día</th><th>Hora</th><th>Materia</th><th>Aula</th></tr>
        <?php foreach($horario as $h): ?>
            <tr><td><?=$h['dia_semana']?></td><td><?=$h['hora_inicio']?></td><td><?=$h['materia']?></td><td><?=$h['aula']?></td></tr>
        <?php endforeach; ?>
    </table>
    <br><a href="index.php">⬅️ Volver al Inicio</a>
</div>