<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'profesor') {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuraciones</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Sistema de Asistencia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="scanner.php">Escanear QR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php">Reportes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="configuraciones.php">Configuraciones</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Cambiar Contraseña</h5>
                    </div>
                    <div class="card-body">
                        <form id="cambiarPasswordForm">
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                <div class="form-text">La contraseña debe tener al menos 8 caracteres.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary" id="cambiarPasswordBtn">Cambiar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('#cambiarPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();
                
                // Validación básica
                if (newPassword.length < 8) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La nueva contraseña debe tener al menos 8 caracteres'
                    });
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden'
                    });
                    return;
                }
                
                // Mostrar indicador de carga
                $('#cambiarPasswordBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                
                // Enviar solicitud al servidor
                $.ajax({
                    url: '../../includes/profesores/cambiar_password.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        currentPassword: currentPassword,
                        newPassword: newPassword
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message || 'Contraseña actualizada correctamente'
                            }).then(function() {
                                // Limpiar el formulario
                                $('#cambiarPasswordForm')[0].reset();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al cambiar la contraseña'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error al conectar con el servidor';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            console.error('Error parsing response:', xhr.responseText);
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        // Restaurar el botón
                        $('#cambiarPasswordBtn').prop('disabled', false).text('Cambiar Contraseña');
                    }
                });
            });
        });
    </script>
</body>
</html> 