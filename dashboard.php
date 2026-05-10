<?php
session_start();

if (!isset($_SESSION['admin_logeado'])) {
    header("Location: login.php");
    exit();
}

$host = "sql312.infinityfree.com";
$user_db = "if0_41511449";
$pass_db = "n25Jhbe0BhJQx7"; // <-- RECUERDA PONER TU CONTRASEÑA
$db   = "if0_41511449_asesorias";

$conexion = mysqli_connect($host, $user_db, $pass_db, $db);

if (!$conexion) {
    die("error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8"); 

// --- 1. CONSULTAS DE KPIs ---
$res_total = mysqli_query($conexion, "SELECT COUNT(*) as t FROM alumnos");
$total_alumnos = mysqli_fetch_assoc($res_total)['t'];

$res_activos = mysqli_query($conexion, "SELECT COUNT(*) as a FROM alumnos WHERE estatus = 1");
$activos = mysqli_fetch_assoc($res_activos)['a'];

$inactivos = $total_alumnos - $activos;
$retencion = ($total_alumnos > 0) ? round(($activos / $total_alumnos) * 100, 1) : 0;

$res_ingresos = mysqli_query($conexion, "SELECT SUM(separacion) as s, SUM(pago1) as p1, SUM(pago2) as p2, SUM(pago3) as p3, SUM(pago4) as p4, SUM(pago5) as p5, SUM(total) as t_ingresos FROM pagos");
$ing = mysqli_fetch_assoc($res_ingresos);
$total_ingresos = $ing['t_ingresos'] ?? 0;

$ticket_promedio = ($activos > 0) ? ($total_ingresos / $activos) : 0;

// --- INGRESOS DEL DÍA (NEW) ---
$hoy = date('Y-m-d');
$res_hoy = mysqli_query($conexion, "SELECT SUM(total) as total_hoy, COUNT(*) as movimientos FROM pagos WHERE DATE(fecha) = '$hoy'");
$hoy_data = mysqli_fetch_assoc($res_hoy);
$ingresos_hoy = $hoy_data['total_hoy'] ?? 0;
$movimientos_hoy = $hoy_data['movimientos'] ?? 0;

// --- ÚLTIMOS MOVIMIENTOS DE BITÁCORA (NEW) ---
$res_bitacora = mysqli_query($conexion, "SELECT usuario, accion, fecha_hora FROM bitacora ORDER BY fecha_hora DESC LIMIT 8");
$bitacora_movimientos = [];
while($row = mysqli_fetch_assoc($res_bitacora)){
    $bitacora_movimientos[] = $row;
}
$json_bitacora = json_encode($bitacora_movimientos);

// --- 2. CONSULTAS DE GRÁFICAS ---
$prepa = 0; $facu = 0;
$res_niveles = mysqli_query($conexion, "SELECT preparatoria, COUNT(*) as c FROM alumnos GROUP BY preparatoria");
while($row = mysqli_fetch_assoc($res_niveles)){
    if($row['preparatoria'] == 1) $prepa += $row['c']; else $facu += $row['c'];
}

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

$fac_data = array_fill_keys($facultades_uanl, 0);
$res_fac = mysqli_query($conexion, "SELECT facultad, COUNT(*) as c FROM alumnos WHERE preparatoria = 0 AND facultad != '' AND facultad != 'No aplica' GROUP BY facultad");
while($row = mysqli_fetch_assoc($res_fac)){
    $f = $row['facultad'];
    if(isset($fac_data[$f])) { $fac_data[$f] = $row['c']; }
}

$fac_nombres = []; $fac_cantidades = [];
foreach($fac_data as $nombre => $cantidad) {
    $fac_nombres[] = "'" . mysqli_real_escape_string($conexion, $nombre) . "'";
    $fac_cantidades[] = $cantidad;
}
$str_fac_nombres = implode(",", $fac_nombres);
$str_fac_cantidades = implode(",", $fac_cantidades);

$meses = []; $ingresos_mes = [];
$res_tendencia = mysqli_query($conexion, "SELECT DATE_FORMAT(fecha, '%b %Y') as mes, SUM(total) as suma FROM pagos WHERE fecha IS NOT NULL AND fecha != '0000-00-00' GROUP BY DATE_FORMAT(fecha, '%Y-%m') ORDER BY fecha ASC LIMIT 6");
while($row = mysqli_fetch_assoc($res_tendencia)){
    $meses[] = "'" . $row['mes'] . "'";
    $ingresos_mes[] = $row['suma'];
}
$str_meses = implode(",", $meses);
$str_ingresos_mes = implode(",", $ingresos_mes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cre-C | Centro de Control Neón</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --blue-dark: #0b1a33;
            --blue-medium: #1f3766;
            --blue-accent: #8fa9dc;
            --blue-light: #acc7ff;
            --green-muted: #6fc784;
            --red-muted: #e08a9a;
            --glass-bg: rgba(8, 14, 24, 0.65);
            --glass-border: rgba(56, 84, 130, 0.18);
            --text-main: #eef4ff;
            --text-muted: rgba(210, 224, 255, 0.72);
        }

        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

        body {
            margin: 0; padding: 20px 0;
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(180deg, rgba(2, 8, 18, 0.9), rgba(8, 14, 24, 0.96)),
                        url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg') center/cover no-repeat fixed;
            background-size: cover; background-attachment: fixed;
            color: var(--text-main);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .top-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
            background: var(--glass-bg); backdrop-filter: blur(26px);
            -webkit-backdrop-filter: blur(26px);
            padding: 14px 20px;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
            animation: fadeInScale 0.85s cubic-bezier(0.22, 0.68, 0.34, 1);
        }
        .top-header h1 { 
            margin: 0; font-family: 'Montserrat'; font-size: 1.4rem; text-transform: uppercase; 
            letter-spacing: 2px; color: var(--text-main); text-shadow: 0 0 18px rgba(36, 80, 150, 0.3);
        }
        .header-actions { display: flex; align-items: center; gap: 10px; }
        .btn-nav {
            background: rgba(56, 88, 145, 0.12);
            color: var(--blue-light); border: 1px solid rgba(143, 169, 220, 0.28);
            padding: 8px 16px; border-radius: 10px; text-decoration: none; font-weight: 700; font-family: 'Montserrat';
            transition: 0.3s; font-size: 0.75rem; letter-spacing: 0.8px;
        }
        .btn-nav:hover { background: rgba(100, 132, 184, 0.18); border-color: rgba(100, 132, 184, 0.4); color: #d9e4ff; box-shadow: 0 8px 20px rgba(30, 58, 110, 0.22); transform: translateY(-1px); }

        .user-profile { display: flex; align-items: center; gap: 8px; font-weight: 600; font-family: 'Montserrat'; font-size: 0.8rem; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #0b1a33, #1f3766); color: #acc7ff;
            display: flex; justify-content: center; align-items: center;
            font-weight: 800; font-size: 1rem;
            box-shadow: 0 0 18px rgba(143, 169, 220, 0.25); border: 1px solid rgba(143, 169, 220, 0.3);
        }

        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; opacity: 0; animation: slideUp 1s ease-out forwards 0.1s; }
        .kpi-card {
            background: var(--glass-bg); padding: 16px;
            border-radius: 14px; border: 1px solid var(--glass-border);
            backdrop-filter: blur(26px);
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            transition: 0.3s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4); }
        .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 2px; }
        .border-cyan::before { background: linear-gradient(90deg, var(--blue-accent), var(--blue-light)); box-shadow: 0 0 12px rgba(143, 169, 220, 0.4); }
        .border-green::before { background: linear-gradient(90deg, var(--green-muted), #7fd991); box-shadow: 0 0 12px rgba(111, 199, 132, 0.3); }
        .border-purple::before { background: linear-gradient(90deg, #8a7cb8, #a8a0d4); box-shadow: 0 0 12px rgba(168, 160, 212, 0.3); }
        .border-red::before { background: linear-gradient(90deg, var(--red-muted), #f09fb8); box-shadow: 0 0 12px rgba(224, 138, 154, 0.3); }

        .kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;}
        .kpi-title { font-family: 'Montserrat'; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; }
        .kpi-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; }
        
        .icon-cyan { background: rgba(143, 169, 220, 0.12); color: var(--blue-accent); border: 1px solid rgba(143, 169, 220, 0.25); }
        .icon-green { background: rgba(111, 199, 132, 0.12); color: var(--green-muted); border: 1px solid rgba(111, 199, 132, 0.25); }
        .icon-purple { background: rgba(168, 160, 212, 0.12); color: #8a7cb8; border: 1px solid rgba(168, 160, 212, 0.25); }
        .icon-red { background: rgba(224, 138, 154, 0.12); color: var(--red-muted); border: 1px solid rgba(224, 138, 154, 0.25); }
        
        .kpi-value { font-size: 1.8rem; font-weight: 800; margin: 0; font-family: 'Montserrat'; }
        .glow-cyan { color: var(--blue-light); text-shadow: 0 0 16px rgba(143, 169, 220, 0.3); }
        .glow-green { color: var(--green-muted); text-shadow: 0 0 16px rgba(111, 199, 132, 0.28); }
        .glow-purple { color: #8a7cb8; text-shadow: 0 0 16px rgba(168, 160, 212, 0.3); }
        .glow-red { color: var(--red-muted); text-shadow: 0 0 16px rgba(224, 138, 154, 0.28); }

        .charts-grid-split { display: grid; grid-template-columns: 2.2fr 1fr; gap: 16px; margin-bottom: 16px; }
        .charts-grid-full { display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 16px; }
        .charts-grid-triple { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
        
        .chart-card {
            background: var(--glass-bg); padding: 16px;
            border-radius: 14px; border: 1px solid var(--glass-border);
            backdrop-filter: blur(26px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            opacity: 0; animation: slideUp 1s ease-out forwards;
        }
        .charts-grid-split .chart-card:nth-child(1) { animation-delay: 0.18s; }
        .charts-grid-split .chart-card:nth-child(2) { animation-delay: 0.26s; }
        .charts-grid-full .chart-card { animation-delay: 0.34s; }
        .charts-grid-triple .chart-card:nth-child(1) { animation-delay: 0.42s; }
        .charts-grid-triple .chart-card:nth-child(2) { animation-delay: 0.50s; }
        .charts-grid-triple .chart-card:nth-child(3) { animation-delay: 0.58s; }
        
        .chart-header { 
            font-family: 'Montserrat'; font-size: 0.8rem; font-weight: 800; color: var(--text-main); 
            text-transform: uppercase; margin-top: 0; margin-bottom: 14px; 
            border-bottom: 1px solid rgba(143, 169, 220, 0.15); padding-bottom: 10px; letter-spacing: 1px;
        }
        
        .chart-wrapper { position: relative; height: 260px; }
        .chart-wrapper-tall { position: relative; height: 600px; }
        .chart-wrapper-small { position: relative; height: 220px; } 

        @media (max-width: 1024px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid-split { grid-template-columns: 1fr; }
            .charts-grid-triple { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .charts-grid-triple { grid-template-columns: 1fr; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .top-header { flex-direction: column; gap: 12px; align-items: flex-start; }
            .header-actions { width: 100%; justify-content: space-between; }
        }
        
        @media (max-width: 640px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .top-header { padding: 12px 16px; }
            .top-header h1 { font-size: 1.2rem; }
            .btn-nav { padding: 6px 12px; font-size: 0.7rem; }
        }
    </style>
</head>
<body>

<div class="container">
    
    <header class="top-header">
        <h1><i class="fa-solid fa-chart-pie"></i> Centro de Control</h1>
        <div class="header-actions">
            <a href="ConsultaG.php" class="btn-nav"><i class="fa-solid fa-users"></i> Ir a Registros</a>
            <a href="menu.php" class="btn-nav"><i class="fa-solid fa-list"></i> Ir al menú principal</a>
            <div class="user-profile">
                <span>Administrador</span>
                <div class="avatar">A</div>
            </div>
        </div>
    </header>

    <div class="kpi-grid">
        <div class="kpi-card border-cyan">
            <div class="kpi-header">
                <span class="kpi-title">Alumnos Activos</span>
                <div class="kpi-icon icon-cyan"><i class="fa-solid fa-user-graduate"></i></div>
            </div>
            <p class="kpi-value glow-cyan"><?php echo $activos; ?></p>
        </div>
        <div class="kpi-card border-green">
            <div class="kpi-header">
                <span class="kpi-title">Ingresos Totales</span>
                <div class="kpi-icon icon-green"><i class="fa-solid fa-sack-dollar"></i></div>
            </div>
            <p class="kpi-value glow-green">$<?php echo number_format($total_ingresos, 2); ?></p>
        </div>
        <div class="kpi-card border-purple">
            <div class="kpi-header">
                <span class="kpi-title">Ticket Promedio</span>
                <div class="kpi-icon icon-purple"><i class="fa-solid fa-receipt"></i></div>
            </div>
            <p class="kpi-value glow-purple">$<?php echo number_format($ticket_promedio, 2); ?></p>
        </div>
        <div class="kpi-card border-red">
            <div class="kpi-header">
                <span class="kpi-title">Bajas / Inactivos</span>
                <div class="kpi-icon icon-red"><i class="fa-solid fa-user-xmark"></i></div>
            </div>
            <p class="kpi-value glow-red"><?php echo $inactivos; ?></p>
        </div>
    </div>

    <div class="charts-grid-split">
        <div class="chart-card">
            <h3 class="chart-header">Tendencia de Recaudación (Mensual)</h3>
            <div class="chart-wrapper">
                <canvas id="tendenciaChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3 class="chart-header">Nivel Educativo</h3>
            <div class="chart-wrapper">
                <canvas id="nivelesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="charts-grid-triple">
        <div class="chart-card">
            <h3 class="chart-header">Desglose de Ingresos</h3>
            <div class="chart-wrapper-small">
                <canvas id="ingresosChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3 class="chart-header">Estado General</h3>
            <div class="chart-wrapper-small">
                <canvas id="estadoChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3 class="chart-header">Distribución</h3>
            <div class="chart-wrapper-small">
                <canvas id="distribucionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="charts-grid-full">
        <div class="chart-card">
            <h3 class="chart-header">Población por Facultad UANL</h3>
            <div class="chart-wrapper-tall">
                <canvas id="facultadesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="charts-grid-full">
        <div class="chart-card">
            <h3 class="chart-header">Últimos Movimientos (Bitácora)</h3>
            <div style="overflow-x: auto; max-height: 280px; overflow-y: auto;">
                <table style="width: 100%; font-size: 0.85rem; border-collapse: collapse;">
                    <thead style="position: sticky; top: 0; background: var(--blue-medium); opacity: 0.8;">
                        <tr>
                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border); color: var(--blue-accent);">USUARIO</th>
                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border); color: var(--blue-accent);">ACCIÓN</th>
                            <th style="padding: 10px; text-align: center; border-bottom: 1px solid var(--glass-border); color: var(--blue-accent);">HORA</th>
                        </tr>
                    </thead>
                    <tbody id="bitacoraBody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
var chartData = {
    str_meses: [<?php echo !empty($str_meses) ? $str_meses : "'Mes actual'"; ?>],
    str_ingresos_mes: [<?php echo !empty($str_ingresos_mes) ? $str_ingresos_mes : "0"; ?>],
    prepa: <?php echo $prepa; ?>,
    facu: <?php echo $facu; ?>,
    ing: {
        s: <?php echo $ing['s'] ?? 0; ?>,
        p1: <?php echo $ing['p1'] ?? 0; ?>,
        p2: <?php echo $ing['p2'] ?? 0; ?>,
        p3: <?php echo $ing['p3'] ?? 0; ?>,
        p4: <?php echo $ing['p4'] ?? 0; ?>,
        p5: <?php echo $ing['p5'] ?? 0; ?>
    },
    str_fac_nombres: [<?php echo !empty($str_fac_nombres) ? $str_fac_nombres : "'Sin datos'"; ?>],
    str_fac_cantidades: [<?php echo !empty($str_fac_cantidades) ? $str_fac_cantidades : "0"; ?>],
    activos: <?php echo $activos; ?>,
    inactivos: <?php echo $inactivos; ?>,
    retencion: <?php echo $retencion; ?>,
    ingresos_hoy: <?php echo $ingresos_hoy; ?>,
    movimientos_hoy: <?php echo $movimientos_hoy; ?>,
    bitacora: <?php echo $json_bitacora; ?>
};

// Llenar tabla de bitácora
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('bitacoraBody');
    if (chartData.bitacora && Array.isArray(chartData.bitacora)) {
        chartData.bitacora.forEach(function(mov) {
            const tr = document.createElement('tr');
            const fecha_hora = new Date(mov.fecha_hora);
            const hora_str = fecha_hora.toLocaleTimeString('es-MX');
            tr.innerHTML = `
                <td style="padding: 10px; border-bottom: 1px solid var(--glass-border); color: var(--blue-light);">${mov.usuario || 'Admin'}</td>
                <td style="padding: 10px; border-bottom: 1px solid var(--glass-border); color: var(--text-muted); font-size: 0.8rem;">${mov.accion}</td>
                <td style="padding: 10px; text-align: center; border-bottom: 1px solid var(--glass-border); color: rgba(143, 169, 220, 0.6); font-size: 0.75rem;">${hora_str}</td>
            `;
            tbody.appendChild(tr);
        });
    }
});
</script>
<script src="charts.js"></script>

</body>
</html>