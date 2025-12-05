<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Motor Tarificador v0.3</title>
        <link rel="stylesheet" type="text/css" href="view.css" media="all">
        <script type="text/javascript" src="view.js"></script>
        <!--
        v0.2 -  Script de Validacion Extension y Tipo (completo) 
                ** falta verificar el tipo de digito en la casilla (solo numeros en ambos)
        v0.3 - Generar opcion de Descarga en CSV el detalle de la extension seleccionada
        -->
        <script>
            function validaForm()
            {
                var x = document.forms["captura"]["extension"].value;
                if (x == null || x == "")
                {
                    alert("Debes conocer la extension a reportar. SOLO NUMEROS");
                    return false;
                }
                var radios = document.getElementsByName("tipo");
                var formValid = false;
                var i = 0;
                while (!formValid && i < radios.length) {
                    if (radios[i].checked)
                        formValid = true;
                    i++;
                }
                if (!formValid)
                    alert("Debes seleccionar Tipo de Reporte");
                return formValid;
            }
        </script>
    </head>
    <body id="main_body" >
        <img id="top" src="top.png" alt="">
        <div id="form_container">
            <h1><a href="./">Reporte de Llamadas</a></h1>
            <form name="captura" id="captura_datos" class="appnitro"  method="POST" action="llamadas.php" onsubmit="return validaForm()">
                <div class="form_description">
                    <center><h2>TARIFAS</h2></center>
                    <center><p><h3>Motor para reportar llamadas - SMDR Avaya</h3></p></center>
                </div>
                <ul >
                    <li id="li_1" >
                        <label class="description" for="extension">Extension:</label>
                        <div>
                            <input id="extension" name="extension" class="element text small" type="text" maxlength="5" value=""/>
                        </div>
                    </li>
                    <li id="li_2" >
                        <label class="description" for="marcado">Marcado:</label>
                        <div>
                            <input id="marcado" name="marcado" class="element text small" type="text" maxlength="15" value=""/> 
                        </div>
                    </li>
                    <li id="li_3" >
                        <label class="description" for="mes">Mes:</label>
                        <div>
                            <select name="mes">
                                <option value="septiembre_WTC">Septiembre 2013</option>
                                <option value="octubre_WTC">Octubre 2013</option>
                                <option value="noviembre_WTC">Noviembre 2013</option>
                                <option value="diciembre_WTC">Diciembre 2013</option>
                            </select>
                        </div>
                    </li>
                    <li id="li_4" >
                        <label class="description" for="tipo">Tipo de Reporte:</label>
                        <span>
                            <input id="tipo_1" name="tipo" class="element radio" type="radio" value="1" />
                            <label class="choice" for="tipo_1">Detalle Llamadas</label>
                            <input id="tipo_3" name="tipo" class="element radio" type="radio" value="3" />
                            <label class="choice" for="tipo_3">Detalle en CSV</label>
                            <input id="tipo_2" name="tipo" class="element radio" type="radio" value="2" />
                            <label class="choice" for="tipo_2">Total de LLamadas</label>
                        </span>
                    </li>
                    <li class="buttons">		    
                        <input id="saveForm" class="button_text" type="submit" name="submit" value="Generar" />
                    </li>
                </ul>
            </form>
            <div id="footer">
                Generado por <a href="http://hypercube.com.mx">Ernesto PB.</a>
            </div>
        </div>
        <img id="bottom" src="bottom.png" alt="">
    </body>
</html>
