<?php

class Gac {

    const TAM_POBLACION = 20;
    const FREQ_MUTACION = 10;
    const TAM_MUTACION = 100;
    const SOBREVIVIENTES = 8;
    const FREQ_EXITO = 1;
    const LETTERS = "abcdefghijklmnopqrstuvwxyz";
    const OFFSPRING = 2;

    public function Gac($text) {
        $this->text = $text;
        $this->lenght = strlen($this->text);
        $this->population = 0;
        $this->max_rate = 0;
        $this->avarage_suc_rate = 0;
        $this->best = array();
        $this->passed = FALSE;
        $this->generations = 0;
    }

    private function check_success($data) {
        $rate = 0;

        for ($i = 0; $i < $this->lenght; $i++) {
            $original_letter = substr($this->text, $i, 1);
            $text_letter = substr($data, $i, 1);
            if ($original_letter == $text_letter) {
                $rate++;
            }
        }

        $rate = $rate / $this->lenght;

        return $rate;
    }

    private function first_population($gene_code) {
        $words = array();
        for ($i = 0; $i < self::TAM_POBLACION; $i++) {
            $this->population++;
            $mutation = TRUE; // Primer puplación, MUTAR TODO!

            $new_ind = $this->generate($gene_code, $mutation);
            $words[$i]["word"] = $new_ind["word"];
            $words[$i]["rate"] = $new_ind["rate"];
            $words[$i]["gene_code"] = $new_ind["gene_code"];

            echo $new_ind["word"] . " - " . $new_ind["rate"] . "<br>\n";
        }
        echo "fin primera generación <br>\n";
        echo "----------------------------<br>\n";
        array_unique($words);
        usort($words, "cmp");
        return $words;
    }

    private function kill($genes) {
        if (count($genes) > self::SOBREVIVIENTES) {
            $diff = count($genes) - self::SOBREVIVIENTES;

            for ($i = 0; $i < $diff; $i++) {
                array_pop($genes);
            }

            print "ASESINADOS $diff INDIVIDUOS<br>\n";
        }

        return $genes;
    }

    private function populate($genes) {
        $this->generations++;
        $new_genes = array();
        echo "------------ COMIENZA LA GENERACION: " . $this->generations . " ------------<br>\n";
        //cruzar todo
        usort($this->best, "cmp");
        for ($j = 0; $j < self::OFFSPRING; $j++) {
            for ($i = 0; $i < count($genes); $i++) {
                $this->population++;
                $best = FALSE;

                if ($genes[$i]["rate"] >= $this->max_rate) {
                    $new_gene = $genes[$i]["gene_code"];
                    $best = TRUE;
                    echo "PAUSA<br>";
                } else {
                    if ($genes[$i]["gene_code"] !== $genes[$i + 1]["gene_code"] && $genes[$i + 1]["gene_code"]) {
                        $new_gene = $this->cross($genes[$i]["gene_code"], $genes[$i + 1]["gene_code"]);
                    } else if ($genes[$i]["gene_code"] !== $genes[$i + 2]["gene_code"] && $genes[$i + 2]["gene_code"]) {
                        $new_gene = $this->cross($genes[$i]["gene_code"], $genes[$i + 2]["gene_code"]);
                    } else {
                        $new_gene = $genes[$i]["gene_code"];
                        echo "<span style='color:blue'>NINGUNA CRUZA!</span><br>\n";
                    }
                }

                if ($new_gene == $genes[$i]["gene_code"] && $i !== 0) {
                    $new_gene = $this->mutate($new_gene);
                    echo "<span style='color:red'>MUTACION INESPERADA!</span><br>\n";
                }

                $mutation = FALSE;

                if ($this->population % self::TAM_MUTACION == 0) {
                    //mutar el gen recien creado si el tamaño de la MUTACION! se alcanza
                    echo "MUTACION!<br>\n";
                    $mutation = TRUE;
                }

                $new_ind = $this->generate($new_gene, $mutation);
                $new_genes[$i]["word"] = $new_ind["word"];
                $new_genes[$i]["rate"] = $new_ind["rate"];
                $new_genes[$i]["gene_code"] = $new_ind["gene_code"];

                if ($new_ind["rate"] >= $this->max_rate && !in_array($new_ind, $this->best)) {
                    $this->max_rate = $new_ind["rate"];
                    $this->best[] = $new_ind;
                    usort($this->best, "cmp");
                }

                echo $new_ind["word"] . " - " . $new_ind["rate"] . "<br>\n";
            }
        }


        array_unique($new_genes);
        usort($new_genes, "cmp");
        return $new_genes;
    }

