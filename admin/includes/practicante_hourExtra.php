<?php
include 'session.php';

if (isset($_POST['add'])) {
    $id = $_POST['id'];
    $newhour = $_POST['horaExtra'];

    $id = $conn->real_escape_string($id);
    $newhour = $conn->real_escape_string($newhour);

    $sql = "SELECT extra_hour FROM asistencia.employees WHERE id='$id'";
    $query = $conn->query($sql);

    if ($query) {
        $row = $query->fetch_assoc();

        if ($row) {
            $current_hours = $row['extra_hour'];

            $new_total_hours = $current_hours + $newhour;

            $update_sql = "UPDATE asistencia.employees SET extra_hour='$new_total_hours' WHERE id='$id'";
            if ($conn->query($update_sql) === TRUE) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Horas extras actualizadas correctamente.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al actualizar las horas extras: ' . $conn->error];
            }
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se encontró el empleado con ID ' . $id];
        }
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error en la consulta: ' . $conn->error];
    }
    header('location: ../practicantes.php');
    exit();
}
?>
