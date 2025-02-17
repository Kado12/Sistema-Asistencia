<?php

date_default_timezone_set('America/Lima');

if (isset($_POST['employee'])) {
	$output = array('error' => false);

	include 'conn.php';
	date_default_timezone_set('America/Lima');

	$employee = $_POST['employee'];
	$status = $_POST['status'];

	$sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
	$query = $conn->query($sql);

	if ($query->num_rows > 0) {
		$row = $query->fetch_assoc();
		$id = $row['id'];
		$date_out = $row['date_out'];

		include 'admin/get_cantidad_faltas_justificadas_injustificadas.php'; 
		$resultado_json = cantFaltas($id);
		$resultado_array = json_decode($resultado_json, true);
        $faltas_injustificadas = $resultado_array['faltas_injustificadas'];
		$date_out_obj = new DateTime($date_out);
        $date_out_obj->modify('+' . $faltas_injustificadas . ' days');
        $new_date_out = $date_out_obj->format('Y-m-d');
		$update_sql = "UPDATE employees SET date_out_new = '$new_date_out' WHERE id = $id";
		$conn->query($update_sql);


		$sql = "SELECT * FROM mejor_colaborador ORDER BY id DESC LIMIT 3";
		$query = $conn->query($sql);

		$frase = "";

		while ($row2 = $query->fetch_assoc()) {
			// Consulta si el empleado es uno de los 3 mejores colaboradores y si es así, se le muestra una frase motivadora aleatoria
			if ($row2['employee_id'] == $id) {
				$sql = "SELECT * FROM frase_colaborador ORDER BY RAND() LIMIT 1";
				$result = $conn->query($sql);
				$row3 = $result->fetch_assoc();
				$frase = "<script>$('#frase').text('" . $row3['frase'] . "')</script>";
			}
		}

		$date_now = date('Y-m-d');

		// Mover a papelera si la fecha de salida es mayor a la fecha actual o si tiene más de 10 faltas
		if ($date_now > $date_out) {
			$sql = "INSERT INTO papelera SELECT * FROM employees WHERE id = '$id'";

			if ($conn->query($sql)) {
				$sql = "DELETE FROM employees WHERE id = '$id'";

				if ($conn->query($sql)) {
					$output['error'] = true;
					$output['message'] = 'Tu usuario ha sido suspendido por haber superado tu fecha de prácticas o límite de faltas';
				} else {
					$output['error'] = true;
					$output['message'] = $conn->error;
				}
			} else {
				$output['error'] = true;
				$output['message'] = $conn->error;
			}
		} else {
			if ($status == 'in') {
				$sql = "SELECT * FROM attendance WHERE employee_id = '$id' AND date = '$date_now' AND time_in IS NOT NULL";
				$query = $conn->query($sql);
				if ($query->num_rows > 0) {
					$output['error'] = true;
					$output['message'] = 'Has registrado tu entrada por hoy';
				} else {
					//updates
					$sched = $row['schedule_id'];
					$lognow = date('H:i:s');
					$sql = "SELECT * FROM schedules WHERE id = '$sched'";
					$squery = $conn->query($sql);
					$srow = $squery->fetch_assoc();

					$NuevaFechas = new DateTime($srow['time_in']);
					$NuevaFechas->modify('+5 minute');
					$NuevaFechas = $NuevaFechas->format("H:i:s");
					$logstatus = ($lognow > $NuevaFechas) ? 0 : 1;
					//

					$sql = "INSERT INTO attendance (employee_id, date, time_in, status) VALUES ('$id', '$date_now', '$lognow', '$logstatus')";

					if ($conn->query($sql)) {
						if (!$logstatus) {
							$alert = '<script>
								Swal.fire({
									title: "Se ha registrado tu ingreso tarde",
									icon: "warning",
									width: "400px"
								})
							</script>';
						} else {
							$alert = '<script>
								Swal.fire({
									title: "Felicitaciones por tu puntualidad!🎉",
									icon: "success",
									width: "400px"
								})
							</script>';
						}

						$output['message'] = $frase . $alert . '<p class="bienvenida">¡Hola, ' . $row['firstname'] . ' ' . $row['lastname'] . '!</p>
						<p class="registro__exitoso">Se ha registrado tu ingreso</p>';
					} else {
						$output['error'] = true;
						$output['message'] = $conn->error;
					}
				}
			}

			if ($status == 'out') {
				$sql = "SELECT *, attendance.id AS uid FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id WHERE attendance.employee_id = '$id' AND date = '$date_now'";
				$query = $conn->query($sql);
				if ($query->num_rows < 1) {
					$output['error'] = true;
					$output['message'] = 'No se puede registrar tu salida, sin previamente registrar tu entrada.';
				} else {
					$row = $query->fetch_assoc();
					if ($row['time_out'] != '00:00:00') {
						$output['error'] = true;
						$output['message'] = 'Has registrado tu salida por hoy';
					} else {
						//updates
						$sched = $row['schedule_id'];
						$lognow1 = date('H:i:s');
						$sql = "SELECT * FROM schedules WHERE id = '$sched'";
						$squery = $conn->query($sql);
						$srow = $squery->fetch_assoc();
						$logstatus = ($lognow1 > $srow['time_out']) ? 0 : 1;
						//

						$sql = "UPDATE attendance SET time_out = '$lognow1' WHERE id = '" . $row['uid'] . "'";
						if ($conn->query($sql)) {
							$output['message'] = $frase . '<p class="bienvenida">¡Adios, ' . $row['firstname'] . ' ' . $row['lastname'] . '!</p> <p class="registro__exitoso">Se ha registrado tu salida</p>';

							$sql = "SELECT * FROM attendance WHERE id = '" . $row['uid'] . "'";
							$query = $conn->query($sql);
							$urow = $query->fetch_assoc();

							$time_in = $urow['time_in'];
							$time_out = $urow['time_out'];

							$sql = "SELECT * FROM employees LEFT JOIN schedules ON schedules.id=employees.schedule_id WHERE employees.id = '$id'";
							$query = $conn->query($sql);
							$srow = $query->fetch_assoc();

							if ($srow['time_in'] > $urow['time_in']) {
								$time_in = $srow['time_in'];
							}

							if ($srow['time_out'] < $urow['time_in']) {
								$time_out = $srow['time_out'];
							}

							$time_in = new DateTime($time_in);
							$time_out = new DateTime($time_out);
							$interval = $time_in->diff($time_out);
							$hrs = $interval->format('%h');
							$mins = $interval->format('%i');
							$mins = $mins / 60;
							$int = $hrs + $mins;

							/*Esto resta 1 hora de numero total de horas si pasa de 4 horas, el total por día es 4.5 horas. Se le estaba restando 1 hora a cada practicante. */

							// if ($int > 4) {
							// 	$int = $int - 1;
							// }

							$sql = "UPDATE attendance SET num_hr = '$int' WHERE id = '" . $row['uid'] . "'";
							$conn->query($sql);
						} else {
							$output['error'] = true;
							$output['message'] = $conn->error;
						}
					}
				}
			}

			if ($status == 'perfil') {
				$output['error'] = false;
				$output['message'] = $frase . '<p class="bienvenida">¡Hola, ' . $row['firstname'] . ' ' . $row['lastname'] . '!</p> <p class="registro__exitoso">¿A qué sección de tu perfil quieres ingresar?</p>';
			}
		}
	} else {
		$output['error'] = true;
		$output['message'] = 'ID de empleado no encontrado';
	}
}

echo json_encode($output);

?>