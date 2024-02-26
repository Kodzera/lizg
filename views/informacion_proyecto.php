<?php
// Incluir el archivo de configuración de la base de datos
include "../config/database.php";

// Incluir el encabezado
include "../config/partials/header.php";

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $idProyecto = $_GET['id'];

    $sql_proyecto = "SELECT * FROM proyecto WHERE id = ?";
    $stmt_proyecto = $conn->prepare($sql_proyecto);
    $stmt_proyecto->bind_param("i", $idProyecto);
    $stmt_proyecto->execute();
    $result_proyecto = $stmt_proyecto->get_result();

    if ($result_proyecto->num_rows > 0) {
        $proyecto = $result_proyecto->fetch_assoc();
?>

        <body class="d-flex flex-column h-100">
            <img src="<?php echo $url ?>images/encabezadoactual.png" width="700">
            <div class="container d-flex justify-content-center align-item-center mb-5">
                <div class="border border-danger p-3 mb-2 rounded mt-2">
                    <h1>Información del Proyecto</h1>
                    <p><strong>Nombre del Proyecto:</strong> <?php echo $proyecto['nombre']; ?></p>
                    <p><strong>Descripción del Proyecto:</strong> <?php echo $proyecto['descripcion']; ?></p>
                </div>
            </div>
            <div class="container d-flex justify-content-evenly">
                <a href="home.php" class="btn btn-warning">Volver</a>
                <!-- Enlace para dirigir al usuario a la página de edición con el ID del proyecto -->
                <a href="editar_proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-primary">Editar Información</a>
                <a href="materiales.php?id=<?php echo $idProyecto; ?>" class="btn btn-dark">Ver Lista de Materiales</a>
                <a href="#" class="btn btn-info">Agregar Material que falta</a>
                <a href="generar_pdf.php?id=<?php echo $idProyecto; ?>&nombre_proyecto=<?php echo urlencode($proyecto['nombre']); ?>" class="btn btn-danger" target="_blank">Generar PDF</a>

                <form action="generar_exel.php" method="post" style="display:inline;">
                    <input type="hidden" name="id_proyecto" value="<?php echo $idProyecto; ?>">
                    <button type="submit" class="btn btn-success">Generar CSV</button>
                </form>


            </div>
            <form action="guardar_cantidad.php" method="POST">
                <input type="hidden" name="idProyecto" value="<?php echo $idProyecto; ?>">
                <table class="table table-sm table-striped table-hover mt-4 container">
                    <thead class="table-dark">
                        <tr>
                            <th>Codigo</th>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta SQL para obtener los materiales asociados al proyecto actual
                        $sql_materiales = "SELECT m.codigo, m.nombre, d.cantidad AS cantidad_detalle 
                        FROM materiales m 
                        INNER JOIN materialesproyecto mp ON m.codigo = mp.codigoMaterial 
                        LEFT JOIN detalle d ON m.codigo = d.codigoMaterial AND d.idProyecto = ?
                        WHERE mp.idProyecto = ?";

                        $stmt_materiales = $conn->prepare($sql_materiales);
                        $stmt_materiales->bind_param("ii", $idProyecto, $idProyecto);
                        $stmt_materiales->execute();
                        $result_materiales = $stmt_materiales->get_result();


                        // Verificar si se encontraron materiales asociados al proyecto
                        if ($result_materiales->num_rows > 0) {
                            // Mostrar los materiales en la tabla
                            while ($row = $result_materiales->fetch_assoc()) {
                                $codigo = stripslashes($row['codigo']);
                                $nombre = stripslashes($row['nombre']);
                                $cantidad_detalle = $row['cantidad_detalle'];
                        ?>
                                <tr>
                                    <td><?php echo $codigo; ?></td>
                                    <td><?php echo $nombre; ?></td>
                                    <td>
                                        <!-- Campo de entrada para la cantidad -->
                                        <input type='number' name='cantidad_<?php echo $codigo; ?>' class='form-control' value='<?php echo $cantidad_detalle; ?>' required>
                                    </td>
                                    <td>
                                        <a href="eliminar_material.php?codigo=<?php echo $codigo; ?>&idProyecto=<?php echo $idProyecto; ?>" class="btn btn-danger">Eliminar</a>
                                        <a href="#" class="btn btn-warning">Editar</a>
                                    </td>

                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='3'>No hay materiales asociados a este proyecto.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary my-5">Guardar</button>
                </div>
            </form>

        </body>
<?php
    } else {
        echo "<p>No se encontró el proyecto.</p>";
    }
} else {
    echo "<p>No se proporcionó un ID de proyecto válido.</p>";
}
?>