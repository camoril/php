<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Reporte de Llamadas</title>
<link rel="stylesheet" type="text/css" href="view.css" media="all">
<script type="text/javascript" src="view.js"></script>
</head>
<body id="main_body" >
	<img id="top" src="top.png" alt="">
	<div id="form_container">
		<h1><a href="./">Reporte de Llamadas</a></h1>
                <?php
/*
 * llamadas.php
 * 
 * Copyright 2013 Ernesto Pineda B. <camoril@gmail.com>
 */
 
// MySQL - (host,username,password,dbname,port,socket)
$con = mysqli_connect('localhost','tarifaswtc','4utedasyq','zadmin_tarifas');

// Validar
if (mysqli_connect_errno($con))
  {
	  echo "No se puede conectar a la Base: " . mysqli_connect_error().PHP_EOL;
  }
// Recibir Variables POST  
$ext = $_POST['extension'];
$dial = $_POST['marcado'];
$type = $_POST['tipo'];
$mes = $_POST['mes'];

echo '<table border = "1">'.PHP_EOL;
// Comienza el ISWITCH Para tipo de reporte
switch ($type) 
{
	case 1:
		echo "<tr><td>Fecha</td><td>Extension</td><td>Numero</td><td>Tiempo</td></tr>".PHP_EOL;
		// Consulta Tipo 1 DETALLE EXTENSION
		$reporte = mysqli_query($con,
		"SELECT `".$mes."`.`CallStart` as 'Fecha',
		`".$mes."`.`Caller` as 'Extension',
		`".$mes."`.`CalledNumber` as 'Numero',
		IFNULL (`".$mes."`.`ConnectedTime`,'Tiempo') as 'Tiempo'
		FROM ".$mes." where (`CalledNumber` like '".$dial."%') and (`Caller` = '".$ext."') and (`".$mes."`.`Caller` IS NOT NULL)
		ORDER BY `".$mes."`.`CallStart` ASC");
		while($fila = mysqli_fetch_array($reporte))
		{
			echo "<tr><td>".$fila['Fecha']."</td><td>".$fila['Extension']."</td><td>".$fila['Numero']."</td><td>".$fila['Tiempo']."</td></tr>".PHP_EOL;
		}
		break;
	case 2:
		echo "<tr><td>Extension</td><td>Tiempo</td></tr>".PHP_EOL;
                // Consulta Tipo 2 TOTAL EXTENSION (Pendiente)
		$reporte = mysqli_query($con,
		"SELECT IFNULL (`".$mes."`.`Caller`,'Total') as 'Extension',
		SEC_TO_TIME( SUM( TIME_TO_SEC( `".$mes."`.`ConnectedTime` ) ) ) as 'Total'
		FROM ".$mes." where (`CalledNumber` like '".$dial."%') and (`Caller` = '".$ext."')
		GROUP BY `".$mes."`.`Caller` ASC WITH ROLLUP");
		while($fila = mysqli_fetch_array($reporte))
		{
			echo "<tr><td>".$fila['Extension']."</td><td>".$fila['Total']."</td></tr>".PHP_EOL;
		}
		break;
        case 3;
            //Exportar a CSV - Pendiente
            // Descargar archivo no presentar
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=detalle.csv');
            
            // Crear archivo apuntando al stream
            $output = fopen('php://output', 'w');
            
            // nombre de Columnas y borrar datos HTML
            ob_end_clean();
            fputcsv($output, array('Fecha', 'Extension', 'Numero','Tiempo'));
            
            // Datos
            $reporte = mysqli_query($con,
		"SELECT `".$mes."`.`CallStart` as 'Fecha',
		`".$mes."`.`Caller` as 'Extension',
		`".$mes."`.`CalledNumber` as 'Numero',
		IFNULL (`".$mes."`.`ConnectedTime`,'Tiempo') as 'Tiempo'
		FROM ".$mes." where (`CalledNumber` like '".$dial."%') and (`Caller` = '".$ext."') and (`".$mes."`.`Caller` IS NOT NULL)
		ORDER BY `".$mes."`.`CallStart` ASC");
		while($fila = mysqli_fetch_array($reporte))
		{
                    echo $fila['Fecha'].",".$fila['Extension'].",".$fila['Numero'].",".$fila['Tiempo']."".PHP_EOL;
		}
                // Borrar datos HTML
                exit();
            break;
}
echo "</table>".PHP_EOL;
//echo "<pre>";
//print_r($reporte);
//echo "</pre>";
// Cerrar Base de Datos *** MUY IMPORTANTE
mysqli_close($con);
echo '<p><a href="./index.php">Back</a></p>'.PHP_EOL;
?>
		<div id="footer">
			Generado por <a href="http://hypercube.com.mx/">Ernesto PB.</a>
		</div>
	</div>
	<img id="bottom" src="bottom.png" alt="">
</body>
</html>
