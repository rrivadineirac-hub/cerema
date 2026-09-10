<?php
// Model/mlogin.php
class LoginModel {
    private $conn;
    private $table_name = "usuarios_sistema";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function authenticate($username, $password) {
        $query = "SELECT u.*, s.nombre, s.ap_paterno 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN asociados s ON u.id_socio = s.id_socio 
                  WHERE u.username = :username AND u.activo = 1";
                  
        $stmt = $this->conn->prepare($query);
        
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificación de la contraseña (usando password_verify de PHP)
            if (password_verify($password, $user['password_hash'])) {
                // Return array without the hash for safety
                unset($user['password_hash']);
                return $user;
            }
        }
        
        return false;
    }
}
?>
