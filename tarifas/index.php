<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Tarificador v1.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function validaForm(event) {
            var ext = document.getElementById("extension").value;
            if (!ext || isNaN(ext)) {
                alert("Debes ingresar una extensión válida (SOLO NÚMEROS).");
                event.preventDefault();
                return false;
            }
            
            var radios = document.getElementsByName("tipo");
            var formValid = false;
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) formValid = true;
            }
            
            if (!formValid) {
                alert("Debes seleccionar un Tipo de Reporte.");
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10">

    <div class="w-full max-w-md bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="bg-blue-600 p-6 text-center">
            <h1 class="text-white text-2xl font-bold">Reporte de Llamadas</h1>
            <p class="text-blue-200 text-sm mt-1">Motor Tarificador - SMDR Avaya</p>
        </div>

        <form name="captura" method="POST" action="llamadas.php" onsubmit="validaForm(event)" class="p-8 space-y-6">
            
            <!-- Extensión -->
            <div>
                <label for="extension" class="block text-sm font-medium text-gray-700 mb-1">Extensión</label>
                <input type="text" id="extension" name="extension" maxlength="5" placeholder="Ej: 1234" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <p class="text-xs text-gray-500 mt-1">Solo números (Máx 5 dígitos)</p>
            </div>

            <!-- Marcado -->
            <div>
                <label for="marcado" class="block text-sm font-medium text-gray-700 mb-1">Número Marcado (Opcional)</label>
                <input type="text" id="marcado" name="marcado" maxlength="15" placeholder="Ej: 55..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>

            <!-- Mes -->
            <div>
                <label for="mes" class="block text-sm font-medium text-gray-700 mb-1">Periodo</label>
                <select name="mes" id="mes" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                    <option value="septiembre_WTC">Septiembre 2013</option>
                    <option value="octubre_WTC">Octubre 2013</option>
                    <option value="noviembre_WTC">Noviembre 2013</option>
                    <option value="diciembre_WTC">Diciembre 2013</option>
                </select>
            </div>

            <!-- Tipo de Reporte -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Reporte</label>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input id="tipo_1" name="tipo" type="radio" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                        <label for="tipo_1" class="ml-3 block text-sm text-gray-700">Detalle de Llamadas</label>
                    </div>
                    <div class="flex items-center">
                        <input id="tipo_2" name="tipo" type="radio" value="2" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                        <label for="tipo_2" class="ml-3 block text-sm text-gray-700">Total de Tiempo</label>
                    </div>
                    <div class="flex items-center">
                        <input id="tipo_3" name="tipo" type="radio" value="3" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                        <label for="tipo_3" class="ml-3 block text-sm text-gray-700">Descargar CSV</label>
                    </div>
                </div>
            </div>

            <!-- Botón -->
            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    Generar Reporte
                </button>
            </div>

        </form>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">Generado por <a href="#" class="text-blue-600 hover:underline">Ernesto PB</a></p>
        </div>
    </div>

</body>
</html>
