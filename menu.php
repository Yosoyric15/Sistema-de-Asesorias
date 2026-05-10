<?php
session_start();

// SEGURIDAD: Expulsar si no hay sesión activa
if (!isset($_SESSION['admin_logeado']) || $_SESSION['admin_logeado'] !== true) {
    header("Location: index.php");
    exit();
}

/** @noinspection PhpUndefinedFunctionInspection */
date_default_timezone_set('America/Monterrey');
$nombre_encargada = $_SESSION['admin_nombre']; //
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Cre-C</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

        body {
            margin: 0; padding: 0; font-family: 'Open Sans', sans-serif; color: #d9e4ff; min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
            background: linear-gradient(180deg, rgba(2, 8, 18, 0.9), rgba(8, 14, 24, 0.96)),
                        url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg') center/cover no-repeat fixed;
            overflow: hidden;
        }

        .panel-container {
            width: 100%; max-width: 1100px;
            background: rgba(8, 14, 24, 0.78); backdrop-filter: blur(26px);
            -webkit-backdrop-filter: blur(26px);
            border-radius: 24px; padding: 50px;
            border: 1px solid rgba(56, 84, 130, 0.18);
            box-shadow: 0 32px 70px rgba(0, 0, 0, 0.7), inset 0 0 22px rgba(44, 68, 112, 0.12);
            margin: 20px; animation: fadeInScale 0.85s cubic-bezier(0.22, 0.68, 0.34, 1);
        }

        .panel-header { text-align: center; margin-bottom: 50px; }
        .panel-header h1 { font-family: 'Montserrat', sans-serif; font-size: 2.4rem; text-transform: uppercase; letter-spacing: 4px; margin: 0; color: #eef4ff; text-shadow: 0 0 20px rgba(36, 80, 150, 0.35); }
        .panel-header h1 span { color: #8fa9dc; }
        .panel-header p { color: rgba(210, 224, 255, 0.72); font-size: 0.9rem; letter-spacing: 2px; margin-top: 10px; text-transform: uppercase; }

        .panel-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
        .panel-column { display: flex; flex-direction: column; gap: 15px; opacity: 0; animation: slideUp 1s ease-out forwards; }
        .panel-column:nth-child(1) { animation-delay: 0.18s; }
        .panel-column:nth-child(2) { animation-delay: 0.36s; }
        .panel-column:nth-child(3) { animation-delay: 0.54s; }

        .column-title { font-family: 'Montserrat', sans-serif; font-size: 0.85rem; color: #8fa9dc; text-transform: uppercase; letter-spacing: 2px; text-align: center; margin-bottom: 15px; border-bottom: 1px dashed rgba(143, 169, 220, 0.2); padding-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; }

        .btn-menu { display: flex; align-items: center; gap: 15px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(110, 130, 170, 0.16); color: #e9efff; padding: 18px 25px; border-radius: 14px; text-decoration: none; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; transition: 0.3s; position: relative; overflow: hidden; justify-content: center; }
        .btn-principal { border-color: rgba(110, 144, 205, 0.24); background: rgba(56, 88, 145, 0.12); }
        .btn-menu i { font-size: 1.2rem; color: rgba(143, 169, 220, 0.7); transition: 0.3s; width: 25px; text-align: center; position: absolute; left: 20px; }
        .btn-menu:hover { transform: translateY(-5px); background: rgba(72, 100, 150, 0.16); border-color: rgba(100, 132, 184, 0.28); box-shadow: 0 10px 30px rgba(30, 58, 110, 0.22); color: #eef4ff; }
        .btn-menu:hover i { color: #acc7ff; transform: scale(1.1); }

        .btn-danger { margin-top: 10px; }
        .btn-danger i { color: rgba(220, 120, 120, 0.65); }
        .btn-danger:hover { background: rgba(135, 55, 75, 0.14); border-color: rgba(205, 90, 90, 0.4); box-shadow: 0 10px 25px rgba(160, 70, 90, 0.18); color: #ffb4c2; }
        .btn-danger:hover i { color: #ff8a9d; }

        .live-clock { font-family: 'Montserrat', sans-serif; color: #8fa9dc; font-size: 0.85rem; margin-top: 20px; letter-spacing: 1px; font-weight: 600; text-align: center; }

        @media (max-width: 900px) { .panel-grid { grid-template-columns: 1fr; gap: 30px; } .panel-container { padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="panel-container">
        <div class="panel-header">
            <h1>Panel de <span>Control</span></h1>
            <p>Asesorías Cre-C | Encargada: <strong><?php echo strtoupper($nombre_encargada); ?></strong></p>
        </div>
        <div class="panel-grid">
            <!-- COLUMNA 1: ACADÉMICO -->
            <div class="panel-column">
                <div class="column-title"><i class="fa-solid fa-graduation-cap"></i> Académico</div>
                <a href="AsesoriasPrincipal.php" class="btn-menu btn-principal"><i class="fa-solid fa-user-plus"></i> Nuevo Registro</a>
                <a href="asistencias.php" class="btn-menu"><i class="fa-solid fa-list-check"></i> Lista Asistencia</a>
                <a href="Consultai.php" class="btn-menu"><i class="fa-solid fa-magnifying-glass"></i> Consulta Individual</a>
            </div>
            <!-- COLUMNA 2: OPERACIONES (CORREGIDA) -->
            <div class="panel-column">
                <div class="column-title"><i class="fa-solid fa-briefcase"></i> Operaciones</div>
                <a href="ConsultaG.php" class="btn-menu btn-principal"><i class="fa-solid fa-sliders"></i> Gestión General</a>
                <a href="CorteCaja.php" class="btn-menu"><i class="fa-solid fa-cash-register"></i> Corte de Caja</a>
                <!-- AQUÍ ESTÁ TU COLUMNA DE GASTOS -->
                <a href="finanzas.php" class="btn-menu"><i class="fa-solid fa-hand-holding-dollar"></i> Registro de Egresos</a>
            </div>
            <!-- COLUMNA 3: ADMINISTRACIÓN -->
            <div class="panel-column">
                <div class="column-title"><i class="fa-solid fa-shield-halved"></i> Administración</div>
                <a href="dashboard.php" class="btn-menu btn-principal"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="Bitacora.php" class="btn-menu"><i class="fa-solid fa-file-shield"></i> Bitácora</a>
                <a href="logout.php" class="btn-menu btn-danger" onclick="confirmarSalida(event)"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
            </div>
        </div>
        <div class="live-clock" id="relojDigital">Sincronizando...</div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function actualizarReloj() {
            const ahora = new Date();
            const tiempo = ahora.toLocaleTimeString('es-MX', { hour: '2-digit', minute:'2-digit', second:'2-digit' });
            document.getElementById('relojDigital').innerHTML = `<i class="fa-regular fa-clock"></i> TIEMPO: ${tiempo}`;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();

        function confirmarSalida(evento) {
            evento.preventDefault();
            const link = evento.currentTarget.getAttribute('href');
            Swal.fire({
                title: '¿Cerrar Sesión?', icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#ff4d4d', cancelButtonColor: '#1a1a2e',
                confirmButtonText: 'Sí, salir', background: '#0a0a0f', color: '#fff'
            }).then((result) => { if (result.isConfirmed) window.location.href = link; });
        }
    </script>
</body>
</html>