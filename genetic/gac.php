<?php

class Gac {

    // Diccionario de caracteres permitidos
    const LETTERS = "abcdefghijklmnopqrstuvwxyz ";

    private $target;
    private $length;
    private $population = [];
    private $generation = 0;

    // Variables de configuración (ya no son constantes)
    private $tam_poblacion;
    private $tasa_mutacion;
    private $elitismo;

    /**
     * Constructor con parámetros configurables
     */
    public function __construct($target, $tam_poblacion = 50, $tasa_mutacion = 0.05, $elitismo = 5) {
        // Convertimos a minúsculas y filtramos caracteres no válidos para evitar bucles infinitos
        $this->target = $this->sanitize_target($target);
        $this->length = strlen($this->target);
        
        $this->tam_poblacion = (int)$tam_poblacion;
        $this->tasa_mutacion = (float)$tasa_mutacion;
        $this->elitismo = (int)$elitismo;
    }

    private function sanitize_target($text) {
        $text = strtolower($text);
        // Eliminar cualquier caracter que no esté en LETTERS
        $clean = "";
        for($i=0; $i<strlen($text); $i++) {
            if (strpos(self::LETTERS, $text[$i]) !== false) {
                $clean .= $text[$i];
            }
        }
        return $clean ?: "error";
    }

    // Función de aptitud (Fitness): Qué tan cerca está del objetivo (0 a 1)
    private function calculate_fitness($dna) {
        $matches = 0;
        for ($i = 0; $i < $this->length; $i++) {
            if ($dna[$i] === $this->target[$i]) {
                $matches++;
            }
        }
        return $matches / $this->length;
    }

    // Generar individuo aleatorio
    private function random_individual() {
        $text = "";
        $max_index = strlen(self::LETTERS) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            $text .= self::LETTERS[mt_rand(0, $max_index)];
        }
        return $text;
    }

    // Crear población inicial
    private function init_population() {
        for ($i = 0; $i < $this->tam_poblacion; $i++) {
            $dna = $this->random_individual();
            $this->population[] = [
                'dna' => $dna,
                'fitness' => $this->calculate_fitness($dna)
            ];
        }
    }

    // Cruza (Crossover) de dos padres
    private function crossover($parent1, $parent2) {
        $child = "";
        // Punto de corte aleatorio para mezclar genes
        $midpoint = mt_rand(0, $this->length - 1);

        for ($i = 0; $i < $this->length; $i++) {
            if ($i > $midpoint) {
                $child .= $parent1[$i];
            } else {
                $child .= $parent2[$i];
            }
        }
        return $child;
    }

    // Mutación
    private function mutate($dna) {
        $max_index = strlen(self::LETTERS) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            // Probabilidad de mutar cada letra individualmente
            if ((mt_rand(0, 1000) / 1000) < $this->tasa_mutacion) {
                $dna[$i] = self::LETTERS[mt_rand(0, $max_index)];
            }
        }
        return $dna;
    }

    // Ejecución principal
    public function execute() {
        if ($this->target === "error") {
            echo "Error: El texto objetivo contiene caracteres inválidos o está vacío.";
            return;
        }

        $this->init_population();
        $found = false;

        // Bucle infinito hasta encontrar la solución (reemplaza la recursividad)
        while (!$found) {
            $this->generation++;

            // 1. Ordenar por fitness (Mejores primero)
            usort($this->population, function($a, $b) {
                return $b['fitness'] <=> $a['fitness'];
            });

            // Mejor individuo actual
            $best = $this->population[0];

            // Imprimir progreso (Solo el mejor de la generación para no saturar)
            // Usamos str_pad para que se vea bonito en consola o navegador
            echo "Gen: " . str_pad($this->generation, 4) . " | Mejor: " . $best['dna'] . " | Fitness: " . number_format($best['fitness'] * 100, 2) . "%<br>";
            flush(); // Forzar salida al navegador

            // ¿Encontramos la solución?
            if ($best['fitness'] >= 1) {
                echo "<h1>¡ENCONTRADO!</h1>";
                echo "Palabra: <strong>" . $best['dna'] . "</strong> en " . $this->generation . " generaciones.";
                $found = true;
                break;
            }

            // Seguridad para evitar bucles infinitos en servidor
            if ($this->generation > 5000) {
                echo "<h3>Límite de generaciones alcanzado (5000). Deteniendo.</h3>";
                break;
            }

            // 2. Nueva Población
            $new_population = [];

            // A. Elitismo: Los mejores pasan directo
            // Aseguramos no exceder la población total si elitismo es muy alto
            $elitismo_real = min($this->elitismo, $this->tam_poblacion);
            
            for ($i = 0; $i < $elitismo_real; $i++) {
                $new_population[] = $this->population[$i];
            }

            // B. Reproducción para llenar el resto
            $remaining_slots = $this->tam_poblacion - $elitismo_real;
            
            for ($i = 0; $i < $remaining_slots; $i++) {
                // Selección simple: Tomar dos padres aleatorios de la mitad superior (mejores genes)
                $parent1 = $this->population[mt_rand(0, floor($this->tam_poblacion / 2))]['dna'];
                $parent2 = $this->population[mt_rand(0, floor($this->tam_poblacion / 2))]['dna'];

                $child_dna = $this->crossover($parent1, $parent2);
                $child_dna = $this->mutate($child_dna);

                $new_population[] = [
                    'dna' => $child_dna,
                    'fitness' => $this->calculate_fitness($child_dna)
                ];
            }

            $this->population = $new_population;
        }
    }
}
?>
