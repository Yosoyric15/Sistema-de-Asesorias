<?php
session_start();

// 1. CONFIGURACIÓN DE CONEXIÓN
$host = "sql312.infinityfree.com";
$user_db = "if0_41511449";
$pass_db = "n25Jhbe0BhJQx7";
$db      = "if0_41511449_asesorias";

$conexion = mysqli_connect($host, $user_db, $pass_db, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$error = false;

// 2. PROCESAMIENTO DE ACCESO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_ingresado = mysqli_real_escape_string($conexion, $_POST['user']);
    $pass_ingresada = mysqli_real_escape_string($conexion, $_POST['pass']);

    $consulta = "SELECT * FROM usuarios WHERE usuario = '$user_ingresado' AND password = '$pass_ingresada'";
    $resultado = mysqli_query($conexion, $consulta);

    if (mysqli_num_rows($resultado) > 0) {
        $datos_usuario = mysqli_fetch_assoc($resultado);
        $_SESSION['admin_logeado'] = true;
        $_SESSION['admin_nombre'] = $datos_usuario['usuario']; 
        header("Location: menu.php");
        exit();
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido | Asesorías Cre-C</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="icono.ico">
    <style>
        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-8px); } 75% { transform: translateX(8px); } }

        body {
            margin: 0; padding: 0; font-family: 'Open Sans', sans-serif; color: #d9e4ff; min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
            background: linear-gradient(180deg, rgba(2, 8, 18, 0.9), rgba(8, 14, 24, 0.96)),
                        url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg') center/cover no-repeat fixed;
            overflow: hidden;
        }

        .panel-container {
            width: 100%; max-width: 460px;
            background: rgba(8, 14, 24, 0.78); backdrop-filter: blur(26px);
            -webkit-backdrop-filter: blur(26px);
            border-radius: 24px; padding: 48px;
            border: 1px solid rgba(56, 84, 130, 0.18);
            box-shadow: 0 32px 70px rgba(0, 0, 0, 0.7), inset 0 0 22px rgba(44, 68, 112, 0.12);
            animation: fadeInScale 0.85s cubic-bezier(0.22, 0.68, 0.34, 1);
        }

        .error-shake { animation: shake 0.42s ease-in-out; border-color: rgba(205, 90, 90, 0.5) !important; }

        .panel-header { text-align: center; margin-bottom: 36px; }
        .logo-admin { height: 76px; width: auto; margin-bottom: 14px; filter: drop-shadow(0 0 12px rgba(28, 70, 145, 0.32)); }

        .panel-header p {
            color: rgba(210, 224, 255, 0.72);
            font-size: 0.78rem; letter-spacing: 2px;
            text-transform: uppercase; font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .input-group { margin-bottom: 22px; opacity: 0; animation: slideUp 0.95s ease-out forwards; }
        .input-group:nth-child(1) { animation-delay: 0.18s; }
        .input-group:nth-child(2) { animation-delay: 0.36s; }

        label {
            display: block; font-family: 'Montserrat'; color: #8aa4d9;
            font-size: 0.72rem; text-transform: uppercase;
            font-weight: 700; margin-bottom: 10px; letter-spacing: 1.4px;
        }

        input {
            width: 100%; padding: 16px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(130, 150, 185, 0.18); border-radius: 14px;
            color: #e9efff; font-size: 1rem; outline: none;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            box-sizing: border-box;
        }
        input::placeholder { color: rgba(209, 219, 243, 0.42); }
        input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(109, 133, 195, 0.5);
            box-shadow: 0 0 18px rgba(34, 58, 110, 0.24);
        }

        .error-message {
            display: none;
            margin-top: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(140, 36, 54, 0.15);
            border: 1px solid rgba(205, 90, 90, 0.35);
            color: #f1d8da;
            font-size: 0.9rem;
            text-align: center;
        }
        .error-visible { display: block; }

        .btn-login {
            width: 100%; padding: 18px; margin-top: 10px;
            background: linear-gradient(135deg, #0b1a33, #1f3766);
            border: none; border-radius: 14px;
            color: #eef4ff; font-family: 'Montserrat'; font-weight: 800;
            font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.6px;
            cursor: pointer; transition: transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease;
            opacity: 0; animation: slideUp 0.95s ease-out forwards; animation-delay: 0.58s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(30, 70, 140, 0.28);
            filter: brightness(1.08);
        }

        .live-clock {
            font-family: 'Montserrat', sans-serif;
            color: #8fa9dc; font-size: 0.75rem;
            margin-top: 20px; letter-spacing: 1.2px;
            font-weight: 600; text-align: center;
        }

        @media (max-width: 520px) {
            .panel-container { padding: 32px; margin: 16px; }
            .logo-admin { height: 68px; }
        }
    </style>
</head>
<body>
    <div class="panel-container <?php echo $error ? 'error-shake' : ''; ?>">
        <div class="panel-header">
            <img src="Asesorias.png" alt="Logo Asesorías Cre-C" class="logo-admin">
            <p>Acceso Administrativo</p>
        </div>
        <form method="POST" action="">
            <div class="input-group">
                <label>Identificador de Usuario</label>
                <input type="text" name="user" required placeholder="Ingresar ID" autocomplete="off">
            </div>
            <div class="input-group">
                <label>Clave de Seguridad</label>
                <input type="password" name="pass" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">Acceder al Portal</button>
            <div class="error-message<?php echo $error ? ' error-visible' : ''; ?>">Credenciales inválidas. Por favor verifica usuario y contraseña.</div>
        </form>
        <div class="live-clock" id="relojDigital">Sincronizando...</div>
    </div>
    <script>
        function actualizarReloj() {
            const ahora = new Date();
            const tiempo = ahora.toLocaleTimeString('es-MX', { hour: '2-digit', minute:'2-digit', second:'2-digit' });
            document.getElementById('relojDigital').innerHTML = `<i class="fa-regular fa-clock"></i> TIEMPO: ${tiempo}`;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();
    </script>
</body>
</html>