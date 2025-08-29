<?php
/**
 * Configuración REAL de conexión a Oracle Database
 * Este archivo NO tiene funciones simuladas - Solo funciona si OCI8 está instalado
 */

class OracleDatabase {
    private $conn;

    // Configuración de conexión a PDB_CENTRAL
    const DB_CONFIG = [
        'host'          => 'localhost',
        'port'          => '1521',
        'service_name'  => 'pdb_central',
        'username'      => 'usuario_central',
        'password'      => 'central123',
        'charset'       => 'AL32UTF8'
    ];

    /**
     * Verifica si OCI8 está instalado
     */
    public static function isOci8Installed() {
        return extension_loaded('oci8');
    }

    /**
     * Intenta conectar y devuelve el estado real
     */
    public function testConnection() {
        if (!self::isOci8Installed()) {
            return [
                'success' => false,
                'message' => '❌ Extensión OCI8 NO está instalada',
                'error' => 'Falta la extensión php_oci8.dll'
            ];
        }

        try {
            $conn = oci_connect(
                self::DB_CONFIG['username'],
                self::DB_CONFIG['password'],
                self::DB_CONFIG['host'] . ':' . self::DB_CONFIG['port'] . '/' . self::DB_CONFIG['service_name'],
                self::DB_CONFIG['charset']
            );

            if ($conn) {
                oci_close($conn);
                return [
                    'success' => true,
                    'message' => '✅ Conexión Oracle EXITOSA',
                    'details' => 'Extensión OCI8 funcionando correctamente'
                ];
            } else {
                $error = oci_error();
                return [
                    'success' => false,
                    'message' => '❌ Error de conexión Oracle',
                    'error' => $error['message']
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '❌ Excepción en conexión',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los DB Links REALES desde la base de datos
     */
    public function getRealDatabaseLinks() {
        if (!self::isOci8Installed()) {
            throw new Exception("OCI8 no instalado. No se pueden obtener DB Links reales.");
        }

        $conn = oci_connect(
            self::DB_CONFIG['username'],
            self::DB_CONFIG['password'],
            self::DB_CONFIG['host'] . ':' . self::DB_CONFIG['port'] . '/' . self::DB_CONFIG['service_name']
        );

        if (!$conn) {
            $error = oci_error();
            throw new Exception("Error de conexión: " . $error['message']);
        }

        $sql = "SELECT db_link, username, host, created, owner 
                FROM user_db_links 
                ORDER BY db_link";

        $stid = oci_parse($conn, $sql);
        if (!$stid || !oci_execute($stid)) {
            $error = oci_error($conn);
            oci_close($conn);
            throw new Exception("Error en consulta: " . $error['message']);
        }

        $links = [];
        while ($row = oci_fetch_array($stid, OCI_ASSOC)) {
            $links[] = $row;
        }

        oci_free_statement($stid);
        oci_close($conn);

        return $links;
    }

    /**
     * Verifica información detallada del sistema
     */
    public static function getSystemInfo() {
        $info = [
            'php_version' => PHP_VERSION,
            'architecture' => (PHP_INT_SIZE === 8) ? 'x64 (64-bit)' : 'x86 (32-bit)',
            'thread_safe' => ZEND_THREAD_SAFE ? 'Sí' : 'No',
            'os' => PHP_OS,
            'oci8_installed' => self::isOci8Installed(),
            'oci8_version' => self::isOci8Installed() ? (function_exists('oci_client_version') ? oci_client_version() : 'Desconocida') : 'No instalado',
            'extension_dir' => ini_get('extension_dir'),
            'php_ini' => php_ini_loaded_file() ?: 'No encontrado'
        ];

        return $info;
    }
}

/**
 * FUNCIONES GLOBALES DE VERIFICACIÓN (sin simulaciones)
 */

/**
 * Verifica el estado REAL de la instalación Oracle
 */
function verifyOracleInstallation() {
    $db = new OracleDatabase();
    return $db->testConnection();
}

/**
 * Obtiene DB Links REALES (falla si no hay OCI8)
 */
function getRealDatabaseLinks() {
    $db = new OracleDatabase();
    return $db->getRealDatabaseLinks();
}

/**
 * Obtiene información del sistema
 */
function getSystemInfo() {
    return OracleDatabase::getSystemInfo();
}

/**
 * Intenta detectar el Oracle Instant Client
 */
function detectInstantClient() {
    $possible_paths = [
        'C:\\oracle\\instantclient',
        'C:\\instantclient',
        'C:\\xampp\\php\\extras\\oracle',
        'C:\\app\\client\\oracle',
        getenv('ORACLE_HOME') ?: '',
        getenv('TNS_ADMIN') ?: ''
    ];

    $detected = [];
    foreach ($possible_paths as $path) {
        if ($path && file_exists($path) && is_dir($path)) {
            // Verificar archivos clave de Oracle
            $files = @scandir($path);
            $oracle_files = array_filter($files, function($file) {
                return preg_match('/^(oci|ora).*\.dll$/i', $file);
            });

            if (count($oracle_files) > 0) {
                $detected[] = [
                    'path' => $path,
                    'files' => array_values($oracle_files)
                ];
            }
        }
    }

    return $detected;
}

/**
 * Verifica las variables de entorno de Oracle
 */
function checkOracleEnvironment() {
    $env_vars = [
        'ORACLE_HOME' => getenv('ORACLE_HOME'),
        'TNS_ADMIN' => getenv('TNS_ADMIN'),
        'PATH' => getenv('PATH'),
        'LD_LIBRARY_PATH' => getenv('LD_LIBRARY_PATH')
    ];

    $status = [];
    foreach ($env_vars as $var => $value) {
        $status[$var] = [
            'set' => !empty($value),
            'value' => $value ?: 'No configurada'
        ];
    }

    return $status;
}

/**
 * Prueba básica de funciones OCI8
 */
function testOci8Functions() {
    if (!OracleDatabase::isOci8Installed()) {
        return ['available' => false, 'functions' => []];
    }

    $functions = [
        'oci_connect',
        'oci_parse',
        'oci_execute',
        'oci_fetch_array',
        'oci_error',
        'oci_close',
        'oci_num_rows',
        'oci_client_version'
    ];

    $available = [];
    foreach ($functions as $function) {
        $available[$function] = function_exists($function);
    }

    return ['available' => true, 'functions' => $available];
}

?>