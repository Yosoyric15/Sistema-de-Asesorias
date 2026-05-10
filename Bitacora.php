<?php
session_start();
// Seguridad: Si no es admin, no entra
if (!isset($_SESSION['admin_logeado'])) { header("Location: login.php"); exit(); }

$host = "sql312.infinityfree.com";
$user = "if0_41511449"; $pass = "n25Jhbe0BhJQx7"; $db = "if0_41511449_asesorias";
$conexion = mysqli_connect($host, $user, $pass, $db);

// Consultamos los últimos 50 movimientos para no saturar la pantalla
$query = mysqli_query($conexion, "SELECT * FROM bitacora ORDER BY fecha_hora DESC LIMIT 50");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>historial de seguridad | cre-c admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        body { 
            margin: 0; padding: 30px; font-family: 'Open Sans', sans-serif; color: #e2e8f0; 
            background: #0a0a0f; /* Fondo negro profundo */
        }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .header-bitacora {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; border-bottom: 1px solid rgba(0, 229, 255, 0.3);
            padding-bottom: 15px;
        }
        h2 { font-family: 'Montserrat'; color: #00e5ff; text-transform: uppercase; margin: 0; letter-spacing: 2px; }
        
        .btn-regresar {
            text-decoration: none; color: #00e5ff; font-weight: bold; font-size: 0.8rem;
            border: 1px solid #00e5ff; padding: 8px 15px; border-radius: 5px; transition: 0.3s;
        }
        .btn-regresar:hover { background: #00e5ff; color: #000; }

        /* Estilo de consola/terminal */
        .log-container {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.5);
        }

        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; padding: 12px; color: #00e5ff; 
            font-size: 0.7rem; text-transform: uppercase; 
            border-bottom: 1px solid rgba(0, 229, 255, 0.5); 
        }
        td { 
            padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); 
            font-size: 0.85rem; font-family: 'Fira Code', monospace; /* Fuente tipo código */
        }

        .user-tag { color: #facc15; font-weight: bold; }
        .date-tag { color: #94a3b8; font-size: 0.75rem; }
        .action-text { color: #4ade80; } /* Verde para las acciones */

        /* Efecto de pulso para indicar que está "vivo" */
        .live-indicator {
            display: inline-block; width: 8px; height: 8px; background: #ff4d4d;
            border-radius: 50%; margin-right: 8px; animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 77, 77, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 77, 77, 0); }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-bitacora">
        <div>
            <span class="live-indicator"></span>
            <h2>auditoría de movimientos</h2>
        </div>
        <a href="menu.php" class="btn-regresar">VOLVER AL PANEL</a>
    </div>

    <div class="log-container">
        <table>
            <thead>
                <tr>
                    <th width="20%">FECHA Y HORA</th>
                    <th width="15%">USUARIO</th>
                    <th width="65%">ACCIÓN REALIZADA</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_array($query)): ?>
                <tr>
                    <td class="date-tag"><?php echo date('d/m/Y H:i:s', strtotime($row['fecha_hora'])); ?></td>
                    <td class="user-tag"><?php echo $row['usuario']; ?></td>
                    <td class="action-text">> <?php echo $row['accion']; ?></td>
                </tr>
                <?php endwhile; ?>

                <?php if(mysqli_num_rows($query) == 0): ?>
                <tr>
                    <td colspan="3" style="text-align:center; padding:40px; opacity:0.5;">
                        No hay registros en la bitácora todavía.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>