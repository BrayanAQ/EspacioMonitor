<?php
echo "<h1>🔍 Diagnóstico Completo de OCI8</h1>";
echo "<style>body {font-family: Arial; margin: 20px;} .success {color: green;} .error {color: red;} .warning {color: orange;} pre {background: #f4f4f4; padding: 10px;}</style>";

// 1. Información del sistema
echo "<h2>📋 Información del Sistema</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Architecture: " . (PHP_INT_SIZE === 8 ? 'x64' : 'x86') . "<br>";
echo "SAPI: " . PHP_SAPI . "<br>";
echo "php.ini loaded: " . php_ini_loaded_file() . "<br>";

// 2. Verificar si OCI8 está cargada
echo "<h2>🔌 Estado de OCI8</h2>";
if (extension_loaded('oci8')) {
    echo "<p class='success'>✅ EXTENSIÓN OCI8 CARGADA</p>";

    // Mostrar información de la extensión
    echo "<h3>📊 Información de OCI8</h3>";
    if (function_exists('oci_client_version')) {
        echo "Versión cliente: " . oci_client_version() . "<br>";
    }

    if (function_exists('oci_connect')) {
        echo "Funciones disponibles: Sí<br>";

        // Intentar una conexión simple
        echo "<h3>🧪 Test de Conexión</h3>";
        $conn = @oci_connect('test', 'test', '//localhost:1521/XE');
        if ($conn) {
            echo "<span class='success'>✅ Conexión exitosa</span><br>";
            oci_close($conn);
        } else {
            $e = oci_error();
            echo "<span class='warning'>⚠️ Error de conexión (esperado): " . $e['message'] . "</span><br>";
        }
    }
} else {
    echo "<p class='error'>❌ EXTENSIÓN OCI8 NO CARGADA</p>";
}

// 3. Verificar archivo DLL (TUS RUTAS ESPECÍFICAS)
echo "<h2>📁 Verificación de Archivos EN TU RUTA</h2>";
$dll_paths = [
    'C:\\xampp\\php\\ext\\php_oci8_19.dll',  // Extensión PHP
    'C:\\Program Files\\Oracle\\instantclient\\oci.dll',  // TU RUTA de Oracle
    'C:\\Program Files\\Oracle\\instantclient\\oraociei19.dll',  // DLL principal
    'C:\\Program Files\\Oracle\\instantclient\\oraocci19.dll'   // Otra DLL importante
];

foreach ($dll_paths as $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<span class='success'>✅ EXISTE:</span> " . $path . " (" . round($size/1024, 2) . " KB)<br>";

        // Verificar si es readable
        if (is_readable($path)) {
            echo "<span class='success'>   → Lectura permitida</span><br>";
        } else {
            echo "<span class='error'>   → Sin permisos de lectura</span><br>";
        }
    } else {
        echo "<span class='error'>❌ NO EXISTE:</span> " . $path . "<br>";
    }
}

// 4. Verificar contenido de TU carpeta de Instant Client
echo "<h2>📦 Contenido de TU Instant Client</h2>";
$your_instantclient_path = 'C:\\Program Files\\Oracle\\instantclient';
if (is_dir($your_instantclient_path)) {
    echo "Contenido de: " . $your_instantclient_path . "<br>";

    $files = scandir($your_instantclient_path);
    $dll_count = 0;

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'dll') {
            echo "→ " . $file . " (" . round(filesize($your_instantclient_path . '\\' . $file)/1024, 2) . " KB)<br>";
            $dll_count++;
        }
    }

    if ($dll_count === 0) {
        echo "<span class='error'>❌ No se encontraron DLLs en tu carpeta</span><br>";
    } else {
        echo "<span class='success'>✅ Se encontraron $dll_count DLLs</span><br>";
    }
} else {
    echo "<span class='error'>❌ Tu carpeta no existe: " . $your_instantclient_path . "</span><br>";
}

// 5. Verificar configuración en php.ini
echo "<h2>⚙️ Configuración en php.ini</h2>";
$ini_file = php_ini_loaded_file();
if ($ini_file && file_exists($ini_file)) {
    $ini_content = file_get_contents($ini_file);

    // Buscar líneas relacionadas con oci8
    preg_match_all('/.*(oci8|oracle).*/i', $ini_content, $matches);

    if (!empty($matches[0])) {
        echo "Líneas encontradas en php.ini:<br>";
        foreach ($matches[0] as $line) {
            echo htmlspecialchars(trim($line)) . "<br>";
        }
    } else {
        echo "<span class='error'>❌ No se encontraron líneas 'oci8' en php.ini</span><br>";
    }
} else {
    echo "<span class='error'>❌ No se pudo leer php.ini</span><br>";
}

// 6. Verificar PATH del sistema (ENFOCADO EN TU RUTA)
echo "<h2>🛣️ Variable PATH del Sistema (TUS RUTAS)</h2>";
$path = getenv('PATH');
$path_items = explode(';', $path);
$oracle_paths = [];
$your_path_found = false;

foreach ($path_items as $item) {
    $item = trim($item);
    if (stripos($item, 'oracle') !== false || stripos($item, 'instantclient') !== false) {
        $oracle_paths[] = $item;

        // Verificar específicamente tu ruta
        if (strtolower($item) === strtolower('C:\Program Files\Oracle\instantclient')) {
            $your_path_found = true;
        }
    }
}