    private function cross($gene1, $gene2) {
        $new_gene = array();
        for ($i = 0; $i < $this->lenght; $i++) {
            $rand = rand(0, 1);
            if ($rand) {
                $new_gene[$i] = $gene1[$i];
            } else {
                $new_gene[$i] = $gene2[$i];
            }
        }

        if ($new_gene !== $gene1 && $new_gene !== $gene2) {
            echo "<span style='color:green'>CRUZA EXITOSA! </span><br>";
        }

        return $new_gene;

        // unset($cross_letters);
        // 		$cross_letters = array();
        // 		$cross_letter_num = rand(0,$this->lenght);
        // 
        // 		for($i=0;$i<$cross_letter_num;$i++)
        // 		{
        // 			$cross_letters[] = rand(0,$this->lenght-1);
        // 		}
        // 
        // 		array_unique($cross_letters);
        // 		foreach($cross_letters as $letter_num)
        // 		{
        // 			$gene1[$letter_num] = $gene2[$letter_num];
        // 		}
        // 		return $gene1;
    }

    public function execute() {
        // the first gene code
        $gene_code = $this->generate_gene_code();
        $first_population = $this->first_population($gene_code);
        $result = $this->evolution($first_population);

        return $result;
    }

    private function evolution($genes_data) {
        // return $genes_data;
        if ($genes_data[0]["rate"] >= self::FREQ_EXITO) {
            echo $genes_data[0]["word"] . " encontrado despues de " . $this->population . " intento(s)";
            return $genes_data;
        } else {
            if (count($this->best) > self::SOBREVIVIENTES || $this->passed) {
                usort($this->best, "cmp");
                $this->best = $this->kill($this->best);
                $genes = $this->best;
                $this->passed = TRUE;
            } else {
                $genes = $genes_data;
            }
            return $this->evolution($this->populate($genes));
        }
    }

    private function generate_gene_code() {
        $code = array();
        for ($i = 0; $i < $this->lenght; $i++) {
            $code[] = rand(0, strlen(self::LETTERS));
        }
        return $code;
    }

    private function generate($gene_code, $mutation) {
        // genera nuevos individuos de acuerdo al codigo genetico
        if ($mutation) {
            $gene_code = $this->mutate($gene_code);
        }
        
        //Definir nueva palabra para cruza
        $new_word = "";

        for ($i = 0; $i < $this->lenght; $i++) {
            $letter = substr(self::LETTERS, $gene_code[$i], 1);
            $new_word .= $letter;
        }

        $new_ind = array();

        $new_ind["gene_code"] = $gene_code;
        $new_ind["word"] = $new_word;
        $new_ind["rate"] = $this->check_success($new_word);

        return $new_ind;
    }

    private function mutate($gene_code) {
        // mutar datos originales y regresarlos
        $mutate_letter_num = floor(self::FREQ_MUTACION * $this->lenght / 100);
        $mutate_letters = array();

        for ($i = 0; $i < $mutate_letter_num; $i++) {
            $which = rand(0, $this->lenght - 1);
            $mutate_letters[] = $which;
        }

        array_unique($mutate_letters);

        foreach ($mutate_letters as $letter_num) {
            $gene_code[$letter_num] = rand(0, strlen(self::LETTERS));
        }
        return $gene_code;
    }

}
?>
