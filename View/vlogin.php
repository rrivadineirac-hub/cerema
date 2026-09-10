<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CEREMA</title>
    <!-- Favicon -->
    <link rel="icon" href="../imagenes/institucinal/logo.png" type="image/png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Login CSS -->
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <div class="login-container">
        
        <!-- Left Panel: Brand / Background Image -->
        <div class="login-brand">
            <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="brand-logo-topleft">
        </div>

        <!-- Right Panel: Form -->
        <div class="login-form-wrapper">
            <div class="form-container">
                <h2>Iniciar Sesión</h2>
                <p class="subtitle">Ingresa tus credenciales para continuar</p>
                
                <div id="alert-box" class="alert-box" style="display: none;"></div>

                <form id="loginForm">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="input-group">
                        <label for="username">Usuario</label>
                        <div class="input-icon-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="username" name="username" placeholder="Tu nombre de usuario" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <div class="input-icon-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Tu contraseña" required>
                            <i class="fa-regular fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="submitBtn">
                        <span class="btn-text">Ingresar</span>
                        <i class="fa-solid fa-arrow-right btn-icon"></i>
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>&copy; <?php echo date('Y'); ?> CEREMA. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Login JS -->
    <script src="../js/login.js"></script>
</body>
</html>
