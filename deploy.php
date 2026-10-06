<?php
// deploy.php - Script de Despliegue Automático en 1 Clic para crema.free.je
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "=======================================================\n";
echo "  DESPLEGADOR AUTOMÁTICO CEREMA -> crema.free.je\n";
echo "=======================================================\n\n";

$envFile = __DIR__ . '/.env.deploy';
if (!file_exists($envFile)) {
    echo "⚠️  No se encontró el archivo '.env.deploy'.\n";
    echo "Creando '.env.deploy' a partir de '.env.deploy.example'...\n";
    copy(__DIR__ . '/.env.deploy.example', $envFile);
    echo "\nPor favor abre '.env.deploy' y edita tus datos de FTP y MySQL de InfinityFree, luego vuelve a ejecutar:\n";
    echo "    php deploy.php\n\n";
    exit(0);
}

// Leer configuración de .env.deploy
$config = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $val) = explode('=', $line, 2);
        $config[trim($key)] = trim($val);
    }
}

$ftpHost   = $config['FTP_HOST'] ?? 'ftpupload.net';
$ftpUser   = $config['FTP_USER'] ?? '';
$ftpPass   = $config['FTP_PASS'] ?? '';
$ftpPort   = (int)($config['FTP_PORT'] ?? 21);
$targetDir = $config['FTP_TARGET_DIR'] ?? '/htdocs';
$siteUrl   = rtrim($config['SITE_URL'] ?? 'http://cerema.free.je', '/');

$dbHost = $config['DB_HOST'] ?? '';
$dbName = $config['DB_NAME'] ?? '';
$dbUser = $config['DB_USER'] ?? '';
$dbPass = $config['DB_PASS'] ?? '';

if (empty($ftpUser) || $ftpUser === 'if0_38400000' || empty($ftpPass) || $ftpPass === 'TuPasswordDecPanel') {
    echo "❌ ERROR: Debes configurar tus datos reales de FTP y MySQL en el archivo '.env.deploy'.\n";
    echo "Edita c:\\xampp\\htdocs\\cerema\\.env.deploy con tus datos de InfinityFree y vuelve a ejecutar este script.\n";
    exit(1);
}

echo "1. Conectando por FTP a $ftpHost:$ftpPort...\n";
$ftpConn = @ftp_connect($ftpHost, $ftpPort, 15);
if (!$ftpConn) {
    die("❌ No se pudo conectar al servidor FTP $ftpHost.\n");
}

$login = @ftp_login($ftpConn, $ftpUser, $ftpPass);
if (!$login) {
    die("❌ Autenticación FTP fallida para usuario $ftpUser. Revisa la contraseña en .env.deploy.\n");
}

@ftp_pasv($ftpConn, true);
echo "✅ Conexión FTP establecida con éxito.\n\n";

// Modificar temporalmente Config/database.php antes de subir
$dbConfigPath = __DIR__ . '/Config/database.php';
$originalDbConfig = file_get_contents($dbConfigPath);

$customDbConfig = preg_replace(
    '/\$this->host = getenv\(\'DB_HOST\'\) \?: "[^"]*";/',
    '$this->host = "' . addslashes($dbHost) . '";',
    $originalDbConfig
);
$customDbConfig = preg_replace(
    '/\$this->db_name = getenv\(\'DB_NAME\'\) \?: "[^"]*";/',
    '$this->db_name = "' . addslashes($dbName) . '";',
    $customDbConfig
);
$customDbConfig = preg_replace(
    '/\$this->username = getenv\(\'DB_USER\'\) \?: "[^"]*";/',
    '$this->username = "' . addslashes($dbUser) . '";',
    $customDbConfig
);
$customDbConfig = preg_replace(
    '/\$this->password = getenv\(\'DB_PASS\'\) \?: "[^"]*";/',
    '$this->password = "' . addslashes($dbPass) . '";',
    $customDbConfig
);

file_put_contents($dbConfigPath, $customDbConfig);

echo "2. Subiendo archivos del proyecto a $targetDir...\n";

// Helper para asegurar reconexión FTP en caso de timeout
function getFtpConnection($host, $port, $user, $pass) {
    $conn = @ftp_connect($host, $port, 15);
    if ($conn && @ftp_login($conn, $user, $pass)) {
        @ftp_pasv($conn, true);
        return $conn;
    }
    return false;
}

function uploadFileWithRetry(&$conn, $host, $port, $user, $pass, $remoteFile, $localFile) {
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        // Asegurar que el directorio remoto exista
        $remoteDir = dirname($remoteFile);
        @ftp_mkdir($conn, $remoteDir);

        if (@ftp_put($conn, $remoteFile, $localFile, FTP_BINARY)) {
            return true;
        }

        // Si falló, intentar reconectar
        @ftp_close($conn);
        sleep(1);
        $conn = getFtpConnection($host, $port, $user, $pass);
    }
    return false;
}

// Función recursiva de subida FTP con reintentos
function uploadDir(&$conn, $host, $port, $user, $pass, $localPath, $remotePath) {
    $dir = opendir($localPath);
    @ftp_mkdir($conn, $remotePath);

    $exclude = ['.git', '.github', 'scratch', '.env.deploy', '.env.deploy.example', 'deploy.php', 'export_db.php', 'crop.ps1'];

    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        if (in_array($file, $exclude)) continue;

        $localFile = $localPath . '/' . $file;
        $remoteFile = $remotePath . '/' . $file;

        if (is_dir($localFile)) {
            uploadDir($conn, $host, $port, $user, $pass, $localFile, $remoteFile);
        } else {
            echo "   -> Subiendo: $remoteFile... ";
            if (uploadFileWithRetry($conn, $host, $port, $user, $pass, $remoteFile, $localFile)) {
                echo "OK\n";
            } else {
                echo "FALLIDO (Reintentos agotados)\n";
            }
        }
    }
    closedir($dir);
}

uploadDir($ftpConn, $ftpHost, $ftpPort, $ftpUser, $ftpPass, __DIR__, $targetDir);

// Eliminar index2.html del servidor remoto si existe
@ftp_delete($ftpConn, $targetDir . '/index2.html');

echo "\n✅ Subida de archivos completada.\n\n";

// Restaurar Config/database.php local
file_put_contents($dbConfigPath, $originalDbConfig);

// 3. Ejecutar instalador remoto de base de datos
echo "3. Ejecutando instalación automática de la base de datos en el servidor remoto...\n";
$setupUrl = $siteUrl . '/setup_remote_db.php';
echo "   Llamando a: $setupUrl\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $setupUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Respuesta del Servidor (HTTP $httpCode):\n";
echo "-------------------------------------------------------\n";
echo $response ? $response : "No se obtuvo respuesta directa (verificar si el servidor tardó o se completó).\n";
echo "-------------------------------------------------------\n\n";

echo "🎉 ¡PROCESO DE DESPLIEGUE FINALIZADO!\n";
echo "Puedes abrir tu navegador en: $siteUrl\n";
