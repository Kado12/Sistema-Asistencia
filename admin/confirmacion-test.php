<?php
include 'includes/header-practicante.php';

$sql1 = "SELECT CASE
            WHEN COUNT(r.employee_id) > 0 THEN true ELSE false
            END AS test
        FROM respuestas_test r
        INNER JOIN preguntas_test p ON p.id = r.pregunta_id
        WHERE r.employee_id = (SELECT id FROM employees WHERE employee_id = '{$_POST['employee_id']}') AND p.test = 1";
$result1 = $conn->query($sql1);
$estado1 = $result1->fetch_assoc();


$sql2 = "SELECT CASE
            WHEN COUNT(r.employee_id) > 0 THEN true ELSE false
            END AS test
        FROM respuestas_test r
        INNER JOIN preguntas_test p ON p.id = r.pregunta_id
        WHERE r.employee_id = (SELECT id FROM employees WHERE employee_id = '{$_POST['employee_id']}') AND p.test = 2";
$result2 = $conn->query($sql2);
$estado2 = $result2->fetch_assoc();

$sql3 = "SELECT CASE
            WHEN COUNT(r.employee_id) > 0 THEN true ELSE false
            END AS test
        FROM respuestas_test r
        INNER JOIN preguntas_test p ON p.id = r.pregunta_id
        WHERE r.employee_id = (SELECT id FROM employees WHERE employee_id = '{$_POST['employee_id']}') AND p.test = 3";
$result3 = $conn->query($sql3);
$estado3 = $result3->fetch_assoc();

$estado = false;
if ($row['position_id'] == 2) {
    if ($estado1['test'] && $estado3['test']) {
        $estado = true;
    }
} else if ($row['position_id'] == 13) {
    if ($estado3['test']) {
        $estado = true;
    }
} else {
    if ($estado2['test']) {
        $estado = true;
    }
}
?>

<body class="bg-white d-flex">
    <?php if ($estado || $row['negocio_id'] == 6) { ?>
        <script>
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'perfil-practicante.php';
            document.body.appendChild(form);

            const inputEmployeeId = document.createElement('input');
            inputEmployeeId.type = 'hidden';
            inputEmployeeId.name = 'employee_id';
            inputEmployeeId.value = '<?php echo $_POST["employee_id"] ?>';

            form.appendChild(inputEmployeeId);
            form.submit();
        </script>
    <?php } ?>
    <div class="test-info letraNavBar rounded-4 overflow-hidden shadow mx-auto my-auto" style="max-width: 630px;">
        <div class="test-title text-white p-4" style="background: #5eb130;">
            <h1 class="letraNavBar fs-5 text-center fw-bold m-0">ENCUESTA PENDIENTE</h1>
        </div>
        <div class="d-flex flex-column p-4">
            <p>Estimado colaborador, tiene encuesta(s) pendiente(s) por resolver recuerda que este proceso es
                importante, ya que nos ayuda a organizar planes de mejora en nuestros servicios.</p>
            <?php if ($row['position_id'] == 2 && !$estado1['test']) { ?>
                <div class="d-flex justify-content-between align-items-center gap-3 my-2">
                    <span>COMPETENCIAS DE LIDERAZGO ORGANIZACIONAL</span>
                    <form method="post" action="test.php?test=1">
                        <input type="hidden" name="employee_id" value="<?php echo $_POST['employee_id'] ?>">
                        <input type="submit" class="btn text-white letraNavBar rounded-pill px-4" value="Realizar encuesta"
                            style="background: #1e4da9;">
                    </form>
                </div>
            <?php } ?>
            <?php if ($row['position_id'] == 2 || $row['position_id'] == 13 && !$estado3['test']) { ?>
                <div class="d-flex justify-content-between align-items-center gap-3 my-2">
                    <span>EVALUACIÓN DE MANEJO DE CONFLICTOS</span>
                    <form method="post" action="test.php?test=3">
                        <input type="hidden" name="employee_id" value="<?php echo $_POST['employee_id'] ?>">
                        <input type="submit" class="btn text-white letraNavBar rounded-pill px-4" value="Realizar encuesta"
                            style="background: #1e4da9;">
                    </form>
                </div>
            <?php } else { ?>
                <div class="d-flex justify-content-between align-items-center gap-3 my-2">
                    <span>ENCARGADOS DE GRUPOS</span>
                    <form method="post" action="test.php?test=2">
                        <input type="hidden" name="employee_id" value="<?php echo $_POST['employee_id'] ?>">
                        <input type="submit" class="btn text-white letraNavBar rounded-pill px-4" value="Realizar encuesta"
                            style="background: #1e4da9;">
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>
</body>