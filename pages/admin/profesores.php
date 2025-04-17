<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Profesores</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Sistema de Asistencia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Profesores</h2>
            <div>
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importarProfesoresModal">
                    <i class="fas fa-file-import me-1"></i> Importar Profesores Masivamente
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoProfesor">
                    Nuevo Profesor
                </button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Buscar Profesor</h5>
                <div class="input-group">
                    <input type="text" id="documento_profesor" class="form-control" placeholder="Número de documento">
                    <button class="btn btn-primary" onclick="buscarProfesor()">Buscar</button>
                </div>
            </div>
        </div>

        <!-- Resultado de búsqueda -->
        <div id="resultadoBusqueda" class="card mb-4" style="display: none;">
            <div class="card-body">
                <h2 class="card-title mb-4">Información del Profesor</h2>
                <div class="row">
                    <div class="col-md-8">
                        <div id="datosProfesor"></div>
                        <div class="mt-3">
                            <button class="btn btn-primary me-2" id="btnEditarProfesor">Editar Profesor</button>
                            <button class="btn btn-danger" id="btnEliminarProfesor">Eliminar Profesor</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaProfesores" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Correo</th>
                                <th>Documento</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $db = new Database();
                            $conn = $db->connect();
                            
                            $stmt = $conn->prepare("
                                SELECT id, nombre, apellidos, correo, documento, created_at 
                                FROM usuarios 
                                WHERE rol_id = 2
                                ORDER BY created_at DESC
                            ");
                            $stmt->execute();
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>{$row['nombre']}</td>";
                                echo "<td>{$row['apellidos']}</td>";
                                echo "<td>{$row['correo']}</td>";
                                echo "<td>{$row['documento']}</td>";
                                echo "<td>{$row['created_at']}</td>";
                                echo "<td>
                                        <button class='btn btn-info btn-sm btn-editar' data-id='{$row['id']}'>Editar</button>
                                        <button class='btn btn-danger btn-sm btn-eliminar' data-id='{$row['id']}'>Eliminar</button>
                                    </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Profesor -->
    <div class="modal fade" id="modalNuevoProfesor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Profesor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoProfesor">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarProfesor()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Profesor -->
    <div class="modal fade" id="modalEditarProfesor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Profesor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarProfesor">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" id="edit_apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="correo" id="edit_correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" id="edit_documento" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarProfesor()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Importar Profesores -->
    <div class="modal fade" id="importarProfesoresModal" tabindex="-1" aria-labelledby="importarProfesoresModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importarProfesoresModalLabel">Importación Masiva de Profesores</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Suba un archivo Excel (.xlsx) o CSV para importar múltiples profesores a la vez. El archivo debe tener las siguientes columnas en este orden:</p>
                    <ol>
                        <li><strong>Nombre</strong> (obligatorio)</li>
                        <li><strong>Apellidos</strong> (obligatorio)</li>
                        <li><strong>Correo</strong> (obligatorio)</li>
                        <li><strong>Documento</strong> (obligatorio - se usará como contraseña inicial)</li>
                    </ol>
                    <p>La primera fila debe contener los encabezados y será ignorada durante la importación.</p>
                    <p class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Nota:</strong> El número de documento se usará como contraseña inicial para cada profesor. Los profesores podrán cambiar su contraseña después de iniciar sesión.
                    </p>
                    <form id="form-importar-profesores" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Archivo de Profesores (Excel o CSV)</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".csv, .xlsx" required>
                        </div>
                    </form>
                    <div id="importar-resultado" class="alert alert-info d-none">
                        <p id="importar-mensaje"></p>
                        <div id="importar-detalles" class="mt-2 d-none">
                            <button class="btn btn-sm btn-outline-info mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseErrores" aria-expanded="false" aria-controls="collapseErrores">
                                Ver detalles de errores
                            </button>
                            <div class="collapse" id="collapseErrores">
                                <div class="card card-body">
                                    <ul id="lista-errores" class="mb-0"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="form-importar-profesores">Importar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Definir la traducción directamente en lugar de cargarla de un archivo externo
            const dataTableEspanol = {
                "decimal": "",
                "emptyTable": "No hay datos disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar columna ascendente",
                    "sortDescending": ": activar para ordenar columna descendente"
                }
            };

            // Inicializar DataTable con configuración completa
            const table = $('#tablaProfesores').DataTable({
                language: dataTableEspanol,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 10,
                responsive: true,
                order: [[5, 'desc']], // Ordenar por fecha de creación descendente por defecto
                columnDefs: [
                    { className: "text-center", targets: [0, 6] } // Centrar ID y columna de acciones
                ]
            });
            
            // Manejar el envío del formulario de nuevo profesor
            $('#formNuevoProfesor').on('submit', function(e) {
                e.preventDefault();
                guardarProfesor();
            });

            // Editar profesor
            $('.btn-editar').click(function() {
                const id = $(this).data('id');
                editarProfesor(id);
            });

            // Eliminar profesor
            $('.btn-eliminar').click(function() {
                const id = $(this).data('id');
                eliminarProfesor(id);
            });
            
            // Detectar Enter en el campo de búsqueda
            $('#documento_profesor').on('keypress', function(e) {
                if (e.which === 13) { // Código para la tecla Enter
                    e.preventDefault();
                    buscarProfesor();
                }
            });
        });

        function buscarProfesor() {
            const documento = $('#documento_profesor').val();
            if (!documento) {
                Swal.fire('Error', 'Por favor ingrese un número de documento', 'error');
                return;
            }

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Buscando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                },
                timer: 1000
            });

            // Realizar la búsqueda
            $.get('../../includes/profesores/buscar.php', { documento: documento })
                .done(function(response) {
                    Swal.close();

                    if (response.success) {
                        const profesor = response.profesor;
                        
                        // Mostrar los datos del profesor
                        $('#datosProfesor').html(`
                            <div class="mb-3">
                                <h4>Nombre: ${profesor.nombre} ${profesor.apellidos}</h4>
                                <h4>Documento: ${profesor.documento}</h4>
                                <h4>Correo: ${profesor.correo}</h4>
                                <h4>Fecha de creación: ${profesor.created_at}</h4>
                            </div>
                        `);
                        
                        // Mostrar el panel de resultados
                        $('#resultadoBusqueda').show();
                        
                        // Configurar botón de editar
                        $('#btnEditarProfesor').off('click').on('click', function() {
                            editarProfesor(profesor.id);
                        });
                        
                        // Configurar botón de eliminar
                        $('#btnEliminarProfesor').off('click').on('click', function() {
                            eliminarProfesor(profesor.id);
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        $('#resultadoBusqueda').hide();
                    }
                })
                .fail(function(jqXHR) {
                    Swal.close();
                    console.error('Error en la búsqueda:', jqXHR.responseText);
                    Swal.fire('Error', 'Error al buscar profesor', 'error');
                    $('#resultadoBusqueda').hide();
                });
        }

        function guardarProfesor() {
            const formData = new FormData($('#formNuevoProfesor')[0]);
            
            // Validar campos
            const nombre = formData.get('nombre').trim();
            const apellidos = formData.get('apellidos').trim();
            const correo = formData.get('correo').trim();
            const documento = formData.get('documento').trim();
            const password = formData.get('password');
            const confirm_password = formData.get('confirm_password');

            if (!nombre || !apellidos || !correo || !documento || !password || !confirm_password) {
                Swal.fire('Error', 'Todos los campos son requeridos', 'error');
                return;
            }

            if (!/^\d+$/.test(documento)) {
                Swal.fire('Error', 'El documento debe contener solo números', 'error');
                return;
            }

            if (password.length < 6) {
                Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }

            if (password !== confirm_password) {
                Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                return;
            }

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar datos al servidor
            $.ajax({
                url: '../../includes/profesores/crear.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message
                        }).then(() => {
                            $('#modalNuevoProfesor').modal('hide');
                            $('#formNuevoProfesor')[0].reset();
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error en la petición:', xhr);
                    let mensaje = 'Error al guardar profesor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }

        function editarProfesor(id) {
            $.get('../../includes/profesores/obtener.php', { id: id })
                .done(function(response) {
                    if (response.success) {
                        $('#edit_id').val(response.profesor.id);
                        $('#edit_nombre').val(response.profesor.nombre);
                        $('#edit_apellidos').val(response.profesor.apellidos);
                        $('#edit_correo').val(response.profesor.correo);
                        $('#edit_documento').val(response.profesor.documento);
                        $('#modalEditarProfesor').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Error al obtener datos del profesor', 'error');
                });
        }

        function actualizarProfesor() {
            const formData = new FormData($('#formEditarProfesor')[0]);
            
            // Validar campos requeridos
            const nombre = formData.get('nombre').trim();
            const apellidos = formData.get('apellidos').trim();
            const correo = formData.get('correo').trim();
            const documento = formData.get('documento').trim();
            const password = formData.get('password');
            const confirm_password = formData.get('confirm_password');

            if (!nombre || !apellidos || !correo || !documento) {
                Swal.fire('Error', 'Los campos nombre, apellidos, correo y documento son requeridos', 'error');
                return;
            }

            if (!/^\d+$/.test(documento)) {
                Swal.fire('Error', 'El documento debe contener solo números', 'error');
                return;
            }

            if (password) {
                if (password.length < 6) {
                    Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
                    return;
                }

                if (password !== confirm_password) {
                    Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                    return;
                }
            }

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Actualizando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '../../includes/profesores/editar.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            $('#modalEditarProfesor').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error en la petición:', xhr);
                    let mensaje = 'Error al actualizar el profesor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }

        function eliminarProfesor(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('../../includes/profesores/eliminar.php', { id: id })
                        .done(function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        })
                        .fail(function() {
                            Swal.fire('Error', 'Error al eliminar el profesor', 'error');
                        });
                }
            });
        }

        $('#form-importar-profesores').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Mostrar barra de progreso
            $('#importar-resultado').removeClass('d-none alert-danger').addClass('alert-info');
            $('#importar-mensaje').text('Cargando...');
            $('#importar-detalles').addClass('d-none');
            $('#lista-errores').empty();
            
            $.ajax({
                url: '../../includes/profesores/importar_excel.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Ocultar barra de progreso
                    $('#importar-resultado').removeClass('d-none');
                    
                    if (response.success) {
                        // Mostrar resultado exitoso
                        $('#importar-resultado').removeClass('alert-danger').addClass('alert-info');
                        $('#importar-mensaje').text(response.message);
                        
                        // Mostrar errores si existen
                        if (response.errores && response.errores.length > 0) {
                            $('#importar-detalles').removeClass('d-none');
                            // Llenar la lista de errores
                            response.errores.forEach(function(error) {
                                $('#lista-errores').append(`<li>${error}</li>`);
                            });
                        } else {
                            $('#importar-detalles').addClass('d-none');
                        }
                        
                        // Cambiar botones del modal
                        $('.modal-footer').html(`
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" onclick="location.reload()">Cerrar y Recargar</button>
                        `);
                        
                        // NO recargamos automáticamente para que el admin pueda ver el informe
                        // setTimeout(function() {
                        //     window.location.reload();
                        // }, 3000);
                    } else {
                        // Mostrar error
                        $('#importar-resultado').removeClass('alert-info').addClass('alert-danger');
                        $('#importar-mensaje').text(response.message);
                        $('#importar-detalles').addClass('d-none');
                    }
                },
                error: function(xhr) {
                    // Ocultar barra de progreso
                    $('#importar-resultado').removeClass('d-none').removeClass('alert-info').addClass('alert-danger');
                    
                    // Mostrar error
                    let message = 'Error en la importación';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    } catch (e) {
                        // Si no podemos parsear la respuesta, usamos el mensaje genérico
                    }
                    
                    $('#importar-mensaje').text(message);
                    $('#importar-detalles').addClass('d-none');
                }
            });
        });
    </script>
</body>
</html> 