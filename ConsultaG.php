<?php 
session_start();
if (!isset($_SESSION['admin_logeado'])) { header("Location: login.php"); exit(); }

$host = "sql312.infinityfree.com";
$user = "if0_41511449"; 
$pass = "n25Jhbe0BhJQx7"; // <-- RECUERDA PONER TU CONTRASEÑA AQUÍ
$db   = "if0_41511449_asesorias";
$conexion = mysqli_connect($host, $user, $pass, $db);

mysqli_set_charset($conexion, "utf8");

function registrarAccion($conexion, $accion) {
    $u = 'Admin';
    $a = mysqli_real_escape_string($conexion, $accion);
    mysqli_query($conexion, "INSERT INTO bitacora (usuario, accion) VALUES ('$u', '$a')");
}

if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['delete']);
    registrarAccion($conexion, "ELIMINACIÓN: Borró ID #$id");
    mysqli_query($conexion, "DELETE FROM alumnos WHERE id = '$id'");
    // También borramos sus pagos asociados para no dejar basura en la base de datos
    mysqli_query($conexion, "DELETE FROM pagos WHERE id_alumno = '$id'");
    header("Location: ConsultaG.php?status=deleted");
    exit();
}

$mensaje_swal = "";
if (isset($_POST['update_action'])) {
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    
    // DATOS DEL ALUMNO (Ya no recibimos claveAlumno)
    $nom = mysqli_real_escape_string($conexion, $_POST['nombres']);
    $apP = mysqli_real_escape_string($conexion, $_POST['apPaterno']);
    $apM = mysqli_real_escape_string($conexion, $_POST['apMaterno']);
    $telA = mysqli_real_escape_string($conexion, $_POST['telefonoAlumno']);
    $telP = mysqli_real_escape_string($conexion, $_POST['telefonoPadres']);
    $telE = mysqli_real_escape_string($conexion, $_POST['telefonoEmergencia']);
    $fac = mysqli_real_escape_string($conexion, $_POST['facultad']);
    $prepa = ($_POST['preparatoria'] == 'Si') ? 1 : 0;
    $est = isset($_POST['estatus']) ? 1 : 0;

    // DATOS DE PAGOS
    $sep = floatval($_POST['separacion']);
    $p1 = floatval($_POST['pago1']);
    $p2 = floatval($_POST['pago2']);
    $p3 = floatval($_POST['pago3']);
    $p4 = floatval($_POST['pago4']);
    $p5 = floatval($_POST['pago5']);
    
    // CALCULAR EL TOTAL AUTOMÁTICAMENTE
    $total = $sep + $p1 + $p2 + $p3 + $p4 + $p5;

    // 1. ACTUALIZAR TABLA ALUMNOS
    $sql_alumnos = "UPDATE alumnos SET nombres='$nom', apPaterno='$apP', apMaterno='$apM', 
                    telefonoAlumno='$telA', telefonoPadres='$telP', telefonoEmergencia='$telE', 
                    facultad='$fac', preparatoria='$prepa', estatus='$est' WHERE id='$id'";
    mysqli_query($conexion, $sql_alumnos);

    // 2. ACTUALIZAR O INSERTAR EN TABLA PAGOS
    // Revisamos si ya existe un registro de pago para este alumno
    $check_pagos = mysqli_query($conexion, "SELECT id_pago FROM pagos WHERE id_alumno = '$id'");
    if(mysqli_num_rows($check_pagos) > 0) {
        // Si existe, actualizamos
        $sql_pagos = "UPDATE pagos SET separacion='$sep', pago1='$p1', pago2='$p2', pago3='$p3', pago4='$p4', pago5='$p5', total='$total' WHERE id_alumno='$id'";
        mysqli_query($conexion, $sql_pagos);
    } else {
        // Si no existe, lo creamos y le ponemos la fecha actual
        $sql_pagos = "INSERT INTO pagos (id_alumno, separacion, pago1, pago2, pago3, pago4, pago5, total, fecha) 
                      VALUES ('$id', '$sep', '$p1', '$p2', '$p3', '$p4', '$p5', '$total', CURDATE())";
        mysqli_query($conexion, $sql_pagos);
    }

    registrarAccion($conexion, "ACTUALIZACIÓN: Editó a $nom (ID #$id)");
    $mensaje_swal = "updated";
}

