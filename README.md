# QRSchool: Sistema de Control de Asistencia Mediante Códigos QR

## Descripción General

**QRSchool** es una solución integral para la gestión de asistencia educativa mediante tecnología de códigos QR. Esta plataforma optimiza el registro de asistencia, eliminando procesos manuales y reduciendo el margen de error humano.

El sistema permite a los docentes registrar la presencia de estudiantes mediante el escaneo de códigos QR únicos, generando automáticamente registros digitales precisos y actualizados en tiempo real.

## Segmentos de Usuarios

### Cuerpo Docente
- Profesionales educativos que requieren eficiencia en el registro de asistencia
- Docentes que necesitan mantener registros precisos y de fácil acceso
- Educadores interesados en la implementación de soluciones tecnológicas en el aula

### Personal Administrativo
- Directivos que requieren reportes de asistencia automáticos y centralizados
- Coordinadores académicos que supervisan métricas de asistencia institucional
- Administrativos que necesitan datos concretos sobre la presencia estudiantil

### Estudiantes
- Alumnos que se benefician de un proceso de registro de asistencia simplificado
- Estudiantes que requieren acceso a su historial de asistencia personal
- Participantes del sistema educativo que utilizan su dispositivo móvil como herramienta académica

## Características Principales

### Sistema de Usuarios Jerarquizado
- **Administradores**: Control total del sistema y configuración institucional
- **Profesores**: Gestión de asistencia y acceso a reportes específicos
- **Estudiantes**: Acceso a información personal y registro de asistencias

### Portal para Estudiantes
- Autenticación mediante número de documento (sin necesidad de contraseña)
- Visualización de información personal y académica
- Descarga de código QR personal en formato estándar (1080×1080 píxeles)
- Consulta de historial completo de asistencias

### Escáner QR de Alto Rendimiento
- Reconocimiento inmediato de códigos QR
- Compatibilidad con múltiples dispositivos y condiciones de iluminación

### Sistema de Reportes Avanzados
- Generación de informes detallados por múltiples parámetros
- Visualización de datos estadísticos con opciones de exportación

### Módulo de Importación Masiva
- Sistema para carga masiva de datos estudiantiles
- Compatibilidad con formatos estándar de hojas de cálculo

## Requisitos de Implementación

### Requisitos Técnicos
- PHP 7.4 o superior
- MySQL/MariaDB
- Extensiones PHP: mbstring, xml, zip
- Servidor Apache/Nginx
- Infraestructura con estabilidad operativa

### Proceso de Instalación
1. **Obtener el repositorio**:
   ```bash
   git clone https://github.com/su-organizacion/qrschool.git
   ```

2. **Instalar dependencias**:
   ```bash
   composer install
   ```

3. **Configurar base de datos**:
   - Crear una base de datos en MySQL
   - Importar el archivo `gestion.sql`
   - Configurar el archivo `.env` con los parámetros de conexión:
     ```
     DB_SERVER=localhost
     DB_USERNAME=[usuario]
     DB_PASSWORD=[contraseña]
     DB_NAME=[nombre_base_datos]
     ```

4. **Configurar el servidor web** para que apunte al directorio del proyecto

5. **Acceder a la aplicación** para comenzar la configuración institucional

## Verificación del Sistema

### Credenciales predeterminadas:
- **Administrador**: 
  - Correo electrónico: scharss@gmail.com
  - Contraseña: xxxxxxx

### Acceso para estudiantes:
- Utilizar el número de documento del estudiante registrado en el sistema

## Colaboración y Desarrollo

La contribución al desarrollo del sistema se gestiona mediante un proceso estructurado de solicitudes de incorporación (pull requests), sujetas a los siguientes criterios:

### Directrices de contribución:
1. **Calidad del código**: Debe cumplir con los estándares de codificación establecidos
2. **Documentación**: Toda contribución debe estar adecuadamente documentada
3. **Pruebas**: Se valorará la implementación de pruebas unitarias y de integración

## Desarrollo Futuro

El plan de desarrollo incluye las siguientes implementaciones programadas:

- **Aplicación móvil** para facilitar el acceso desde dispositivos portátiles
- **Autenticación biométrica** para incrementar la seguridad del sistema
- **Integración con plataformas de calendario** para sincronización de eventos
- **Implementación de inteligencia artificial** para análisis predictivo de asistencia

## Consideraciones Finales

El sistema QRSchool representa una solución tecnológica orientada a optimizar procesos educativos fundamentales. La implementación de este sistema permite a las instituciones educativas modernizar sus procedimientos administrativos y concentrar recursos en su misión educativa principal.

## Información Legal

Este proyecto se distribuye bajo la licencia MIT, que permite su uso, modificación y distribución bajo ciertas condiciones específicas detalladas en el archivo de licencia.

---

*Desarrollado por scharss@gmail.com Todos los derechos reservados.*

