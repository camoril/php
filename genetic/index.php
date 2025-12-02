<CENTER>
    <style>
        body { font-family: monospace; background: #f0f0f0; padding: 20px; }
        form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block; margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        input[type="submit"]:hover { background: #45a049; }
        .results { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block; width: 100%; max-width: 600px; text-align: left; max-height: 500px; overflow-y: auto; }
    </style>

    <h2>Algoritmo Genético Interactivo</h2>

    <?php
    // Valores por defecto
    $target = isset($_POST['target']) ? $_POST['target'] : "hypercubecom";
    $pop_size = isset($_POST['pop_size']) ? (int)$_POST['pop_size'] : 50;
    $mutation_rate = isset($_POST['mutation_rate']) ? (float)$_POST['mutation_rate'] : 0.05;
    $elitism = isset($_POST['elitism']) ? (int)$_POST['elitism'] : 5;
    ?>

    <form method="POST">
        <label>Texto Objetivo (a-z y espacios):</label>
        <input type="text" name="target" value="<?php echo htmlspecialchars($target); ?>" required pattern="[a-zA-Z ]+">
        
        <label>Tamaño de Población (10-500):</label>
        <input type="number" name="pop_size" value="<?php echo $pop_size; ?>" min="10" max="500" required>
        
        <label>Tasa de Mutación (0.01 - 1.0):</label>
        <input type="text" name="mutation_rate" value="<?php echo $mutation_rate; ?>" required>
        
        <label>Elitismo (Individuos que sobreviven):</label>
        <input type="number" name="elitism" value="<?php echo $elitism; ?>" min="0" max="500" required>
        
        <input type="submit" name="run" value="Evolucionar">
    </form>

    <br>

    <?php
    if (isset($_POST['run'])) {
        echo '<div class="results">';
        echo "<h3>Resultados:</h3>";
        
        // Aumentar tiempo de ejecución por si la palabra es muy larga
        set_time_limit(300); 

        include_once("gac.php");
        
        // Instanciar y ejecutar con parámetros del usuario
        $ga = new Gac($target, $pop_size, $mutation_rate, $elitism);
        $ga->execute();
        
        echo '</div>';
    }
    ?>
</CENTER>
