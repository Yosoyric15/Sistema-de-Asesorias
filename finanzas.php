<?php
session_start();
// Conexión a la base de datos
$conexion = mysqli_connect("sql312.infinityfree.com", "if0_41511449", "n25Jhbe0BhJQx7", "if0_41511449_asesorias");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");

// Procesar nuevo Egreso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_egreso'])) {
    $tipo = mysqli_real_escape_string($conexion, $_POST['tipo']);
    $monto = floatval($_POST['monto']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $retro = mysqli_real_escape_string($conexion, $_POST['retroalimentacion']);

    $sql = "INSERT INTO egresos (tipo, monto, fecha, retroalimentacion) VALUES ('$tipo', '$monto', '$fecha', '$retro')";
    if(mysqli_query($conexion, $sql)){
        $mensaje_exito = "Gasto registrado correctamente.";
    }
}

// ---------------- CÁLCULOS DE UTILIDAD ----------------
// 1. Total Ingresos (de la tabla pagos)
$query_ingresos = mysqli_query($conexion, "SELECT SUM(total) as total_ingresos FROM pagos");
$row_ingresos = mysqli_fetch_assoc($query_ingresos);
$total_ingresos = $row_ingresos['total_ingresos'] ? $row_ingresos['total_ingresos'] : 0;

// 2. Total Egresos
$query_egresos = mysqli_query($conexion, "SELECT SUM(monto) as total_egresos FROM egresos");
$row_egresos = mysqli_fetch_assoc($query_egresos);
$total_egresos = $row_egresos['total_egresos'] ? $row_egresos['total_egresos'] : 0;

// 3. Utilidad Neta
$utilidad = $total_ingresos - $total_egresos;

