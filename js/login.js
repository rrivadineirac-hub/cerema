// js/login.js
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const alertBox = document.getElementById('alert-box');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');

    // Toggle password visibility
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle icon
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    // Handle form submission via AJAX
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic frontend validation
        const username = document.getElementById('username').value.trim();
        const password = passwordInput.value.trim();

        if(username === '' || password === '') {
            showAlert('Por favor ingresa usuario y contraseña.', 'alert-error');
            return;
        }

        // Disable button to prevent double submit
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Autenticando...';
        submitBtn.disabled = true;

        const formData = new FormData(loginForm);

        // Determinar URL relativa al controlador
        const targetUrl = window.location.pathname.includes('/Controller/') 
            ? 'login.controller.php' 
            : 'Controller/login.controller.php';

        fetch(targetUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch(err) {
                throw new Error('Respuesta del servidor no es JSON: ' + text.substring(0, 150));
            }
        })
        .then(data => {
            if(data.success) {
                // Redirect on success
                window.location.href = data.redirect;
            } else {
                showAlert(data.message || 'Credenciales incorrectas o error en el sistema.', 'alert-error');
                submitBtn.innerHTML = originalBtnHtml;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error en Login:', error);
            showAlert('Error: ' + error.message, 'alert-error');
            submitBtn.innerHTML = originalBtnHtml;
            submitBtn.disabled = false;
        });
    });

    // Check for URL parameters (e.g. ?msg=timeout)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'timeout') {
        showAlert('Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.', 'alert-warning');
        // Clean the URL so it doesn't stay there if they refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function showAlert(message, type) {
        alertBox.textContent = message;
        alertBox.className = 'alert-box ' + type;
        alertBox.style.display = 'block';
        
        // Retrigger animation
        alertBox.style.animation = 'none';
        alertBox.offsetHeight; /* trigger reflow */
        alertBox.style.animation = null;
    }
});
