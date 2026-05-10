<?php
session_start();

// Conexión a la base de datos InfinityFree
$host = "sql312.infinityfree.com";
$user_db = "if0_41511449";
$pass_db = "n25Jhbe0BhJQx7";
$db      = "if0_41511449_asesorias";

$conexion = mysqli_connect($host, $user_db, $pass_db, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");

// ==========================================
// LÓGICA DE ASIGNACIÓN DE GRUPOS (MÁXIMO 25)
// ==========================================
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    $id_alumno = intval($_POST['id_alumno']);
    
    if ($_POST['accion'] == 'agregar_al_grupo') {
        $id_g = intval($_POST['id_grupo_destino']);
        // Validar que el grupo no pase de 25 alumnos
        $check = mysqli_query($conexion, "SELECT COUNT(*) as total FROM alumnos WHERE id_grupo = $id_g AND estatus = 1");
        $cupo = mysqli_fetch_assoc($check)['total'];
        
        if ($cupo < 25) {
            mysqli_query($conexion, "UPDATE alumnos SET id_grupo = $id_g WHERE id = $id_alumno");
        } else {
            $mensaje = "<script>alert('Error: El grupo destino ya está lleno (25 alumnos max).');</script>";
        }
    } 
    elseif ($_POST['accion'] == 'remover_del_grupo') {
        mysqli_query($conexion, "UPDATE alumnos SET id_grupo = NULL WHERE id = $id_alumno");
    }
    elseif ($_POST['accion'] == 'cambiar_grupo') {
        $nuevo_g = intval($_POST['nuevo_grupo']);
        $check = mysqli_query($conexion, "SELECT COUNT(*) as total FROM alumnos WHERE id_grupo = $nuevo_g AND estatus = 1");
        if (mysqli_fetch_assoc($check)['total'] < 25) {
            mysqli_query($conexion, "UPDATE alumnos SET id_grupo = $nuevo_g WHERE id = $id_alumno");
        } else {
            $mensaje = "<script>alert('Error: El grupo destino está lleno.');</script>";
        }
    }
}

// Obtener lista de grupos creados
$query_grupos = mysqli_query($conexion, "SELECT * FROM grupos ORDER BY nombre_grupo ASC");
$grupos = [];
while($g = mysqli_fetch_assoc($query_grupos)){ $grupos[] = $g; }

// Determinar qué grupo se está visualizando
$grupo_actual_id = isset($_GET['id_grupo']) ? intval($_GET['id_grupo']) : (!empty($grupos) ? $grupos[0]['id_grupo'] : 0);
$nombre_grupo_actual = "Seleccione un Grupo";
foreach($grupos as $g){ if($g['id_grupo'] == $grupo_actual_id) $nombre_grupo_actual = $g['nombre_grupo']; }

// 1. Traer alumnos del grupo actual
$query_alumnos_grupo = mysqli_query($conexion, "SELECT id, claveAlumno, nombres, apPaterno, apMaterno FROM alumnos WHERE estatus = 1 AND id_grupo = $grupo_actual_id ORDER BY apPaterno ASC LIMIT 25");

// 2. Traer alumnos FALTANTES (Sin grupo asignado)
$query_faltantes = mysqli_query($conexion, "SELECT id, claveAlumno, nombres, apPaterno, apMaterno FROM alumnos WHERE estatus = 1 AND id_grupo IS NULL ORDER BY apPaterno ASC");

