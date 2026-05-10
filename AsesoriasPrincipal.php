<?php 
session_start();
// 1. Conexión a la base de datos
$conexion = mysqli_connect("sql312.infinityfree.com", "if0_41511449", "n25Jhbe0BhJQx7", "if0_41511449_asesorias");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Para evitar problemas con los acentos
mysqli_set_charset($conexion, "utf8");

// --- GENERACIÓN DE CLAVE AUTOMÁTICA ---
// Buscamos el valor numérico más alto en la columna claveAlumno
$query_max = mysqli_query($conexion, "SELECT MAX(CAST(claveAlumno AS UNSIGNED)) as max_clave FROM alumnos");
$row_max = mysqli_fetch_assoc($query_max);

if ($row_max['max_clave'] !== null) {
    $siguiente_numero = intval($row_max['max_clave']) + 1;
} else {
    $siguiente_numero = 0; // Si la tabla está vacía, empieza en 0
}
// Formateamos para que siempre tenga 4 dígitos (0000, 0001, 0015, etc.)
$nueva_clave_auto = str_pad($siguiente_numero, 4, "0", STR_PAD_LEFT);


// Función de bitácora
function registrarAccion($conexion, $accion) {
    $usuario = isset($_SESSION['admin_logeado']) ? 'Admin' : 'Desconocido';
    $accion_limpia = mysqli_real_escape_string($conexion, $accion);
    mysqli_query($conexion, "INSERT INTO bitacora (usuario, accion) VALUES ('$usuario', '$accion_limpia')");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clave = mysqli_real_escape_string($conexion, $_POST['claveAlumno']);
    $apPaterno = mysqli_real_escape_string($conexion, $_POST['apPaterno']);
    $apMaterno = mysqli_real_escape_string($conexion, $_POST['apMaterno']);
    $nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
    $telA = mysqli_real_escape_string($conexion, $_POST['telefonoAlumno']);
    $telE = mysqli_real_escape_string($conexion, $_POST['telefonoEmergencia']);
    $facultad = mysqli_real_escape_string($conexion, $_POST['Facultad']);
    $carrera = mysqli_real_escape_string($conexion, $_POST['carrera']);
    $prepa = ($_POST['Preparatoria'] == 'Si') ? 1 : 0;

    // EL INSERT YA NO TIENE TELÉFONO DE PADRES Y SÍ TIENE CARRERA
    $sqlalumnos = "INSERT INTO alumnos (claveAlumno, apPaterno, apMaterno, nombres, telefonoAlumno, telefonoEmergencia, facultad, carrera, preparatoria, estatus) 
                   VALUES ('$clave', '$apPaterno', '$apMaterno', '$nombres', '$telA', '$telE', '$facultad', '$carrera', '$prepa', 1)";

    if (mysqli_query($conexion, $sqlalumnos)) {
        $id_recien_creado = mysqli_insert_id($conexion);
        $sep = $_POST['separacion'] ?: 0;
        $p1 = $_POST['pago1'] ?: 0;
        $p2 = $_POST['pago2'] ?: 0;
        $p3 = $_POST['pago3'] ?: 0;
        $p4 = $_POST['pago4'] ?: 0;
        $p5 = $_POST['pago5'] ?: 0;
        $fecha = $_POST['fecha'];
        $total = $sep + $p1 + $p2 + $p3 + $p4 + $p5;

        $sqlpagos = "INSERT INTO pagos (id_alumno, separacion, pago1, pago2, pago3, pago4, pago5, fecha, total) 
                     VALUES ('$id_recien_creado', '$sep', '$p1', '$p2', '$p3', '$p4', '$p5', '$fecha', '$total')";

        if (mysqli_query($conexion, $sqlpagos)) {
            registrarAccion($conexion, "NUEVO INGRESO: Se registró al alumno $nombres $apPaterno con pago inicial de $$total");
            
            // --- LÓGICA PARA SABER A DÓNDE IR ---
            $destino = 'ConsultaG.php'; // Por defecto sale al menú de consulta
            if (isset($_POST['accion_guardar']) && $_POST['accion_guardar'] == 'nuevo') {
                $destino = $_SERVER['PHP_SELF']; // Si le dio a "Guardar y Nuevo", recarga esta misma hoja
            }

            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <body style='background-color: #0a0a0f;'>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: 'Alumno registrado exitosamente',
                        icon: 'success',
                        background: '#0a0a0f', color: '#fff', confirmButtonColor: '#00e5ff'
                    }).then(() => { window.location = '$destino'; });
                });
            </script></body>";
            exit();
        }
    }
}