// Lista de Facultades UANL
$facultades_uanl = [
    "Agronomía", "Arquitectura", "Artes Escénicas", "Artes Visuales", "Ciencias Biológicas", 
    "Ciencias de la Comunicación", "Ciencias de la Tierra", "Ciencias Físico Matemáticas", 
    "Ciencias Forestales", "Ciencias Políticas y Relaciones Internacionales", "Ciencias Químicas", 
    "Contaduría Pública y Administración (FACPYA)", "Derecho y Criminología (FACDYC)", 
    "Economía", "Enfermería", "Filosofía y Letras", "Ingeniería Civil (FIC)", 
    "Ingeniería Mecánica y Eléctrica (FIME)", "Medicina", "Medicina Veterinaria y Zootecnia", 
    "Música", "Odontología", "Organización Deportiva", "Psicología", "Salud Pública y Nutrición", 
    "Trabajo Social y Desarrollo Humano"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión | Cre-C Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { margin: 0; padding: 20px; font-family: 'Open Sans', sans-serif; color: white; background: linear-gradient(rgba(10,10,20,0.95), rgba(10,10,20,0.95)), url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg'); background-size: cover; background-attachment: fixed; }
        .container { max-width: 1100px; margin: 0 auto; }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px); padding: 35px; border-radius: 25px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.5); margin-bottom: 20px; }
        h2 { font-family: 'Montserrat'; text-align: center; text-transform: uppercase; letter-spacing: 3px; background: linear-gradient(90deg, #fff, #00e5ff); -background-clip: text; -webkit-text-fill-color: transparent; margin: 0 0 20px 0;}
        .search-wrapper { position: relative; margin-bottom: 25px; }
        .input-buscador { width: 100%; padding: 15px 45px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; color: white; box-sizing: border-box; outline: none; font-size: 1rem;}
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #00e5ff; }
        table { width: 100%; border-collapse: collapse; border-radius: 15px; overflow: hidden; background: rgba(0,0,0,0.2); }
        th { background: rgba(0,229,255,0.1); color: #00e5ff; padding: 15px; text-align: left; font-size: 0.7rem; text-transform: uppercase; }
        td { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.03); cursor: pointer; transition: 0.2s; font-size: 0.85rem; vertical-align: middle; }
        tr:hover td { background: rgba(255, 255, 255, 0.03); }
        .btn-action { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-family: 'Montserrat'; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; transition: 0.3s; display: inline-block; }
        .btn-edit { color: #00e5ff; border: 1px solid #00e5ff; margin-right: 5px; }
        .btn-edit:hover { background: #00e5ff; color: black; }
        .btn-delete { color: #ff4d4d; border: 1px solid #ff4d4d; }
        .btn-delete:hover { background: #ff4d4d; color: white; }
        
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 0.60rem; font-family: 'Montserrat'; font-weight: 800; text-transform: uppercase; display: inline-block; margin-left: 10px; vertical-align: text-bottom;}
        .badge-activo { background: rgba(0, 255, 102, 0.1); color: #00ff66; border: 1px solid #00ff66; }
        .badge-inactivo { background: rgba(255, 51, 51, 0.1); color: #ff3333; border: 1px solid #ff3333; }

        #seccionEdicion { display: none; animation: fadeIn 0.5s ease; }
        .grid-edit { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px; }
        .grid-pagos { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-top: 15px; padding: 15px; background: rgba(0,229,255,0.05); border-radius: 10px; border: 1px solid rgba(0,229,255,0.2); }
        label { display: block; font-family: 'Montserrat'; font-size: 0.6rem; color: #00e5ff; text-transform: uppercase; font-weight: 800; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: #1a1a1a; color: white; box-sizing: border-box; outline: none; }
        select option { background: #1a1a1a; color: white; }
        .btn-update { width: 100%; padding: 15px; background: linear-gradient(90deg, #0055ff, #00e5ff); border: none; border-radius: 10px; color: white; font-family: 'Montserrat'; font-weight: 800; text-transform: uppercase; cursor: pointer; margin-top: 20px; }
        .toolbar { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .btn-nav { text-decoration: none; color: white; font-size: 0.7rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; }
    </style>
</head>
<body>

<div class="container">
    <div class="toolbar">
        <a href="menu.php" class="btn-nav">🏠 MENÚ PRINCIPAL</a>
        <a href="ConsultaG.php" class="btn-nav">🔄 ACTUALIZAR LISTA</a>
    </div>

    <div id="seccionEdicion" class="glass-card">
        <h2>Editar Información y Pagos</h2>
        <form method="POST">
            <input type="hidden" name="update_action" value="1">
            <input type="hidden" name="id" id="edit_id">
            
            <div style="background: rgba(0,229,255,0.05); padding: 10px; border-radius: 10px; margin-bottom: 15px; border: 1px solid rgba(0,229,255,0.2); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <label style="display:inline-block; margin:0;">Estatus Activo:</label>
                    <input type="checkbox" name="estatus" id="edit_estatus" style="width:auto;">
                </div>
                <div style="font-family: 'Montserrat'; font-size: 0.8rem; color: #fff;">
                    CLAVE: <span id="display_clave" style="color:#00e5ff; font-weight:bold;"></span> 
                    <span style="font-size: 0.6rem; opacity: 0.6;">(No modificable)</span>
                </div>
            </div>

            <div class="grid-edit">
                <div><label>Nombre(s)</label><input type="text" name="nombres" id="edit_nombre" required></div>
                <div><label>Ap. Paterno</label><input type="text" name="apPaterno" id="edit_paterno" required></div>
                <div><label>Ap. Materno</label><input type="text" name="apMaterno" id="edit_materno"></div>
                <div><label>Tel. Alumno</label><input type="text" name="telefonoAlumno" id="edit_telA"></div>
                <div><label>Tel. Padres</label><input type="text" name="telefonoPadres" id="edit_telP"></div>
                <div><label>Tel. Emergencia</label><input type="text" name="telefonoEmergencia" id="edit_telE"></div>
                
                <div>
                    <label>¿Nivel Prepa?</label>
                    <select name="preparatoria" id="edit_prepa" onchange="toggleFacultad()">
                        <option value="No">No (Universidad)</option>
                        <option value="Si">Si (Preparatoria)</option>
                    </select>
                </div>
                <div id="div_facultad">
                    <label>Facultad UANL</label>
                    <select name="facultad" id="edit_facu">
                        <option value="No aplica">Seleccione Facultad...</option>
                        <?php foreach($facultades_uanl as $f): ?>
                            <option value="<?php echo $f; ?>"><?php echo $f; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h3 style="font-family: 'Montserrat'; font-size: 0.8rem; color: #00e5ff; text-transform: uppercase; margin-top: 30px; margin-bottom: 0;">Finanzas del Alumno</h3>
            <div class="grid-pagos">
                <div><label>Separación</label><input type="number" step="0.01" name="separacion" id="edit_sep" value="0"></div>
                <div><label>Pago 1</label><input type="number" step="0.01" name="pago1" id="edit_p1" value="0"></div>
                <div><label>Pago 2</label><input type="number" step="0.01" name="pago2" id="edit_p2" value="0"></div>
                <div><label>Pago 3</label><input type="number" step="0.01" name="pago3" id="edit_p3" value="0"></div>
                <div><label>Pago 4</label><input type="number" step="0.01" name="pago4" id="edit_p4" value="0"></div>
                <div><label>Pago 5</label><input type="number" step="0.01" name="pago5" id="edit_p5" value="0"></div>
            </div>

            <button type="submit" class="btn-update">Guardar y Calcular Total</button>
            <a href="#" onclick="cancelarEdicion()" style="display:block; text-align:center; color:#ff4d4d; margin-top:15px; font-size:0.7rem; font-weight:800; text-decoration:none;">❌ CANCELAR EDICIÓN</a>
        </form>
    </div>

    <div id="seccionTabla" class="glass-card">
        <h2>Listado General</h2>
        <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" id="buscador" class="input-buscador" placeholder="Escribe para buscar por nombre, clave o ID...">
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID / Clave / Estatus</th>
                    <th>Estudiante</th>
                    <th>Facultad / Institución</th>
                    <th>Total Pagado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <?php 
                // AQUI USAMOS LEFT JOIN PARA TRAER LOS DATOS DEL ALUMNO Y SUS PAGOS AL MISMO TIEMPO
                $query = "SELECT a.*, p.separacion, p.pago1, p.pago2, p.pago3, p.pago4, p.pago5, p.total 
                          FROM alumnos a 
                          LEFT JOIN pagos p ON a.id = p.id_alumno 
                          ORDER BY a.id DESC";
                
                $q = mysqli_query($conexion, $query);
                while($r = mysqli_fetch_assoc($q)): 
                ?>
                <tr onclick="irAExpediente(<?php echo $r['id']; ?>)">
                    <td>
                        <span style="opacity:0.4;">#<?php echo $r['id']; ?></span> | 
                        <span style="color:#00e5ff; font-weight:bold;"><?php echo $r['claveAlumno']; ?></span>
                        <?php if($r['estatus'] == 1): ?>
                            <span class="badge badge-activo">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?php echo $r['apPaterno']." ".$r['nombres']; ?></td>
                    <td style="font-size:0.75rem; opacity:0.7;"><?php echo ($r['preparatoria'] == 1) ? "PREPARATORIA" : $r['facultad']; ?></td>
                    <td style="color:#00ff66; font-weight:bold;">$<?php echo number_format((float)$r['total'], 2); ?></td>
                    <td onclick="event.stopPropagation()">
                        <a href="#" onclick='cargarEdicion(<?php echo json_encode($r); ?>)' class="btn-action btn-edit">Editar</a>
                        <a href="#" onclick="confirmarBorrado(<?php echo $r['id']; ?>)" class="btn-action btn-delete">Borrar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function irAExpediente(id) { window.location.href = 'Consultai.php?id=' + id; }

    // FUNCION PARA ESCONDER LA FACULTAD SI ES PREPA
    function toggleFacultad() {
        let esPrepa = document.getElementById('edit_prepa').value;
        let divFacultad = document.getElementById('div_facultad');
        let selectFacultad = document.getElementById('edit_facu');
        
        if(esPrepa === 'Si') {
            divFacultad.style.display = 'none';
            selectFacultad.value = 'No aplica';
        } else {
            divFacultad.style.display = 'block';
        }
    }

    function cargarEdicion(datos) {
        document.getElementById('seccionTabla').style.display = 'none';
        document.getElementById('seccionEdicion').style.display = 'block';
        
        // Asignamos datos
        document.getElementById('edit_id').value = datos.id;
        document.getElementById('display_clave').innerText = datos.claveAlumno; // Mostramos la clave pero no se edita
        document.getElementById('edit_nombre').value = datos.nombres;
        document.getElementById('edit_paterno').value = datos.apPaterno;
        document.getElementById('edit_materno').value = datos.apMaterno;
        document.getElementById('edit_telA').value = datos.telefonoAlumno;
        document.getElementById('edit_telP').value = datos.telefonoPadres || "";
        document.getElementById('edit_telE').value = datos.telefonoEmergencia || "";
        document.getElementById('edit_prepa').value = (datos.preparatoria == 1) ? "Si" : "No";
        document.getElementById('edit_facu').value = datos.facultad;
        document.getElementById('edit_estatus').checked = (datos.estatus == 1);

        // Asignamos pagos (Si vienen null de la BD, ponemos 0)
        document.getElementById('edit_sep').value = datos.separacion || 0;
        document.getElementById('edit_p1').value = datos.pago1 || 0;
        document.getElementById('edit_p2').value = datos.pago2 || 0;
        document.getElementById('edit_p3').value = datos.pago3 || 0;
        document.getElementById('edit_p4').value = datos.pago4 || 0;
        document.getElementById('edit_p5').value = datos.pago5 || 0;
        
        toggleFacultad(); // Verificamos si escondemos la facultad al cargar
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function cancelarEdicion() { document.getElementById('seccionEdicion').style.display = 'none'; document.getElementById('seccionTabla').style.display = 'block'; }

    document.getElementById('buscador').addEventListener('keyup', function() {
        let f = this.value.toLowerCase();
        document.querySelectorAll('#cuerpoTabla tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(f) ? '' : 'none';
        });
    });

    function confirmarBorrado(id) {
        Swal.fire({ title: '¿Eliminar registro?', text: "Se borrará al alumno y su historial de pagos.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ff4d4d', confirmButtonText: 'SÍ, BORRAR', background: '#0a0a0f', color: '#fff' })
        .then((res) => { if(res.isConfirmed) window.location.href = 'ConsultaG.php?delete=' + id; });
    }

    <?php if($mensaje_swal == "updated"): ?>
        Swal.fire({ title: '¡Actualizado!', text: 'Los datos y pagos se guardaron correctamente.', icon: 'success', background: '#0a0a0f', color: '#fff', confirmButtonColor: '#00e5ff' });
    <?php endif; ?>
</script>
</body>
</html>