if (!empty($oracle_paths)) {
    echo "Rutas de Oracle en PATH:<br>";
    foreach ($oracle_paths as $path) {
        if (file_exists($path)) {
            $status = (strtolower($path) === strtolower('C:\Program Files\Oracle\instantclient'))
                ? "<span class='success'>✅ TU RUTA:</span>"
                : "<span class='success'>✅ EXISTE:</span>";
            echo $status . " " . $path . "<br>";
        } else {
            echo "<span class='error'>❌ NO EXISTE:</span> " . $path . "<br>";
        }
    }

    if (!$your_path_found) {
        echo "<span class='error'>❌ TU RUTA ESPECÍFICA no está en el PATH</span><br>";
        echo "Falta: C:\\Program Files\\Oracle\\instantclient<br>";
    }
} else {
    echo "<span class='error'>❌ No se encontraron rutas de Oracle en el PATH</span><br>";
}

// 7. Verificar variables de entorno de Oracle
echo "<h2>🌐 Variables de Entorno Oracle</h2>";
$oracle_vars = [
    'ORACLE_HOME',
    'TNS_ADMIN',
    'NLS_LANG',
    'PATH'
];

foreach ($oracle_vars as $var) {
    $value = getenv($var);
    if ($value) {
        if ($var === 'PATH') {
            // Mostrar solo las partes de Oracle del PATH
            $path_parts = explode(';', $value);
            $oracle_parts = array_filter($path_parts, function($part) {
                return stripos($part, 'oracle') !== false || stripos($part, 'instantclient') !== false;
            });

            if (!empty($oracle_parts)) {
                echo "$var contiene:<br>";
                foreach ($oracle_parts as $part) {
                    echo "→ " . $part . "<br>";
                }
            } else {
                echo "$var = <span class='error'>No contiene rutas de Oracle</span><br>";
            }
        } else {
            echo "$var = $value<br>";
        }
    } else {
        echo "$var = <span class='warning'>No definida</span><br>";
    }
}

// 8. Probar carga manual de extensión
echo "<h2>🧪 Test de Carga Manual</h2>";
if (!extension_loaded('oci8')) {
    // Intentar cargar la DLL
    if (function_exists('dl')) {
        $result = @dl('php_oci8_19.dll');
        if ($result) {
            echo "<span class='success'>✅ DLL cargada manualmente con éxito</span><br>";
        } else {
            echo "<span class='error'>❌ Error al cargar DLL manualmente</span><br>";

            // Mostrar último error
            $last_error = error_get_last();
            if ($last_error) {
                echo "Último error: " . $last_error['message'] . "<br>";
            }
        }
    } else {
        echo "<span class='warning'>⚠️ Función dl() está deshabilitada</span><br>";
    }
} else {
    echo "Extensión ya cargada, no necesita carga manual<br>";
}

// 9. Verificar logs de error
echo "<h2>📋 Logs de Error Recientes</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $logs = tailCustom($error_log, 15);
    echo "Últimas 15 líneas del log (busca 'oci', 'oracle', 'failed'):<br>";
    echo "<pre>" . htmlspecialchars($logs) . "</pre>";
} else {
    echo "Log de errores: " . ($error_log ?: 'No configurado') . "<br>";
}

// 10. Extensiones cargadas
echo "<h2>📦 Extensiones Cargadas</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<pre>" . implode("\n", $extensions) . "</pre>";

// Función para leer las últimas líneas de un archivo
function tailCustom($filepath, $lines = 1) {
    try {
        if (!file_exists($filepath)) return "Archivo no existe: $filepath";

        $f = @fopen($filepath, "rb");
        if ($f === false) return "No se pudo abrir el archivo";

        fseek($f, -1, SEEK_END);
        if (fread($f, 1) != "\n") $lines -= 1;

        $output = '';
        while (ftell($f) > 0 && $lines >= 0) {
            $chunk = min(ftell($f), 1024);
            fseek($f, -$chunk, SEEK_CUR);
            $output = ($chunk = fread($f, $chunk)) . $output;
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            $lines -= substr_count($chunk, "\n");
        }

        fclose($f);
        return trim($output);
    } catch (Exception $e) {
        return "Error reading log file: " . $e->getMessage();
    }
}

echo "<hr>";
echo "<h2>🎯 Resumen del Diagnóstico ESPECÍFICO</h2>";

if (extension_loaded('oci8')) {
    echo "<p class='success'>✅ OCI8 está funcionando correctamente</p>";
} else {
    echo "<p class='error'>❌ OCI8 no se está cargando</p>";
    echo "<h3>📋 Verifica específicamente:</h3>";
    echo "1. <strong>Tu ruta existe:</strong> C:\\Program Files\\Oracle\\instantclient<br>";
    echo "2. <strong>Tu ruta está en PATH:</strong> Debe aparecer en variables de entorno<br>";
    echo "3. <strong>Las DLLs existen:</strong> oci.dll, oraociei19.dll, etc.<br>";
    echo "4. <strong>Reinicio completo:</strong> Después de cambiar el PATH<br>";
    echo "5. <strong>Coincidencia de arquitectura:</strong> x86 vs x64<br>";
}

echo "<p><strong>📋 Copia y pega TODA esta salida para analizarla</strong></p>";
echo "<p><strong>💡 Fíjate especialmente en las secciones de 'TU RUTA'</strong></p>";
?>