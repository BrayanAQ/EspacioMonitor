<?php
session_start();
require_once 'database.php';

// Obtener DB Links disponibles usando la clase
$oracleDB = new OracleDatabase();
$dblinks = $oracleDB->getRealDatabaseLinks(); // Esto fallará si no hay OCI8

// Si no hay OCI8 instalado, getRealDatabaseLinks() lanzará una excepción
// Podemos capturarla y mostrar un mensaje apropiado
try {
    $dblinks = $oracleDB->getRealDatabaseLinks();
} catch (Exception $e) {
    // Si hay error, mostramos mensaje pero continuamos
    $dblinks = [];
    $error_message = $e->getMessage();
}

$status = $oracleDB->testConnection();

// Verificar si ya está logueado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Oracle DB Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-database"></i>
            </div>
            <h1>Oracle Database Manager</h1>
            <p>Seleccione un Database Link para conectarse</p>

            <!-- Mostrar estado de conexión -->
            <div class="connection-status">
                <div class="status-item">
                    <strong>Modo:</strong>
                    <span class="<?= $status['success'] ? 'success' : 'error' ?>">
                            <?= $status['success'] ? '🔗 Modo Real' : '❌ Error de conexión' ?>
                        </span>
                </div>
                <div class="status-item">
                    <strong>Estado:</strong> <?= $status['message'] ?>
                </div>
                <?php if (isset($error_message)): ?>
                    <div class="status-item error">
                        <strong>Error:</strong> <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($dblinks)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se encontraron Database Links. Verifica la conexión a la base de datos.
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="dblink">Database Link:</label>
                <div class="input-with-icon">
                    <i class="fas fa-link"></i>
                    <select id="dblink" name="dblink" required class="form-select">
                        <option value="">-- Seleccione un DB Link --</option>
                        <?php foreach ($dblinks as $link): ?>
                            <option value="<?= htmlspecialchars($link['DB_LINK']) ?>"
                                    data-username="<?= htmlspecialchars($link['USERNAME']) ?>"
                                    data-host="<?= htmlspecialchars($link['HOST']) ?>">
                                <?= htmlspecialchars($link['DB_LINK']) ?>
                                (<?= htmlspecialchars($link['USERNAME']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required
                           placeholder="Ingrese la contraseña" class="form-input">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Conectar
                </button>
                <button type="button" onclick="location.reload()" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
        </form>

        <div class="login-footer">
            <p>Conectando a: <strong>PDB_CENTRAL</strong></p>
            <p><?= count($dblinks) ?> DB Links disponibles</p>

            <?php if (!$status['success']): ?>
                <div class="demo-warning">
                    <i class="fas fa-info-circle"></i>
                    Error de conexión: <?= $status['error'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('dblink').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const username = selectedOption.getAttribute('data-username');
        if (username) {
            document.getElementById('password').placeholder = `Contraseña para ${username}`;
        }
    });
</script>
</body>
</html>