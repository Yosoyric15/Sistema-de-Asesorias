<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Gastos</title>

<style>
body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #ffffff;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('la-ceremonia-se-llevo-a-cabo-en-el-vestibulo-del-emblematico-edificio.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(4px);
            z-index: -1;
            transform: scale(1.1);
        }

h2{
    font-size: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
    margin-bottom: 20px;
}

.contenedor{
    width:600px;
    margin:auto;
    background:black;
    padding:20px;
    border-radius:10px;
}

input[type="number"]{
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    box-sizing: border-box;
    color: #222;
}


#boton1{
    display: inline-block;
    padding: 15px 30px;
    background-color: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 1.2em;
    transition: all 0.3s ease; 
    position: absolute;
    top: 30%; 
    left: 45%;
    transform: translate(-50%, -50%);
    opacity: 0;
    animation: slideUp 0.8s ease-out 0.6s forwards;
}

</style>
</head>

<div class="contenedor">

<h2>Egreso y Pago</h2>

<label>Tipo de gasto</label><br>

<select id="tipo">
<option value="">Seleccionar</option>
<option value="Renta">Renta</option>
<option value="Manuales">Manuales</option>
</select>

<br>

<label>Monto</label><br>
<input type="number" id="monto">

<br>

<label>Fecha</label><br>
<input type="date" id="fecha">

<br><br>

<button id="boton1" onclick="grabar()">Grabar</button>
<button id="boton2" onclick="window.location='Consulta.html'">Consultar</button>

</div>
</div>
<script>

function grabar(){

let tipo=document.getElementById("tipo").value;
let monto=document.getElementById("monto").value;
let fecha=document.getElementById("fecha").value;

if(tipo=="" || monto=="" || fecha==""){
alert("Completa todos los campos");
return;
}

let gastos=JSON.parse(localStorage.getItem("gastos")) || [];

gastos.push({
fecha:fecha,
tipo:tipo,
monto:parseFloat(monto)
});

localStorage.setItem("gastos",JSON.stringify(gastos));

alert("Gasto guardado");

}

</script>

</body>
</html>