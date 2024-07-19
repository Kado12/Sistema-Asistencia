<?php
$conn = new mysqli('localhost', 'root', '75792854', 'asistencia');
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

?>