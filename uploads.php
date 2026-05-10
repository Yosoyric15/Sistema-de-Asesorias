// ... (dentro de tu bloque IF POST) ...

$comprobante_nombre = NULL;
if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] == 0) {
    $ruta_destino = "uploads/";
    $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
    // Renombramos el archivo con la clave del alumno para que no se repitan
    $comprobante_nombre = "PAGO_" . $clave . "." . $extension;
    
    move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_destino . $comprobante_nombre);
}

// Ahora agregamos el campo al INSERT de alumnos
$sqlalumnos = "INSERT INTO alumnos (claveAlumno, apPaterno, apMaterno, nombres, telefonoAlumno, telefonoPadres, telefonoEmergencia, facultad, preparatoria, estatus, comprobante_pago) 
               VALUES ('$clave', '$apPaterno', '$apMaterno', '$nombres', '$telA', '$telP', '$telE', '$facultad', '$prepa', 1, '$comprobante_nombre')";