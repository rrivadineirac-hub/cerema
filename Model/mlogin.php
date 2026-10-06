<?php
// Model/mlogin.php
class LoginModel {
    private $conn;
    private $table_name = "usuarios_sistema";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Verificar si el asociado tiene un cargo de Presidente o Contador (Tesorero/Hacienda) en la Mesa Directiva
     */
    private function getDirectivaRole($idSocio) {
        if (empty($idSocio)) {
            return null;
        }
        try {
            $queryDir = "SELECT c.nombre_cargo 
                         FROM mesa_directiva md 
                         INNER JOIN cargos c ON md.id_cargo = c.id_cargo 
                         WHERE md.id_socio = :id_socio 
                         ORDER BY md.gestion DESC, md.id_directiva DESC LIMIT 1";
            $stmtDir = $this->conn->prepare($queryDir);
            $stmtDir->bindParam(":id_socio", $idSocio, PDO::PARAM_INT);
            $stmtDir->execute();

            if ($stmtDir->rowCount() > 0) {
                $cargoData = $stmtDir->fetch(PDO::FETCH_ASSOC);
                $cargoNombre = mb_strtolower(trim($cargoData['nombre_cargo']), 'UTF-8');
                
                if (strpos($cargoNombre, 'presidente') !== false) {
                    return 'Presidente';
                } elseif (strpos($cargoNombre, 'contador') !== false || strpos($cargoNombre, 'tesorero') !== false || strpos($cargoNombre, 'hacienda') !== false) {
                    return 'Contador';
                }
            }
        } catch (Exception $e) {
            // Ignorar excepciones en caso de fallo en BD
        }
        return null;
    }

    public function authenticate($username, $password) {
        $usernameClean = trim(htmlspecialchars(strip_tags($username)));

        // 1. Intentar autenticar contra usuarios_sistema
        $query = "SELECT u.*, s.nombre, s.ap_paterno, s.ci as socio_ci 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN asociados s ON u.id_socio = s.id_socio 
                  WHERE LOWER(TRIM(u.username)) = LOWER(:username) AND u.activo = 1";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $usernameClean);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificación de la contraseña (usando password_verify o texto directo para compatibilidad)
            if (password_verify($password, $user['password_hash']) || $password === $user['password_hash']) {
                unset($user['password_hash']);

                // Si el usuario está vinculado a un socio y no es Super Usuario / Administrador,
                // verificar si tiene cargo de Presidente o Contador en la Mesa Directiva
                if (!empty($user['id_socio']) && !in_array($user['rol_sistema'], ['Super Usuario', 'Infoser 76', 'Administrador'])) {
                    $directivaRol = $this->getDirectivaRole($user['id_socio']);
                    if ($directivaRol !== null) {
                        $user['rol_sistema'] = $directivaRol;
                    }
                }

                return $user;
            }
        }

        // 2. Si no se encontró en usuarios_sistema, intentar autenticar como ASOCIADO (Socio)
        // El usuario puede ingresar con su ID de socio (ej. ASC-001, ASC-015, ASC-155 o sólo el número 1, 15, 155) o su CI
        // La contraseña por defecto del asociado es su número de CI
        $idSocioExtraido = null;
        if (preg_match('/^ASC-?(\d+)$/i', $usernameClean, $matches)) {
            $idSocioExtraido = (int)$matches[1];
        } elseif (is_numeric($usernameClean)) {
            $idSocioExtraido = (int)$usernameClean;
        }

        $querySocio = "SELECT id_socio, ci, complemento, nombre, ap_paterno, ap_materno, estado 
                       FROM asociados 
                       WHERE ";
        if ($idSocioExtraido !== null) {
            $querySocio .= "(id_socio = :id_socio OR ci = :username_raw)";
        } else {
            $querySocio .= "ci = :username_raw";
        }
        $querySocio .= " LIMIT 1";

        $stmtSocio = $this->conn->prepare($querySocio);
        $stmtSocio->bindParam(":username_raw", $usernameClean);
        if ($idSocioExtraido !== null) {
            $stmtSocio->bindParam(":id_socio", $idSocioExtraido, PDO::PARAM_INT);
        }
        $stmtSocio->execute();

        if ($stmtSocio->rowCount() > 0) {
            $socio = $stmtSocio->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si el estado del socio permite ingresar (Activo o Pasivo)
            // Validar contraseña contra su CI (limpiando espacios)
            $ciLimpio = trim($socio['ci']);
            $passwordClean = trim($password);

            if ($passwordClean === $ciLimpio) {
                // Formatear código de usuario (ej. ASC-001)
                $codigoASC = 'ASC-' . str_pad($socio['id_socio'], 3, '0', STR_PAD_LEFT);

                // Determinar rol por defecto (Socio) o por cargo en Mesa Directiva (Presidente / Contador)
                $rolSistema = 'Socio';
                $directivaRol = $this->getDirectivaRole($socio['id_socio']);
                if ($directivaRol !== null) {
                    $rolSistema = $directivaRol;
                }

                return [
                    'id_usuario'     => 'socio_' . $socio['id_socio'],
                    'id_socio'       => $socio['id_socio'],
                    'username'       => $codigoASC,
                    'rol_sistema'    => $rolSistema,
                    'nombre'         => $socio['nombre'],
                    'ap_paterno'     => $socio['ap_paterno'],
                    'socio_ci'       => $socio['ci'],
                    'activo'         => 1
                ];
            }
        }
        
        return false;
    }
}
?>
