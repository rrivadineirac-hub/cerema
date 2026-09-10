<?php
// Config/database.php
date_default_timezone_set('America/La_Paz');

class Database {
    private $host = "localhost";
    private $db_name = "cerema_db";
    private $username = "root"; // Ajustar si es diferente en tu XAMPP
    private $password = "";     // Ajustar si tiene contraseña en XAMPP
    public $conn;
    public $error = null;

    public function getConnection() {
        $this->conn = null;
        $this->error = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $exception) {
            $this->error = $exception->getMessage();
            
            // Si la base de datos no existe en el nuevo equipo (error 1049)
            if (strpos($this->error, "Unknown database") !== false || $exception->getCode() == 1049) {
                try {
                    // Conectar a MySQL sin especificar base de datos
                    $server_conn = new PDO(
                        "mysql:host=" . $this->host . ";charset=utf8mb4",
                        $this->username,
                        $this->password,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    // Crear la base de datos si no existe
                    $server_conn->exec("CREATE DATABASE IF NOT EXISTS `$this->db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
                    
                    // Conectar a la nueva BD con multi-statements para la importación
                    $this->conn = new PDO(
                        "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                        $this->username,
                        $this->password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
                        ]
                    );

                    // Importar tablas y datos iniciales desde cerema_db.sql si existe
                    $sql_file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cerema_db.sql';
                    if (file_exists($sql_file)) {
                        $sql_content = file_get_contents($sql_file);
                        if (!empty($sql_content)) {
                            $this->conn->exec($sql_content);
                        }
                    }
                    $this->error = null;
                } catch(PDOException $e) {
                    $this->error = "No se pudo crear o inicializar automáticamente la base de datos '$this->db_name'. Por favor créala manualmente en phpMyAdmin e importa el archivo cerema_db.sql. Detalle: " . $e->getMessage();
                }
            }
        }

        return $this->conn;
    }
}
?>