// 4. Obtener desglose de egresos para la tabla
$query_tabla_egresos = mysqli_query($conexion, "SELECT * FROM egresos ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero y Egresos | Cre-C</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

        body {
            margin: 0; padding: 20px; font-family: 'Open Sans', sans-serif; color: #d9e4ff; min-height: 100vh;
            background: linear-gradient(180deg, rgba(2, 8, 18, 0.9), rgba(8, 14, 24, 0.96)),
                        url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg') center/cover no-repeat fixed;
        }

        .container { max-width: 1400px; margin: 0 auto; width: 100%; }

        /* Navegación y Encabezados */
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid rgba(143, 169, 220, 0.2); padding-bottom: 18px; }
        .btn-action { display: inline-block; text-decoration: none; color: #e9efff; font-family: 'Montserrat'; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; padding: 12px 22px; border: 1px solid rgba(143, 169, 220, 0.28); border-radius: 12px; background: rgba(56, 88, 145, 0.12); cursor: pointer; transition: 0.3s; }
        .btn-action:hover { background: rgba(143, 169, 220, 0.2); color: #acc7ff; box-shadow: 0 0 18px rgba(30, 58, 110, 0.25); }
        .btn-print { border-color: rgba(143, 169, 220, 0.35); }
        .btn-print:hover { background: rgba(100, 132, 184, 0.18); color: #d9e4ff; }
        h1 { font-family: 'Montserrat'; text-transform: uppercase; letter-spacing: 3px; color: #eef4ff; margin: 0; font-size: 1.6rem; text-shadow: 0 0 18px rgba(36, 80, 150, 0.3); }

        /* Widgets de Utilidad */
        .widgets-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 28px; opacity: 0; animation: fadeInScale 0.9s ease-out forwards; }
        .widget { background: rgba(8, 14, 24, 0.65); border: 1px solid rgba(56, 84, 130, 0.2); padding: 20px 24px; border-radius: 14px; display: flex; flex-direction: column; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35); }
        .widget-title { font-family: 'Montserrat'; font-size: 0.72rem; text-transform: uppercase; color: rgba(210, 224, 255, 0.65); letter-spacing: 1.3px; }
        .widget-value { font-size: 2.1rem; font-weight: 800; font-family: 'Montserrat'; margin-top: 8px; }
        .text-ingreso { color: #6fc784; text-shadow: 0 0 12px rgba(111, 199, 132, 0.28); }
        .text-egreso { color: #e08a9a; text-shadow: 0 0 12px rgba(224, 138, 154, 0.25); }
        .text-utilidad { color: #8fa9dc; text-shadow: 0 0 12px rgba(143, 169, 220, 0.3); }

        /* Layout principal */
        .main-grid { display: grid; grid-template-columns: 320px 1fr; gap: 24px; }

        /* Panel Izquierdo: Formulario */
        .form-panel { background: rgba(8, 14, 24, 0.78); border: 1px solid rgba(56, 84, 130, 0.18); padding: 26px; border-radius: 14px; height: fit-content; box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5); opacity: 0; animation: slideUp 1s ease-out forwards 0.15s; }
        .form-panel h3 { font-family: 'Montserrat'; font-size: 0.9rem; color: #eef4ff; border-bottom: 1px dashed rgba(143, 169, 220, 0.2); padding-bottom: 14px; margin: 0 0 18px 0; text-transform: uppercase; letter-spacing: 1.5px; }
        label { display: block; font-family: 'Montserrat'; font-size: 0.7rem; color: #8aa4d9; text-transform: uppercase; margin: 14px 0 7px; letter-spacing: 1px; font-weight: 700; }
        input, select, textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(130, 150, 185, 0.18); background: rgba(255, 255, 255, 0.05); color: #e9efff; font-family: 'Open Sans'; font-size: 0.9rem; box-sizing: border-box; outline: none; transition: 0.25s; }
        input::placeholder, textarea::placeholder { color: rgba(209, 219, 243, 0.42); }
        input:focus, select:focus, textarea:focus { border-color: rgba(109, 133, 195, 0.5); background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 20px rgba(34, 58, 110, 0.24); }
        .btn-submit { width: 100%; padding: 14px; margin-top: 18px; border: none; border-radius: 10px; background: linear-gradient(135deg, #0b1a33, #1f3766); color: #eef4ff; font-family: 'Montserrat'; font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1.4px; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(30, 70, 140, 0.32); filter: brightness(1.08); }

        /* Panel Derecho: Tabla */
        .data-panel { background: rgba(8, 14, 24, 0.78); border: 1px solid rgba(56, 84, 130, 0.18); border-radius: 14px; overflow: hidden; box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5); opacity: 0; animation: slideUp 1s ease-out forwards 0.3s; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th { background: rgba(44, 68, 112, 0.2); padding: 14px 16px; text-align: left; font-family: 'Montserrat'; text-transform: uppercase; font-size: 0.75rem; color: #8aa4d9; border-bottom: 1px solid rgba(143, 169, 220, 0.2); letter-spacing: 1px; font-weight: 700; }
        td { padding: 12px 16px; border-bottom: 1px solid rgba(143, 169, 220, 0.1); color: #d9e4ff; vertical-align: middle; }
        tr:hover td { background: rgba(56, 88, 145, 0.12); }
        .td-monto { font-family: 'Montserrat'; font-weight: 700; color: #e08a9a; }
        .td-fecha { white-space: nowrap; color: #8fa9dc; }
        .retro-text { max-width: 400px; word-wrap: break-word; font-size: 0.8rem; color: rgba(210, 224, 255, 0.72); }
        .msg-success { color: #6fc784; font-size: 0.8rem; margin-bottom: 12px; font-weight: 700; }
        .msg-empty { text-align: center; padding: 28px 16px; color: rgba(210, 224, 255, 0.45); font-size: 0.9rem; }

        @media (max-width: 1024px) {
            .main-grid { grid-template-columns: 1fr; }
            .widgets-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .widgets-grid { grid-template-columns: 1fr; }
            .header-bar { flex-direction: column; gap: 12px; align-items: flex-start; }
        }

        @media print {
            body { background: white; color: black; padding: 0; }
            .form-panel, .btn-action, .btn-print { display: none; }
            .main-grid { grid-template-columns: 1fr; }
            .widget { border: 1px solid #ccc; background: transparent; box-shadow: none; }
            .text-ingreso, .text-egreso, .text-utilidad { color: black; text-shadow: none; }
            h1, th { color: black; text-shadow: none; border-color: #ccc; }
            td { border-color: #eee; color: black; }
            .td-monto, .td-fecha, .retro-text { color: black; }
            .data-panel, .widgets-grid { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-bar">
        <h1><i class="fa-solid fa-chart-line"></i> Reporte Financiero y Egresos | Cre-c</h1>
        <div>
            <a href="ConsultaG.php" class="btn-action"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            <button onclick="window.print()" class="btn-action btn-print"><i class="fa-solid fa-file-pdf"></i> Generar Reporte</button>
        </div>
    </div>

    <!-- WIDGETS DE UTILIDAD -->
    <div class="widgets-grid">
        <div class="widget">
            <span class="widget-title">Total Ingresos Brutos</span>
            <span class="widget-value text-ingreso">$<?php echo number_format($total_ingresos, 2); ?></span>
        </div>
        <div class="widget">
            <span class="widget-title">Total Egresos (Pagos)</span>
            <span class="widget-value text-egreso">-$<?php echo number_format($total_egresos, 2); ?></span>
        </div>
        <div class="widget" style="border-color: rgba(143, 169, 220, 0.35); background: rgba(56, 88, 145, 0.15);">
            <span class="widget-title" style="color: #8fa9dc;">Utilidad Neta (Fondo)</span>
            <span class="widget-value text-utilidad">$<?php echo number_format($utilidad, 2); ?></span>
        </div>
    </div>

    <div class="main-grid">
        <!-- FORMULARIO DE EGRESOS -->
        <div class="form-panel">
            <h3>Registrar Salida de Dinero</h3>
            <?php if(isset($mensaje_exito)) echo "<div class='msg-success'>✓ $mensaje_exito</div>"; ?>
            
            <form method="POST" action="">
                <label>Tipo de Gasto</label>
                <select name="tipo" required>
                    <option value="">Seleccionar...</option>
                    <option value="Renta">Renta de Local/Servicios</option>
                    <option value="Pago Asesores">Pago a Asesores</option>
                    <option value="Manuales">Materiales e Impresiones</option>
                    <option value="Publicidad">Publicidad</option>
                    <option value="Otros">Otros (Especificar en notas)</option>
                </select>

                <label>Monto a Retirar ($)</label>
                <input type="number" name="monto" step="0.01" required placeholder="0.00">

                <label>Fecha del Movimiento</label>
                <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>

                <label>Retroalimentación / Notas</label>
                <textarea name="retroalimentacion" rows="3" placeholder="Ej: Pago de quincena al Ing. García, o Factura folio #123..." required></textarea>

                <button type="submit" name="registrar_egreso" class="btn-submit"><i class="fa-solid fa-money-bill-transfer"></i> Registrar Gasto</button>
            </form>
        </div>

        <!-- TABLA DENSA DE REPORTES -->
        <div class="data-panel">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Retroalimentación / Concepto</th>
                        <th style="text-align: right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query_tabla_egresos) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query_tabla_egresos)): ?>
                            <tr>
                                <td>#<?php echo str_pad($row['id_egreso'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td class="td-fecha"><?php echo $row['fecha']; ?></td>
                                <td><strong><?php echo $row['tipo']; ?></strong></td>
                                <td class="retro-text"><?php echo $row['retroalimentacion']; ?></td>
                                <td class="td-monto" style="text-align: right;">-$<?php echo number_format($row['monto'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="msg-empty">No hay egresos registrados aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>