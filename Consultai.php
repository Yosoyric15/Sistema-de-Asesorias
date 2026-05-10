<?php 
error_reporting(E_ALL);

$conexion = mysqli_connect("sql312.infinityfree.com", "if0_41511449", "n25Jhbe0BhJQx7", "if0_41511449_asesorias");

if(!$conexion) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$alumno = null;

$id_final = null;
if(isset($_GET["id"])) {
    $id_final = mysqli_real_escape_string($conexion, $_GET["id"]);
} elseif(isset($_POST["id_busqueda"])) {
    $id_final = mysqli_real_escape_string($conexion, $_POST["id_busqueda"]);
}

if($id_final) {
    $sql = "SELECT a.*, p.separacion, p.pago1, p.pago2, p.pago3, p.pago4, p.pago5 
            FROM alumnos a 
            LEFT JOIN pagos p ON a.id = p.id_alumno 
            WHERE a.id = '$id_final'";
            
    $resultado = mysqli_query($conexion, $sql);
    if($resultado) {
        $alumno = mysqli_fetch_assoc($resultado);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente Alumno | Asesorías Cre-c</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        /* --- ANIMACIONES --- */
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        body {
            margin: 0; padding: 40px 20px; 
            font-family: 'Open Sans', sans-serif;
            color: white; min-height: 100vh; 
            display: flex; flex-direction: column; align-items: center;
            background: linear-gradient(rgba(10,10,20,0.9), rgba(10,10,20,0.95)), 
                        url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg');
            background-size: cover; background-attachment: fixed; background-position: center;
            animation: fadeIn 1s ease;
        }

        .main-wrapper { max-width: 850px; width: 100%; animation: slideUp 0.8s ease-out; }

        /* --- CARD PREMIUM --- */
        .card {
            background: rgba(255, 255, 255, 0.04); 
            padding: 40px; 
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.15); 
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        h2 { 
            font-family: 'Montserrat', sans-serif; 
            text-transform: uppercase; 
            letter-spacing: 3px; 
            color: #fff; 
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(90deg, #fff, #00e5ff);
            -background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h3 { 
            font-family: 'Montserrat', sans-serif; 
            color: #00e5ff; 
            border-bottom: 1px solid rgba(0, 229, 255, 0.3); 
            padding-bottom: 10px; 
            margin-top: 40px; 
            text-transform: uppercase; 
            font-size: 0.8rem; 
            letter-spacing: 2px;
        }

        /* --- BUSCADOR --- */
        .busqueda {
            background: rgba(255, 255, 255, 0.05); 
            padding: 20px; 
            border-radius: 15px; 
            margin-bottom: 35px;
            display: flex; gap: 15px; align-items: center; justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        input[type="number"]#search { 
            background: rgba(255, 255, 255, 0.9); 
            color: #1a1a1a; 
            width: 160px; padding: 14px; 
            border-radius: 10px; border: none; 
            font-weight: 700; font-size: 1rem;
            outline: none;
        }

        /* --- CAMPOS DE DATOS --- */
        label { 
            font-family: 'Montserrat', sans-serif; font-size: 0.65rem; 
            color: #00e5ff; font-weight: 800; display: block; margin-bottom: 8px; 
            text-transform: uppercase; letter-spacing: 1.5px;
            margin-top: 20px;
        }

        input[readonly] {
            width: 100%; padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);
            background: rgba(255, 255, 255, 0.07); color: #fff; 
            font-weight: 600; font-size: 0.95rem;
            box-sizing: border-box;
        }

        /* --- BOTONES --- */
        .btn-consultar {
            background: linear-gradient(90deg, #0055ff, #00e5ff); 
            color: white; border: none; padding: 14px 30px; 
            border-radius: 10px; cursor: pointer; font-weight: 800; 
            font-family: 'Montserrat', sans-serif; transition: 0.4s; text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-consultar:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0, 229, 255, 0.4); }

        .btn-volver {
            display: inline-block; color: #fff; text-decoration: none;
            font-family: 'Montserrat'; font-weight: 700; font-size: 0.75rem;
            border: 1px solid rgba(255,255,255,0.2); padding: 12px 20px; border-radius: 10px;
            transition: 0.3s; text-transform: uppercase; margin: 10px 5px;
        }
        .btn-volver:hover { background: #fff; color: #000; }

        .btn-menu-principal {
            border-color: #00e5ff;
            background: rgba(0, 229, 255, 0.05);
            color: #00e5ff;
        }
        .btn-menu-principal:hover { background: #00e5ff; color: #000; }

        /* --- CONTENEDOR DE PAGOS --- */
        .acordeon-pagos {
            background: rgba(0, 0, 0, 0.2); 
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px; margin-top: 25px;
        }
        summary { padding: 15px; cursor: pointer; color: #00e5ff; text-align: center; list-style: none; font-weight: 800; font-size: 0.8rem; font-family: 'Montserrat'; }
        
        .grid-comprimido {
            display: grid; grid-template-columns: repeat(3, 1fr); 
            gap: 15px; padding: 25px; border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .pago-item input { 
            text-align: center; 
            border: 1px solid rgba(0, 229, 255, 0.3) !important; 
            color: #4ade80 !important;
            background: rgba(74, 222, 128, 0.05) !important;
        }

        @media (max-width: 650px) { .grid-comprimido { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="card">
        <h2>Expediente del Estudiante</h2>
        
        <form action="" method="post" class="busqueda">
            <input type="number" name="id_busqueda" id="search" placeholder="ID Alumno" required>
            <button type="submit" class="btn-consultar">🔍 Buscar</button>
        </form>

        <?php if($alumno): ?>
            <h3>Información Académica</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>ID Registro</label>
                    <input type="text" value="#<?php echo $alumno['id']; ?>" readonly>
                </div>
                <div>
                    <label>Clave Alumno</label>
                    <input type="text" value="<?php echo $alumno['claveAlumno']; ?>" readonly>
                </div>
            </div>

            <label>Nombre Completo</label>
            <input type="text" value="<?php echo $alumno['nombres'] . ' ' . $alumno['apPaterno'] . ' ' . $alumno['apMaterno']; ?>" readonly>
            
            <label>Facultad Universitaria / Institución</label>
            <input type="text" value="<?php echo $alumno['facultad']; ?>" readonly>

            <label>Nivel Académico</label>
            <input type="text" value="<?php echo ($alumno['preparatoria'] == 1) ? 'PREPARATORIA' : 'UNIVERSIDAD'; ?>" readonly>

            <h3>Estatus Financiero</h3>
            <label>Teléfono de Contacto</label>
            <input type="text" value="<?php echo $alumno['telefonoAlumno']; ?>" readonly>
            
            <details class="acordeon-pagos" open>
                <summary>📊 DESGLOSE DE PAGOS</summary>
                <div class="grid-comprimido">
                    <div class="pago-item"><label>Separación</label><input type="text" value="$<?php echo number_format($alumno['separacion'] ?? 0, 2); ?>" readonly></div>
                    <div class="pago-item"><label>Pago 1</label><input type="text" value="$<?php echo number_format($alumno['pago1'] ?? 0, 2); ?>" readonly></div>
                    <div class="pago-item"><label>Pago 2</label><input type="text" value="$<?php echo number_format($alumno['pago2'] ?? 0, 2); ?>" readonly></div>
                    <div class="pago-item"><label>Pago 3</label><input type="text" value="$<?php echo number_format($alumno['pago3'] ?? 0, 2); ?>" readonly></div>
                    <div class="pago-item"><label>Pago 4</label><input type="text" value="$<?php echo number_format($alumno['pago4'] ?? 0, 2); ?>" readonly></div>
                    <div class="pago-item"><label>Pago 5</label><input type="text" value="$<?php echo number_format($alumno['pago5'] ?? 0, 2); ?>" readonly></div>
                </div>
            </details>

            <div style="text-align: center; margin-top: 35px;">
                <a href="menu.php" class="btn-volver btn-menu-principal">🏠 Menú Principal</a>
                <a href="ConsultaG.php" class="btn-volver">← Listado General</a>
            </div>

        <?php elseif($id_final): ?>
            <div style="text-align: center; padding: 20px;">
                <p style="color: #ff4d4d; font-weight: 800; font-size: 1.1rem;">⚠️ EXPEDIENTE NO ENCONTRADO</p>
                <p style="opacity: 0.6; font-size: 0.9rem;">El ID <?php echo $id_final; ?> no existe en la base de datos.</p>
                <a href="menu.php" class="btn-volver btn-menu-principal">🏠 Menú Principal</a>
                <a href="ConsultaG.php" class="btn-volver">Reintentar</a>
            </div>
        <?php else: ?>
            <p style="text-align: center; opacity: 0.5; margin: 40px 0; font-size: 0.9rem;">Ingrese un número de ID para visualizar el expediente completo.</p>
            <div style="text-align: center;">
                <a href="menu.php" class="btn-volver btn-menu-principal">🏠 Volver al Menú</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php mysqli_close($conexion); ?>
</body>
</html>