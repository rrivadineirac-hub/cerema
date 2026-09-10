<?php
// index.php
// Punto de entrada de la aplicación (Front Controller básico)
session_start();

// LOGIN HABILITADO
if (!isset($_SESSION['user_id'])) {
    header("Location: Controller/login.controller.php");
    exit();
}

// 1. Cargar la configuración de la base de datos
require_once 'Config/database.php';

// 2. Verificar la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

if ($db) {
    // Si la conexión es exitosa, redirigimos al usuario a la vista del Dashboard
    header("Location: View/dashboard.php");
    exit();
} else {
    // Si falla la conexión, mostramos un mensaje de error amigable y descriptivo
    echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Error de Conexión - CEREMA</title></head><body style='margin:0; font-family: system-ui, -apple-system, sans-serif; background: #F4F7F6;'>";
    echo "<div style='padding: 50px 20px; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; box-sizing: border-box;'>";
    echo "<div style='background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 650px; width: 100%; text-align: left;'>";
    echo "<h1 style='color: #FF7A00; margin-top: 0; display: flex; align-items: center; gap: 10px;'>⚠️ Error de Conexión a la Base de Datos</h1>";
    echo "<p style='color: #4A5568;'>No se pudo conectar a la base de datos <b>cerema_db</b> en este equipo.</p>";
    
    if (!empty($database->error)) {
        echo "<div style='background: #FFF5F5; border-left: 4px solid #E53E3E; padding: 12px 15px; margin: 20px 0; border-radius: 4px; font-family: monospace; font-size: 13px; color: #C53030; word-break: break-all;'>";
        echo "<b>Detalle del error:</b> " . htmlspecialchars($database->error);
        echo "</div>";
    }
    
    echo "<h3 style='color: #2D3748; margin-top: 25px;'>Pasos para solucionar este problema en el nuevo equipo:</h3>";
    echo "<ol style='line-height: 1.8; color: #4A5568; padding-left: 20px;'>";
    echo "<li>Abre el panel de control de <b>XAMPP</b> y verifica que el servicio <b>MySQL</b> esté en verde (<b>Start</b>).</li>";
    echo "<li>Abre <a href='http://localhost/phpmyadmin' target='_blank' style='color: #3182CE; font-weight: bold;'>http://localhost/phpmyadmin</a> en tu navegador.</li>";
    echo "<li>Crea la base de datos <code>cerema_db</code> e importa el archivo <b>cerema_db.sql</b> que se encuentra en la raíz de este proyecto.</li>";
    echo "<li>Si tu MySQL en el nuevo equipo usa contraseña o un puerto distinto (ej. 3307), actualiza los datos en <code>Config/database.php</code>.</li>";
    echo "</ol>";
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='index.php' style='display: inline-block; background: #FF7A00; color: white; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px;'>Reintentar Conexión</a>";
    echo "</div>";
    echo "</div>";
    echo "</div></body></html>";
}
?>
