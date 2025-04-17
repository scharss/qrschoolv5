<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .nav-tabs .nav-item .nav-link {
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">QR School</h2>
            
            <!-- Tabs de navegación -->
            <ul class="nav nav-tabs mb-4" id="loginTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-login" type="button" role="tab">Admin/Profesor</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-login" type="button" role="tab">Estudiante</button>
                </li>
            </ul>
            
            <!-- Contenido de los tabs -->
            <div class="tab-content" id="loginTabsContent">
                <!-- Tab de Admin/Profesor -->
                <div class="tab-pane fade show active" id="admin-login" role="tabpanel">
                    <form id="adminLoginForm">
                        <input type="hidden" name="user_type" value="admin">
                        <div class="mb-3">
                            <label for="admin-email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="admin-email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin-password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="admin-password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="adminLoginButton">Iniciar Sesión</button>
                    </form>
                </div>
                
                <!-- Tab de Estudiante -->
                <div class="tab-pane fade" id="student-login" role="tabpanel">
                    <form id="studentLoginForm">
                        <input type="hidden" name="user_type" value="student">
                        <div class="mb-3">
                            <label for="student-document" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="student-document" name="document" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100" id="studentLoginButton">Acceder</button>
                    </form>
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
    <!-- Custom JS -->
    <script src="assets/js/login.js"></script>
</body>
</html> 