echo $mensaje;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos y Asistencia | Cre-C</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        /* =========================================
           MODO PANTALLA (ESTILOS OSCUROS Y NEÓN)
           ========================================= */
        @media screen {
            body { 
                margin: 0; padding: 20px; font-family: 'Open Sans', sans-serif; color: white; 
                background: #0a0a0f; 
                background-image: radial-gradient(circle at 50% 0%, #1a1a2e 0%, #0a0a0f 70%);
                min-height: 100vh;
            }
            .container { max-width: 1500px; margin: 0 auto; width: 100%; } 
            
            /* Panel Superior de Control */
            .header-bar { 
                display: flex; justify-content: space-between; align-items: center; 
                margin-bottom: 20px; background: rgba(255, 255, 255, 0.03); padding: 15px 25px; 
                border-radius: 12px; border: 1px solid rgba(0,229,255,0.2);
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            }
            
            .controles-extra { display: flex; align-items: center; gap: 15px; }
            .controles-extra label { font-family: 'Montserrat'; font-size: 0.75rem; color: #00e5ff; text-transform: uppercase; font-weight: 800; }
            
            .input-neon, .select-neon {
                background: rgba(0,0,0,0.4); border: 1px solid rgba(0,229,255,0.3);
                color: white; padding: 8px 12px; border-radius: 6px;
                font-family: 'Open Sans'; font-weight: 600; font-size: 0.8rem; outline: none; transition: 0.3s;
            }
            .select-neon option { background: #1a1a2e; color: white; }
            .input-neon:focus, .select-neon:focus { border-color: #00e5ff; box-shadow: 0 0 10px rgba(0,229,255,0.3); }

            .btn-action { background: rgba(0, 229, 255, 0.05); color: #fff; padding: 10px 20px; border: 1px solid rgba(0, 229, 255, 0.4); border-radius: 8px; cursor: pointer; font-weight: 800; font-family: 'Montserrat'; text-transform: uppercase; transition: 0.3s; font-size: 0.75rem; text-decoration: none; display: inline-block; }
            .btn-action:hover { background: #00e5ff; color: black; box-shadow: 0 0 15px rgba(0,229,255,0.5); }
            
            .btn-print { background: transparent; border-color: #a855f7; color: #a855f7; }
            .btn-print:hover { background: #a855f7; color: white; box-shadow: 0 0 15px rgba(168,85,247,0.5); }

            /* LAYOUT ALTA DENSIDAD */
            .layout-grid { display: grid; grid-template-columns: 380px 1fr; gap: 20px; align-items: start; }

            /* Panel Faltantes (Izquierda) */
            .panel-faltantes { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,77,77,0.3); border-radius: 12px; padding: 15px; max-height: 80vh; overflow-y: auto; }
            .panel-faltantes h3 { margin-top: 0; color: #ff4d4d; font-family: 'Montserrat'; font-size: 0.9rem; text-transform: uppercase; border-bottom: 1px solid rgba(255,77,77,0.2); padding-bottom: 10px; position: sticky; top: -15px; background: rgba(10,10,15,0.95); z-index: 10;}
            .item-faltante { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.75rem; transition: 0.2s;}
            .item-faltante:hover { background: rgba(255,255,255,0.02); }
            .btn-add { background: rgba(0,255,102,0.1); color: #00ff66; border: 1px solid #00ff66; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.7rem; transition: 0.3s;}
            .btn-add:hover { background: #00ff66; color: black; }

            /* Panel Lista Oficial (Derecha) */
            .panel-lista { 
                background: rgba(255, 255, 255, 0.02); border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); 
                padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            }

            .header-doc { border-bottom: 1px dashed rgba(0,229,255,0.3); padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
            .doc-title { text-align: center; text-transform: uppercase; margin-bottom: 20px; font-size: 1.1rem; font-family: 'Montserrat'; font-weight: 800; color: #fff; letter-spacing: 2px; }
            .info-doc { text-align: right; font-size: 0.7rem; color: #aaa; line-height: 1.6; font-family: 'Montserrat'; }

            /* Tabla Principal */
            table { width: 100%; border-collapse: collapse; font-size: 0.75rem; background: rgba(0,0,0,0.3); }
            th { background: rgba(0, 229, 255, 0.05); border-bottom: 1px solid rgba(0,229,255,0.2); padding: 10px; text-transform: uppercase; color: #00e5ff; letter-spacing: 1px; text-align: left; font-family: 'Montserrat'; font-size: 0.65rem; }
            td { border-bottom: 1px solid rgba(255,255,255,0.05); padding: 8px 10px; color: #ddd; vertical-align: middle; }
            tr:hover td { background: rgba(255,255,255,0.02); }
            
            .col-id { width: 30px; text-align: center; font-weight: 800; color: rgba(255,255,255,0.3); }
            .col-clave { width: 70px; color: #a855f7; font-weight: 700; font-family: 'Montserrat'; }
            
            .btn-remove { background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 0.9rem; padding: 2px 5px; transition: 0.2s;}
            .btn-remove:hover { transform: scale(1.2); }

            .firmas { display: flex; justify-content: space-around; margin-top: 40px; }
            .linea-firma { width: 200px; border-top: 1px solid rgba(255,255,255,0.2); text-align: center; font-size: 0.65rem; padding-top: 8px; color: rgba(255,255,255,0.5); text-transform: uppercase; font-family: 'Montserrat'; }
        }

        /* =========================================
           MODO IMPRESIÓN (COMPRIMIDO A 1 HOJA)
           ========================================= */
        @media print {
            body { background: white !important; color: black !important; font-family: Arial, sans-serif; margin: 0; padding: 0; }
            .header-bar, .panel-faltantes, .col-acciones { display: none !important; } 
            .container { max-width: 100%; }
            .layout-grid { display: block; }
            
            .panel-lista { padding: 0; border: none; box-shadow: none; background: transparent; }
            
            /* Reducimos espacios del encabezado */
            .header-doc { border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: flex-end; }
            .info-doc { text-align: right; font-size: 0.75rem; color: #000; line-height: 1.2; }
            .doc-title { text-align: center; text-transform: uppercase; margin-bottom: 10px; font-size: 1rem; font-weight: bold; color: #000; }
            
            /* Comprimimos la tabla para que entren los 25 alumnos */
            table { width: 100%; border-collapse: collapse; margin-bottom: 15px; background: transparent; }
            th { background: #f2f2f2 !important; border: 1px solid #000 !important; padding: 4px; font-size: 0.7rem; color: #000; }
            td { border: 1px solid #000 !important; padding: 3px 8px; font-size: 0.75rem; height: 16px; color: #000; }
            
            .col-id { width: 30px; text-align: center; font-weight: bold; color: #000; }
            .col-clave { width: 80px; text-align: center; color: #000; }
            
            /* Subimos las firmas para ganar espacio */
            .firmas { display: flex; justify-content: space-around; margin-top: 25px; }
            .linea-firma { width: 200px; border-top: 1px solid #000; text-align: center; font-size: 0.65rem; padding-top: 5px; color: #000; text-transform: uppercase; }
            
            /* Reducimos los márgenes de la hoja física (1 cm arriba/abajo, 1.5 cm a los lados) */
            @page { size: A4 portrait; margin: 10mm 15mm; }
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- BARRA SUPERIOR -->
    <div class="header-bar">
        <a href="menu.php" class="btn-action"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        
        <div class="controles-extra">
            <label>Filtrar Grupo:</label>
            <form action="" method="GET" style="margin:0;">
                <select name="id_grupo" class="select-neon" onchange="this.form.submit()">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach($grupos as $g): ?>
                        <option value="<?php echo $g['id_grupo']; ?>" <?php if($grupo_actual_id == $g['id_grupo']) echo 'selected'; ?>>
                            <?php echo $g['nombre_grupo']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <label style="margin-left: 10px;">Ciclo:</label>
            <input type="text" id="inputCiclo" class="input-neon" style="width: 80px;" value="<?php echo date('Y'); ?>" oninput="actualizarCiclo()">
        </div>

        <button onclick="window.print()" class="btn-action btn-print"><i class="fa-solid fa-print"></i> Imprimir Lista</button>
    </div>

    <div class="layout-grid">
        
        <!-- PANEL IZQUIERDO: FALTANTES -->
        <div class="panel-faltantes">
            <h3><i class="fa-solid fa-user-clock"></i> Faltantes (<?php echo mysqli_num_rows($query_faltantes); ?>)</h3>
            <?php if($grupo_actual_id > 0): ?>
                <p style="font-size:0.65rem; color:#aaa; margin-top:0; margin-bottom:10px;">Envíalos al <strong><?php echo $nombre_grupo_actual; ?></strong> con un clic.</p>
                
                <?php while($f = mysqli_fetch_assoc($query_faltantes)): ?>
                    <div class="item-faltante">
                        <div>
                            <span style="color:#00e5ff; font-weight:800; font-family:'Montserrat';">#<?php echo $f['claveAlumno']; ?></span>
                            <br><?php echo $f['apPaterno'] . " " . $f['apMaterno'] . " " . $f['nombres']; ?>
                        </div>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="accion" value="agregar_al_grupo">
                            <input type="hidden" name="id_alumno" value="<?php echo $f['id']; ?>">
                            <input type="hidden" name="id_grupo_destino" value="<?php echo $grupo_actual_id; ?>">
                            <button type="submit" class="btn-add" title="Agregar a <?php echo $nombre_grupo_actual; ?>"><i class="fa-solid fa-plus"></i></button>
                        </form>
                    </div>
                <?php endwhile; ?>
                
            <?php else: ?>
                <p style="font-size:0.75rem; color:#ff4d4d; text-align:center; padding:20px 0;">Sube a la barra superior y selecciona un grupo para empezar a asignar.</p>
            <?php endif; ?>
        </div>

        <!-- PANEL DERECHO: DOCUMENTO PDF / LISTA OFICIAL -->
        <div class="panel-lista">
            <div class="header-doc">
                <div>
                    <!-- Logo opcional -->
                    <h2 style="margin:0; font-family:'Montserrat'; color:#00e5ff; font-size: 1.2rem;">SISTEMA CRE-C</h2>
                </div>
                <div class="info-doc">
                    <strong>FORMATO:</strong> Control de Asistencia<br>
                    <strong>GRUPO:</strong> <span style="color:#00e5ff; font-weight:800; font-size:0.8rem;"><?php echo mb_strtoupper($nombre_grupo_actual); ?></span><br>
                    <strong>FECHA:</strong> <?php echo date('d/m/Y'); ?><br>
                    <strong>CICLO:</strong> <span id="textoCiclo"><?php echo date('Y'); ?></span>
                </div>
            </div>

            <div class="doc-title">Lista Oficial de Cupo (25 Espacios)</div>

            <table>
                <thead>
                    <tr>
                        <th class="col-id">#</th>
                        <th class="col-clave">Clave</th>
                        <th>Nombre del Estudiante</th>
                        <th>Firma / Observaciones</th>
                        <th class="col-acciones no-print" style="text-align: center;"><i class="fa-solid fa-gear"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $contador = 0;
                    while($row = mysqli_fetch_array($query_alumnos_grupo)): 
                        $contador++;
                    ?>
                    <tr>
                        <td class="col-id"><?php echo $contador; ?></td>
                        <td class="col-clave"><?php echo $row['claveAlumno']; ?></td>
                        <td><?php echo $row['apPaterno'] . " " . $row['apMaterno'] . " " . $row['nombres']; ?></td>
                        <td></td>
                        <td class="col-acciones no-print" style="width: 120px;">
                            <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                <!-- Mover a otro grupo -->
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="accion" value="cambiar_grupo">
                                    <input type="hidden" name="id_alumno" value="<?php echo $row['id']; ?>">
                                    <select name="nuevo_grupo" class="select-neon" style="padding:2px 4px; font-size:0.65rem;" onchange="this.form.submit()">
                                        <option value="">Mover...</option>
                                        <?php foreach($grupos as $g): if($g['id_grupo'] != $grupo_actual_id): ?>
                                            <option value="<?php echo $g['id_grupo']; ?>"><?php echo $g['nombre_grupo']; ?></option>
                                        <?php endif; endforeach; ?>
                                    </select>
                                </form>
                                <!-- Quitar del grupo -->
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="accion" value="remover_del_grupo">
                                    <input type="hidden" name="id_alumno" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn-remove" title="Mandar a Faltantes"><i class="fa-solid fa-user-minus"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php 
                    // Rellenar filas vacías hasta llegar a 25
                    for($i = $contador + 1; $i <= 25; $i++): 
                    ?>
                    <tr>
                        <td class="col-id"><?php echo $i; ?></td>
                        <td class="col-clave">---</td>
                        <td style="color: rgba(255,255,255,0.2); font-style: italic; font-size: 0.7rem;">Espacio Disponible</td>
                        <td></td>
                        <td class="col-acciones no-print"></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <div class="firmas">
                <div class="linea-firma">Firma del Docente</div>
                <div class="linea-firma">Firma del Coordinador</div>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para que el ciclo escolar se actualice en la hoja al escribir
    function actualizarCiclo() {
        var valor = document.getElementById('inputCiclo').value;
        document.getElementById('textoCiclo').innerText = valor;
    }
</script>

</body>
</html>