// Lista de facultades UANL
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
    <title>Nuevo Registro | Cre-C</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        body {
            margin: 0; padding: 40px 20px; font-family: 'Open Sans', sans-serif; color: white; min-height: 100vh;
            background: linear-gradient(rgba(10,10,20,0.92), rgba(10,10,20,0.92)), url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg');
            background-size: cover; background-attachment: fixed; 
        }

        .container { max-width: 1200px; margin: 0 auto; width: 100%; }

        .btn-nav {
            display: inline-block; text-decoration: none; color: #fff; font-family: 'Montserrat'; font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; padding: 10px 20px; border: 1px solid rgba(0, 229, 255, 0.4);
            border-radius: 10px; background: rgba(0, 229, 255, 0.05); transition: 0.3s; margin-bottom: 20px;
        }
        .btn-nav:hover { background: #00e5ff; color: black; box-shadow: 0 0 15px rgba(0,229,255,0.5); }

        h2 { 
            font-family: 'Montserrat'; text-align: center; text-transform: uppercase; letter-spacing: 4px;
            background: linear-gradient(90deg, #fff, #00e5ff); -background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 30px; margin-top: 0;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 40px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .layout-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; }
        .compact-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }

        .section-tag {
            grid-column: span 3; font-family: 'Montserrat'; font-size: 0.75rem; color: #00e5ff;
            text-transform: uppercase; font-weight: 800; margin: 20px 0 5px; letter-spacing: 2px;
            border-bottom: 1px solid rgba(0, 229, 255, 0.2); padding-bottom: 5px;
        }

        label { display: block; font-family: 'Montserrat'; font-size: 0.6rem; color: rgba(255,255,255,0.5); text-transform: uppercase; font-weight: 700; margin-bottom: 5px; margin-left: 5px; }

        input, select {
            width: 100%; padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255, 255, 255, 0.06); color: white; font-weight: 600; box-sizing: border-box; outline: none; transition: 0.3s;
        }
        input:focus, select:focus { border-color: #00e5ff; background: rgba(255, 255, 255, 0.1); box-shadow: 0 0 10px rgba(0,229,255,0.2); }

        input[readonly] { background: rgba(0,229,255,0.05); border-color: rgba(0,229,255,0.3); color: #00e5ff; cursor: not-allowed; }

        #select-facu { color: #00e5ff; font-weight: 800; text-shadow: 0 0 5px rgba(0,229,255,0.3); }
        #select-facu option { color: #ffffff; background: #1a1a24; }

        #contenedorfacu, #contenedorcarrera { transition: all 0.4s ease; opacity: 1; transform: scaleY(1); transform-origin: top; }
        #contenedorfacu.hidden, #contenedorcarrera.hidden { opacity: 0; transform: scaleY(0); display: none; }

        .acordeon-pagos { grid-column: span 3; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; margin-top: 15px; }
        summary { padding: 15px; cursor: pointer; color: white; font-weight: 800; text-align: center; list-style: none; font-family: 'Montserrat'; font-size: 0.8rem; }
        .grid-pagos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; padding: 20px; }

        /* Estilo para los dos botones finales */
        .btn-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px; }
        .btn-submit {
            width: 100%; padding: 18px; border: none; border-radius: 12px; color: white;
            font-family: 'Montserrat'; font-weight: 800; text-transform: uppercase; cursor: pointer; transition: 0.4s; letter-spacing: 1px; font-size: 0.8rem;
        }
        .btn-nuevo { background: linear-gradient(90deg, #a855f7, #6b21a8); }
        .btn-salir { background: linear-gradient(90deg, #0055ff, #00e5ff); }
        .btn-submit:hover { transform: translateY(-3px); filter: brightness(1.2); }
        .btn-nuevo:hover { box-shadow: 0 10px 30px rgba(168, 85, 247, 0.5); }
        .btn-salir:hover { box-shadow: 0 10px 30px rgba(0, 229, 255, 0.5); }

        /* --- ESTILOS VISTA PREVIA --- */
        .preview-panel {
            background: linear-gradient(135deg, rgba(20,20,30,0.8), rgba(10,10,15,0.9));
            border: 1px solid rgba(0, 229, 255, 0.3); border-radius: 20px; padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5), inset 0 0 20px rgba(0,229,255,0.05);
            position: sticky; top: 40px; display: flex; flex-direction: column; gap: 20px;
        }
        .preview-title { text-align: center; font-family: 'Montserrat'; font-size: 0.8rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 3px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 15px; }
        .preview-header { display: flex; justify-content: space-between; align-items: center; }
        .preview-clave { font-family: 'Montserrat'; font-size: 1.5rem; font-weight: 800; color: #00e5ff; text-shadow: 0 0 10px rgba(0,229,255,0.5); }
        .preview-status { background: rgba(0,255,102,0.1); color: #00ff66; border: 1px solid #00ff66; padding: 4px 10px; border-radius: 20px; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; font-family: 'Montserrat'; }
        
        .preview-body { background: rgba(0,0,0,0.3); padding: 20px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); }
        .preview-label { font-size: 0.6rem; color: rgba(255,255,255,0.4); text-transform: uppercase; font-weight: 700; margin-bottom: 3px; }
        .preview-name { font-size: 1.3rem; font-weight: 800; margin-bottom: 15px; color: #fff; word-break: break-word;}
        .preview-school { font-size: 0.85rem; color: #a855f7; font-weight: 600; display: flex; flex-direction: column; gap: 4px; margin-bottom: 15px;}
        
        .preview-grid-mini { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.75rem; color: #ccc; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;}
        .preview-grid-mini i { color: rgba(255,255,255,0.5); width: 15px;}

        .preview-breakdown { background: rgba(0,0,0,0.3); padding: 15px 20px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); font-size: 0.8rem;}
        .breakdown-row { display: flex; justify-content: space-between; margin-bottom: 5px; color: #ccc;}
        .breakdown-row.total { border-top: 1px solid rgba(0,229,255,0.2); margin-top: 10px; padding-top: 10px; font-family: 'Montserrat'; font-weight: 800; color: #00e5ff; font-size: 1.2rem;}

        @media (max-width: 900px) { 
            .layout-grid { grid-template-columns: 1fr; }
            .preview-panel { position: static; margin-bottom: 30px; }
            .compact-grid { grid-template-columns: 1fr 1fr; } 
            .section-tag, .acordeon-pagos { grid-column: span 2; } 
            .grid-pagos { grid-template-columns: 1fr 1fr; } 
        }
    </style>
</head>
<body>

<div class="container">
    <a href="ConsultaG.php" class="btn-nav"><i class="fa-solid fa-arrow-left"></i> Volver a Registros</a>
    <a href="menu.php" class="btn-nav"><i class="fa-solid fa-arrow-left"></i> Volver al menú</a>
    
    <div class="glass-card">
        <h2>Registro de Alumno</h2>
        
        <div class="layout-grid">
            
            <form action="" method="post" id="formRegistro">
                <div class="compact-grid">
                    
                    <div class="section-tag">Datos Identificación</div>
                    <div>
                        <label>Clave (Automática)</label>
                        <input type="text" name="claveAlumno" id="inputClave" value="<?php echo $nueva_clave_auto; ?>" readonly>
                    </div>
                    <div style="grid-column: span 2;">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombres" id="inputNombres" required>
                    </div>
                    <div>
                        <label>Ap. Paterno</label>
                        <input type="text" name="apPaterno" id="inputPaterno" required>
                    </div>
                    <div>
                        <label>Ap. Materno</label>
                        <input type="text" name="apMaterno" id="inputMaterno">
                    </div>
                    <div>
                        <label>¿Va a Preparatoria?</label>
                        <select name="Preparatoria" id="select-prepa">
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                    </div>

                    <div class="section-tag">Contacto y Ubicación</div>
                    <!-- QUITAMOS TELÉFONO PADRES -->
                    <div><label>Tel. Alumno</label><input type="number" name="telefonoAlumno" id="inputTelA"></div>
                    <div style="grid-column: span 2;"><label>Tel. Emergencia</label><input type="number" name="telefonoEmergencia" id="inputTelE"></div>
                    
                    <div id="contenedorfacu" style="grid-column: span 3;">
                        <label>Facultad UANL</label>
                        <select name="Facultad" id="select-facu">
                            <option value="No aplica">Seleccione Facultad...</option>
                            <?php foreach($facultades_uanl as $f): ?>
                                <option value="<?php echo $f; ?>"><?php echo $f; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- NUEVO CAMPO: CARRERA -->
                    <div id="contenedorcarrera" style="grid-column: span 3;">
                        <label>Carrera Específica</label>
                        <input type="text" name="carrera" id="inputCarrera" placeholder="Ej. Arquitecto, Ing. en Software, etc." required>
                    </div>

                    <details class="acordeon-pagos">
                        <summary>💳 GESTIÓN DE PAGOS</summary>
                        <div class="grid-pagos">
                            <div><label>Separación</label><input type="number" class="pago-input" name="separacion" id="inputP0" value="0"></div>
                            <div><label>Pago 1</label><input type="number" class="pago-input" name="pago1" id="inputP1" value="0"></div>
                            <div><label>Pago 2</label><input type="number" class="pago-input" name="pago2" id="inputP2" value="0"></div>
                            <div><label>Pago 3</label><input type="number" class="pago-input" name="pago3" id="inputP3" value="0"></div>
                            <div><label>Pago 4</label><input type="number" class="pago-input" name="pago4" id="inputP4" value="0"></div>
                            <div><label>Pago 5</label><input type="number" class="pago-input" name="pago5" id="inputP5" value="0"></div>
                        </div>
                    </details>

                    <div style="grid-column: span 3; margin-top: 15px;">
                        <label>Fecha de Operación</label>
                        <input type="date" name="fecha" id="inputFecha" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                </div>

                <div class="btn-wrapper">
                    <button type="submit" name="accion_guardar" value="salir" class="btn-submit btn-salir">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar y Salir
                    </button>
                    <button type="submit" name="accion_guardar" value="nuevo" class="btn-submit btn-nuevo">
                        <i class="fa-solid fa-user-plus"></i> Guardar y Nuevo
                    </button>
                </div>

            </form>

            <div>
                <div class="preview-panel">
                    <div class="preview-title">Vista Previa del Alumno</div>
                    
                    <div class="preview-header">
                        <div class="preview-clave" id="prevClave">#<?php echo $nueva_clave_auto; ?></div>
                        <div class="preview-status">Nuevo Ingreso</div>
                    </div>

                    <div class="preview-body">
                        <div class="preview-label">Estudiante</div>
                        <div class="preview-name" id="prevNombre">---</div>
                        
                        <div class="preview-school">
                            <div><i class="fa-solid fa-building-columns"></i> <span id="prevInstitucion">Facultad</span></div>
                            <div id="prevCarreraContenedor" style="font-size: 0.75rem; color: #ccc; margin-left: 20px;">
                                <i class="fa-solid fa-graduation-cap"></i> <span id="prevCarrera">---</span>
                            </div>
                        </div>

                        <div class="preview-label" style="margin-top:10px;">Contacto Directo</div>
                        <div class="preview-grid-mini">
                            <div><i class="fa-solid fa-mobile-screen"></i> <span id="prevTelA">---</span></div>
                            <div><i class="fa-solid fa-truck-medical" style="color:#ff4d4d;"></i> <span id="prevTelE">---</span></div>
                            <div style="grid-column: span 2;"><i class="fa-solid fa-calendar-day"></i> <span id="prevFecha"><?php echo date('Y-m-d'); ?></span></div>
                        </div>
                    </div>

                    <div class="preview-breakdown">
                        <div class="preview-label" style="margin-bottom: 10px;">Desglose Financiero</div>
                        <div class="breakdown-row"><span>Separación:</span> <span id="prevP0">$0.00</span></div>
                        <div class="breakdown-row"><span>Pago 1:</span> <span id="prevP1">$0.00</span></div>
                        <div class="breakdown-row"><span>Pago 2:</span> <span id="prevP2">$0.00</span></div>
                        <div class="breakdown-row"><span>Pago 3:</span> <span id="prevP3">$0.00</span></div>
                        <div class="breakdown-row"><span>Pago 4:</span> <span id="prevP4">$0.00</span></div>
                        <div class="breakdown-row"><span>Pago 5:</span> <span id="prevP5">$0.00</span></div>
                        <div class="breakdown-row total">
                            <span>TOTAL:</span> 
                            <span id="prevTotal">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

        </div> </div> </div>

<script>
    const selectPrepa = document.getElementById('select-prepa');
    const selectFacu = document.getElementById('select-facu');
    const inputCarrera = document.getElementById('inputCarrera');
    const contenedorFacu = document.getElementById('contenedorfacu');
    const contenedorCarrera = document.getElementById('contenedorcarrera');
    const prevCarreraContenedor = document.getElementById('prevCarreraContenedor');
    
    const handlePrepaChange = function() {
        if (this.value === 'Si') { 
            // --- ES PREPARATORIA ---
            contenedorFacu.classList.add('hidden'); // Ocultar Facultad
            contenedorCarrera.classList.add('hidden'); // Ocultar Carrera
            
            selectFacu.value = 'No aplica';
            inputCarrera.value = 'No aplica'; // Relleno por defecto para que deje guardar
            prevCarreraContenedor.style.display = 'none'; // Quitar de la vista previa
        } else { 
            // --- ES UNIVERSIDAD (No) ---
            contenedorFacu.classList.remove('hidden'); // Mostrar Facultad
            contenedorCarrera.classList.remove('hidden'); // Mostrar Carrera
            
            if(inputCarrera.value === 'No aplica') inputCarrera.value = ''; // Limpiar el "No aplica"
            prevCarreraContenedor.style.display = 'block';
        }
        actualizarVistaPrevia();
    };

    selectPrepa.addEventListener('change', handlePrepaChange);

    // Forzar la validación al cargar la página (por si acaso)
    handlePrepaChange.call(selectPrepa);

    // 2. Lógica Vista Previa Expandida
    const form = document.getElementById('formRegistro');
    
    // Función para formatear moneda
    const formatMoney = (val) => '$' + parseFloat(val || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    function actualizarVistaPrevia() {
        // Textos y Nombres
        const nombres = document.getElementById('inputNombres').value;
        const paterno = document.getElementById('inputPaterno').value;
        const materno = document.getElementById('inputMaterno').value;
        let nombreCompleto = `${nombres} ${paterno} ${materno}`.trim();
        document.getElementById('prevNombre').innerText = nombreCompleto || '---';

        // Institución y Carrera
        let institucionText = "Facultad";
        if(selectPrepa.value === 'Si') {
            institucionText = "Nivel Preparatoria";
        } else if (selectFacu.value && selectFacu.value !== 'No aplica') {
            institucionText = selectFacu.value;
        }
        document.getElementById('prevInstitucion').innerText = institucionText;
        document.getElementById('prevCarrera').innerText = inputCarrera.value || '---';

        // Teléfonos
        document.getElementById('prevTelA').innerText = document.getElementById('inputTelA').value || '---';
        document.getElementById('prevTelE').innerText = document.getElementById('inputTelE').value || '---';
        
        // Fecha
        document.getElementById('prevFecha').innerText = document.getElementById('inputFecha').value || '---';

        // Desglose de Pagos
        let p0 = parseFloat(document.getElementById('inputP0').value) || 0;
        let p1 = parseFloat(document.getElementById('inputP1').value) || 0;
        let p2 = parseFloat(document.getElementById('inputP2').value) || 0;
        let p3 = parseFloat(document.getElementById('inputP3').value) || 0;
        let p4 = parseFloat(document.getElementById('inputP4').value) || 0;
        let p5 = parseFloat(document.getElementById('inputP5').value) || 0;

        document.getElementById('prevP0').innerText = formatMoney(p0);
        document.getElementById('prevP1').innerText = formatMoney(p1);
        document.getElementById('prevP2').innerText = formatMoney(p2);
        document.getElementById('prevP3').innerText = formatMoney(p3);
        document.getElementById('prevP4').innerText = formatMoney(p4);
        document.getElementById('prevP5').innerText = formatMoney(p5);

        // Total
        let total = p0 + p1 + p2 + p3 + p4 + p5;
        document.getElementById('prevTotal').innerText = formatMoney(total);
    }

    // Escuchamos escritura en tiempo real
    form.addEventListener('input', actualizarVistaPrevia);
    form.addEventListener('change', actualizarVistaPrevia);
    actualizarVistaPrevia();
</script>

</body>
</html>