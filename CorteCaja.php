<?php
session_start();
$host = "sql312.infinityfree.com";
$user = "if0_41511449";
$pass = "n25Jhbe0BhJQx7";
$db   = "if0_41511449_asesorias";

$conexion = mysqli_connect($host, $user, $pass, $db);
mysqli_set_charset($conexion, "utf8");

$hoy = date('Y-m-d');

// 1. Resumen General
$q_resumen = mysqli_query($conexion, "SELECT 
    COUNT(*) as total_movs,
    SUM(total) as gran_total,
    SUM(separacion) as total_sep,
    SUM(pago1+pago2+pago3+pago4+pago5) as total_abonos
    FROM pagos WHERE fecha = '$hoy'");
$resumen = mysqli_fetch_assoc($q_resumen);

// 2. Desglose por Facultad 
$q_facultades = mysqli_query($conexion, "SELECT a.facultad, COUNT(*) as cantidad, SUM(p.total) as monto 
    FROM alumnos a 
    INNER JOIN pagos p ON a.id = p.id_alumno 
    WHERE p.fecha = '$hoy' 
    GROUP BY a.facultad ORDER BY monto DESC");

// 3. Auditoría: Últimos 10 pagos
$q_detalles = mysqli_query($conexion, "SELECT a.nombres, a.apPaterno, p.total, p.separacion, a.claveAlumno 
    FROM alumnos a 
    INNER JOIN pagos p ON a.id = p.id_alumno 
    WHERE p.fecha = '$hoy' ORDER BY p.id_pago DESC LIMIT 10");

// 4. Conteo Prepa vs Universidad
$q_niveles = mysqli_query($conexion, "SELECT preparatoria, COUNT(*) as c FROM alumnos a INNER JOIN pagos p ON a.id = p.id_alumno WHERE p.fecha = '$hoy' GROUP BY preparatoria");
$prepa_hoy = 0; $univ_hoy = 0;
while($n = mysqli_fetch_assoc($q_niveles)) {
    if($n['preparatoria'] == 1) $prepa_hoy = $n['c']; else $univ_hoy = $n['c'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SISTEMA CRE-C | CORTE PROFESIONAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Open+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        :root {
            --cyan-neon: #00e5ff;
            --green-neon: #00ff66;
            --purple-neon: #a855f7;
            --bg-glass: rgba(255, 255, 255, 0.03);
            --border-glass: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0; padding: 40px; font-family: 'Open Sans', sans-serif; color: white;
            background: linear-gradient(rgba(10,10,20,0.95), rgba(10,10,20,0.95)), url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg');
            background-size: cover; background-attachment: fixed; min-height: 100vh;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        /* HEADER */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-family: 'Montserrat'; font-weight: 900; letter-spacing: 4px; color: #fff; margin: 0; text-transform: uppercase; }
        .header h1 span { color: var(--cyan-neon); text-shadow: 0 0 15px var(--cyan-neon); }
        
        .btn-nav {
            text-decoration: none; color: #fff; font-family: 'Montserrat'; font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; padding: 10px 20px; border: 1px solid var(--cyan-neon);
            border-radius: 10px; background: rgba(0, 229, 255, 0.1); transition: 0.3s;
        }
        .btn-nav:hover { background: var(--cyan-neon); color: #000; box-shadow: 0 0 20px var(--cyan-neon); }

        /* GRID */
        .report-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 30px; }

        .glass-card {
            background: var(--bg-glass); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass); border-radius: 20px; padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4); position: relative; overflow: hidden;
        }

        /* Iluminación superior por tarjeta */
        .glass-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; }
        .c-green::before { background: var(--green-neon); box-shadow: 0 0 15px var(--green-neon); }
        .c-purple::before { background: var(--purple-neon); box-shadow: 0 0 15px var(--purple-neon); }
        .c-cyan::before { background: var(--cyan-neon); box-shadow: 0 0 15px var(--cyan-neon); }

        .tag { font-family: 'Montserrat'; font-size: 0.65rem; font-weight: 800; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; display: block; }
        
        .big-value { font-family: 'Montserrat'; font-size: 2.8rem; font-weight: 900; color: var(--green-neon); text-shadow: 0 0 20px rgba(0,255,102,0.4); margin: 10px 0; }
        
        /* TABLAS INTERNAS */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; font-size: 0.6rem; color: var(--cyan-neon); text-transform: uppercase; padding: 10px; border-bottom: 1px solid var(--border-glass); font-family: 'Montserrat'; }
        td { padding: 12px 10px; font-size: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.03); }
        
        .row-hover:hover td { background: rgba(255,255,255,0.02); }

        .progress-container { height: 8px; background: rgba(0,0,0,0.3); border-radius: 10px; margin: 20px 0; overflow: hidden; display: flex; }
        .p-univ { background: var(--cyan-neon); box-shadow: 0 0 10px var(--cyan-neon); }
        .p-prepa { background: var(--purple-neon); box-shadow: 0 0 10px var(--purple-neon); }

        .audit-log { grid-column: span 3; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>CORTE <span>DIARIO</span></h1>
            <p style="margin:5px 0 0 0; font-size:0.7rem; letter-spacing:3px; opacity:0.5;"><?php echo strtoupper(date('d M Y')); ?> | TERMINAL DE AUDITORÍA</p>
        </div>
        <a href="menu.php" class="btn-nav">🏠 VOLVER AL PANEL</a>
    </div>

    <div class="report-grid">
        <div class="glass-card c-green">
            <span class="tag">Ingreso Total Bruto</span>
            <div class="big-value">$<?php echo number_format($resumen['gran_total'] ?? 0, 2); ?></div>
            <div style="display: flex; justify-content: space-between; margin-top: 20px; font-size: 0.75rem;">
                <div><span style="opacity:0.4;">SEP:</span> <b style="color:var(--cyan-neon);">$<?php echo number_format($resumen['total_sep'] ?? 0, 2); ?></b></div>
                <div><span style="opacity:0.4;">PAGOS:</span> <b style="color:var(--green-neon);">$<?php echo number_format($resumen['total_abonos'] ?? 0, 2); ?></b></div>
            </div>
            <div style="margin-top:20px; padding:10px; border-radius:10px; background:rgba(0,0,0,0.2); text-align:center;">
                <span style="font-size:0.6rem; opacity:0.5;">REGISTROS HOY: </span>
                <span style="font-weight:800; color:var(--cyan-neon);"><?php echo $resumen['total_movs']; ?></span>
            </div>
        </div>

        <div class="glass-card c-purple">
            <span class="tag">Captación por Nivel</span>
            <div style="margin-top: 20px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:0.8rem;">
                    <span>Universidad</span> <b style="color:var(--cyan-neon);"><?php echo $univ_hoy; ?></b>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                    <span>Preparatoria</span> <b style="color:var(--purple-neon);"><?php echo $prepa_hoy; ?></b>
                </div>
            </div>
            <div class="progress-container">
                <?php 
                $total_h = ($univ_hoy + $prepa_hoy) ?: 1;
                $p_univ = ($univ_hoy / $total_h) * 100;
                $p_prepa = ($prepa_hoy / $total_h) * 100;
                ?>
                <div class="p-univ" style="width: <?php echo $p_univ; ?>%"></div>
                <div class="p-prepa" style="width: <?php echo $p_prepa; ?>%"></div>
            </div>
            <span style="font-size:0.55rem; opacity:0.3; text-transform:uppercase;">Balance de tráfico educativo en tiempo real</span>
        </div>

        <div class="glass-card c-cyan">
            <span class="tag">Rendimiento por Facultad</span>
            <table>
                <thead>
                    <tr><th>Facultad</th><th style="text-align:right;">Monto</th></tr>
                </thead>
                <tbody>
                    <?php while($f = mysqli_fetch_assoc($q_facultades)): ?>
                    <tr class="row-hover">
                        <td style="font-size: 0.65rem; font-weight:600;"><?php echo $f['facultad']; ?></td>
                        <td style="text-align:right; color:var(--green-neon); font-weight:800;">$<?php echo number_format($f['monto'], 0); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="glass-card c-cyan audit-log">
            <span class="tag">Historial de Auditoría (Últimos movimientos)</span>
            <table>
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Estudiante</th>
                        <th>Concepto</th>
                        <th style="text-align:right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($d = mysqli_fetch_assoc($q_detalles)): ?>
                    <tr class="row-hover">
                        <td style="color:var(--cyan-neon); font-weight:800;">#<?php echo $d['claveAlumno']; ?></td>
                        <td style="font-weight:600;"><?php echo $d['apPaterno']." ".$d['nombres']; ?></td>
                        <td style="opacity:0.6; font-size:0.7rem;"><?php echo ($d['separacion'] > 0) ? "INSCRIPCIÓN / SEP" : "ABONO DE ASESORÍA"; ?></td>
                        <td style="text-align:right; color:var(--green-neon); font-weight:800;">$<?php echo number_format($d['total'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>