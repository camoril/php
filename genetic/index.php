<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algoritmo Genético Interactivo</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; display: flex; gap: 20px; flex-wrap: wrap; }
        .panel { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); flex: 1; min-width: 300px; }
        .sidebar { flex: 0 0 350px; background: #e9ecef; }
        
        h1, h2, h3 { color: #2c3e50; margin-top: 0; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 16px; }
        
        .btn-group { display: flex; gap: 10px; }
        input[type="submit"], .btn-reset { padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; flex: 1; text-align: center; text-decoration: none; transition: background 0.3s; }
        input[type="submit"] { background: #3498db; color: white; }
        input[type="submit"]:hover { background: #2980b9; }
        .btn-reset { background: #95a5a6; color: white; display: inline-block; }
        .btn-reset:hover { background: #7f8c8d; }

        .results { margin-top: 20px; max-height: 600px; overflow-y: auto; background: #2c3e50; color: #ecf0f1; padding: 20px; border-radius: 10px; font-family: 'Courier New', monospace; }
        .success { color: #2ecc71; font-weight: bold; font-size: 1.2em; padding: 10px; border: 2px solid #2ecc71; border-radius: 5px; margin-top: 10px; text-align: center; }
        
        .info-item { margin-bottom: 15px; }
        .info-item strong { display: block; color: #2c3e50; margin-bottom: 5px; }
        .info-item p { margin: 0; font-size: 0.9em; line-height: 1.5; color: #666; }
    </style>
</head>
<body>

    <?php
    // Valores por defecto
    $target = isset($_POST['target']) ? $_POST['target'] : "hypercubecom";
    $pop_size = isset($_POST['pop_size']) ? (int)$_POST['pop_size'] : 50;
    $mutation_rate = isset($_POST['mutation_rate']) ? (float)$_POST['mutation_rate'] : 0.05;
    $elitism = isset($_POST['elitism']) ? (int)$_POST['elitism'] : 5;
    ?>

<div class="container">
    <!-- Panel Izquierdo: Formulario -->
    <div class="panel">
        <h2>🧬 Configuración</h2>
        <form method="POST">
            <label>Texto Objetivo (a-z y espacios):</label>
            <input type="text" name="target" value="<?php echo htmlspecialchars($target); ?>" required pattern="[a-zA-Z ]+" placeholder="ej. hola mundo">
            
            <label>Tamaño de Población (10-500):</label>
            <input type="number" name="pop_size" value="<?php echo $pop_size; ?>" min="10" max="500" required>
            
            <label>Tasa de Mutación (0.01 - 1.0):</label>
            <input type="text" name="mutation_rate" value="<?php echo $mutation_rate; ?>" required>
            
            <label>Elitismo (Individuos que sobreviven):</label>
            <input type="number" name="elitism" value="<?php echo $elitism; ?>" min="0" max="500" required>
            
            <div class="btn-group">
                <input type="submit" name="run" value="🚀 Evolucionar">
                <a href="index.php" class="btn-reset">🔄 Resetear</a>
            </div>
        </form>

        <?php if (isset($_POST['run'])): ?>
            <div class="results">
                <h3>Resultados de la Evolución:</h3>
                <?php
                set_time_limit(300); 
                include_once("gac.php");
                $ga = new Gac($target, $pop_size, $mutation_rate, $elitism);
                $ga->execute();
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel Derecho: Instrucciones -->
    <div class="panel sidebar">
        <h2>📘 Guía Rápida</h2>
        
        <div class="info-item">
            <strong>Texto Objetivo</strong>
            <p>La frase que el algoritmo intentará "adivinar" mediante evolución. Solo usa letras (a-z) y espacios.</p>
        </div>

        <div class="info-item">
            <strong>Tamaño de Población</strong>
            <p>Cuántos "individuos" compiten en cada generación. Más población = más diversidad, pero más lento.</p>
        </div>

        <div class="info-item">
            <strong>Tasa de Mutación</strong>
            <p>Probabilidad (0.0 a 1.0) de que una letra cambie al azar. 
            <br><em>Baja (0.01):</em> Evolución lenta y estable.
            <br><em>Alta (0.5):</em> Caótico, difícil de converger.</p>
        </div>

        <div class="info-item">
            <strong>Elitismo</strong>
            <p>Número de los mejores individuos que pasan intactos a la siguiente generación. Garantiza que la mejor solución encontrada no se pierda.</p>
        </div>

        <div class="info-item">
            <strong>¿Cómo funciona?</strong>
            <p>El sistema genera frases al azar, selecciona las que más se parecen a tu objetivo, las mezcla (cruza) y añade pequeños cambios (mutación) repetidamente hasta lograr la coincidencia perfecta.</p>
        </div>
    </div>
</div>

</body>
</html>
