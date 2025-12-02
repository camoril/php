<CENTER>
    <?php
    set_time_limit(0);
    ini_set("memory_limit", "25M");

    function cmp($a, $b) {
        if ($a["rate"] == $b["rate"]) {
            return 0;
        }
        return ($a["rate"] > $b["rate"]) ? -1 : 1;
    }

    include("gac.php");

    $text = "hypercubecom"; //valor buscado
    $my_ga = new Gac($text);


    $first_gene_code = $my_ga->execute();
    ?>
</CENTER>
