<?php
session_start();
// Verificar que el usuario está autenticado como estudiante
if (!isset($_SESSION['estudiante_id']) || $_SESSION['user_role'] !== 'estudiante') {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$estudiante_id = $_SESSION['estudiante_id'];
$estudiante_nombre = $_SESSION['estudiante_nombre'];
$documento = $_SESSION['estudiante_documento'];

$db = new Database();
$conn = $db->connect();

// Obtener información del estudiante
try {
    $stmt = $conn->prepare("
        SELECT e.*, g.nombre as grupo_nombre 
        FROM estudiantes e 
        LEFT JOIN grupos g ON e.grupo_id = g.id 
        WHERE e.id = ?
    ");
    $stmt->execute([$estudiante_id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        // Si no se encuentra el estudiante, redirigir al login
        header('Location: ../../index.php');
        exit;
    }

    // Obtener total de asistencias
    $stmt = $conn->prepare("SELECT COUNT(*) FROM asistencias WHERE estudiante_id = ?");
    $stmt->execute([$estudiante_id]);
    $total_asistencias = $stmt->fetchColumn();

} catch (PDOException $e) {
    // En caso de error, redirigir al login
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?php echo htmlspecialchars($estudiante_nombre); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .qr-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .profile-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="perfil.php">Sistema de Asistencia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3">Bienvenido, <?php echo htmlspecialchars($estudiante_nombre); ?></h1>
                    <p class="text-muted mb-1">Documento: <?php echo htmlspecialchars($documento); ?></p>
                    <p class="text-muted">Grupo: <?php echo $estudiante['grupo_nombre'] ? htmlspecialchars($estudiante['grupo_nombre']) : 'Sin grupo asignado'; ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-1"><strong>Total de asistencias:</strong> <?php echo $total_asistencias; ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Mi Código QR</h2>
                        <div class="qr-container">
                            <img id="qrImage" src="<?php echo $estudiante['qr_code']; ?>" alt="Código QR" class="img-fluid mb-3" style="max-width: 100%;">
                        </div>
                        <button id="btnDownloadQR" class="btn btn-success w-100">
                            <i class="fas fa-download"></i> Descargar QR
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Mis Asistencias</h2>
                        <div class="table-responsive">
                            <table id="tablaAsistencias" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Profesor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Las asistencias se cargarán con AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="mb-0">Sistema de Asistencia con QR &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Cargar asistencias del estudiante
            $.ajax({
                url: '../../includes/estudiantes/obtener_asistencias.php',
                type: 'GET',
                data: {
                    estudiante_id: <?php echo $estudiante_id; ?>,
                    for_student: true
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const asistencias = response.data || [];
                        let html = '';

                        if (asistencias.length > 0) {
                            asistencias.forEach(function(asistencia) {
                                // Separar la fecha y la hora
                                const fechaHora = new Date(asistencia.fecha_hora);
                                const fecha = fechaHora.toLocaleDateString('es-ES');
                                const hora = fechaHora.toLocaleTimeString('es-ES');

                                html += `
                                    <tr>
                                        <td>${fecha}</td>
                                        <td>${hora}</td>
                                        <td>${asistencia.profesor_nombre}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = '<tr><td colspan="3" class="text-center">No hay asistencias registradas</td></tr>';
                        }

                        $('#tablaAsistencias tbody').html(html);
                        $('#tablaAsistencias').DataTable({
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                            },
                            order: [[0, 'desc'], [1, 'desc']]
                        });
                    } else {
                        Swal.fire('Error', 'No se pudieron cargar las asistencias', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al cargar las asistencias', 'error');
                }
            });

            // Función para descargar el QR con fondo blanco y tamaño específico
            $('#btnDownloadQR').on('click', function() {
                // Obtener la imagen del QR
                const qrImage = document.getElementById('qrImage');
                
                // Crear un canvas para manipular la imagen
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Establecer el tamaño fijo para el QR (1080x1080)
                const targetSize = 1080;
                canvas.width = targetSize;
                canvas.height = targetSize;
                
                // Dibujar fondo blanco sólido
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, targetSize, targetSize);
                
                // Esperar a que la imagen esté cargada
                if (qrImage.complete) {
                    renderQR();
                } else {
                    qrImage.onload = renderQR;
                }
                
                function renderQR() {
                    // Calcular el tamaño y posición para centrar el QR
                    const originalSize = Math.min(qrImage.naturalWidth, qrImage.naturalHeight);
                    const padding = targetSize * 0.1; // 10% de padding
                    const size = targetSize - (padding * 2);
                    
                    // Dibujar el QR centrado
                    ctx.drawImage(
                        qrImage,
                        0, 0, originalSize, originalSize, // Source rectangle
                        padding, padding, size, size      // Destination rectangle
                    );
                    
                    // Mejorar contraste del QR (opcional)
                    const imageData = ctx.getImageData(0, 0, targetSize, targetSize);
                    const data = imageData.data;
                    
                    // Umbral para convertir a blanco y negro puro
                    const threshold = 200;
                    for (let i = 0; i < data.length; i += 4) {
                        // Si el pixel no es completamente blanco, hacerlo negro
                        const grayscale = (data[i] + data[i + 1] + data[i + 2]) / 3;
                        
                        if (grayscale < threshold) {
                            data[i] = 0;      // R
                            data[i + 1] = 0;  // G
                            data[i + 2] = 0;  // B
                            data[i + 3] = 255; // Alpha
                        } else {
                            data[i] = 255;    // R
                            data[i + 1] = 255;// G
                            data[i + 2] = 255;// B
                            data[i + 3] = 255;// Alpha
                        }
                    }
                    
                    ctx.putImageData(imageData, 0, 0);
                    
                    // Convertir a PNG y descargar
                    const pngUrl = canvas.toDataURL('image/png');
                    
                    // Crear link de descarga
                    const link = document.createElement('a');
                    link.href = pngUrl;
                    link.download = 'mi-qr-<?php echo $documento; ?>.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    </script>
</body>
</html> 