# OpenMind Plugin - WordPress

Plugin de gestión para psicólogos y pacientes con sistema de actividades, mensajería y bitácora personal.

## 📋 Requisitos

- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+

## 🚀 Instalación

1. **Clonar/Copiar el plugin:**
```bash
cd wp-content/plugins/
git clone [repo-url] openmind-plugin
# O descomprimir el ZIP en wp-content/plugins/
```

2. **Instalar dependencias:**
```bash
cd openmind-plugin
composer install
```

3. **Activar el plugin:**
    - Panel de WordPress → Plugins → Activar "OpenMind"
    - Esto creará automáticamente las tablas y roles necesarios

4. **Crear las páginas necesarias:**
    - Crear página: "Dashboard Psicólogo" (slug: `dashboard-psicologo`)
    - Crear página: "Dashboard Paciente" (slug: `dashboard-paciente`)

## 👥 Roles y Capabilities

### Psicólogo (psychologist)
- `manage_patients` - Gestionar pacientes
- `manage_activities` - Crear y asignar actividades

### Paciente (patient)
- `view_activities` - Ver actividades asignadas
- `write_diary` - Escribir en bitácora personal

## 🎯 Funcionalidades

### Para Psicólogos:
- ✅ Agregar pacientes (crea usuarios automáticamente)
- ✅ Ver lista de pacientes con estadísticas
- ✅ Crear actividades
- ✅ Asignar actividades a pacientes
- ✅ Sistema de mensajería con pacientes
- ✅ Ver actividades completadas

### Para Pacientes:
- ✅ Ver actividades asignadas
- ✅ Marcar actividades como completadas
- ✅ Escribir bitácora personal con estados de ánimo
- ✅ Mensajería con psicólogo asignado
- ✅ Filtrar actividades (pendientes/completadas)

## 🗄️ Estructura de Base de Datos

### Tablas Custom:

**wp_openmind_relationships**
- Relación psicólogo ↔ paciente

**wp_openmind_messages**
- Sistema de mensajería bidireccional

**wp_openmind_diary**
- Bitácora personal de pacientes

### Custom Post Type:

**activity**
- Actividades terapéuticas asignables

## 🔒 Seguridad

- ✅ Nonces en todos los formularios AJAX
- ✅ Sanitización de inputs
- ✅ Escapado de outputs
- ✅ Verificación de capabilities
- ✅ Prepared statements en queries

## 📁 Estructura del Proyecto

```
openmind-plugin/
├── composer.json
├── openmind.php (entry point)
├── src/
│   ├── Core/
│   │   ├── Plugin.php
│   │   └── Installer.php
│   ├── Controllers/
│   │   ├── ActivityController.php
│   │   ├── MessageController.php
│   │   ├── PatientController.php
│   │   └── DiaryController.php
│   └── Repositories/
│       ├── MessageRepository.php
│       └── DiaryRepository.php
├── templates/
│   ├── dashboard-psychologist.php
│   ├── dashboard-patient.php
│   └── components/
│       ├── header.php
│       ├── patient-card.php
│       ├── activity-card.php
│       └── diary-list.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
└── vendor/ (composer autoload)
```

## 🔧 Desarrollo

### Agregar nuevo endpoint AJAX:

1. Crear método en Controller:
```php
public static function tuMetodo(): void {
    check_ajax_referer('openmind_nonce', 'nonce');
    // Tu lógica
    wp_send_json_success(['data' => $resultado]);
}
```

2. Registrar acción en `init()`:
```php
add_action('wp_ajax_openmind_tu_accion', [self::class, 'tuMetodo']);
```

3. Llamar desde JS:
```javascript
const formData = new FormData();
formData.append('action', 'openmind_tu_accion');
formData.append('nonce', openmindData.nonce);

const response = await fetch(openmindData.ajaxUrl, {
    method: 'POST',
    body: formData
});
```

## 📝 TODO / Mejoras Futuras

- [ ] Modal para mensajería en tiempo real
- [ ] Notificaciones push
- [ ] Exportar bitácora a PDF
- [ ] Estadísticas y gráficos
- [ ] Sistema de recordatorios
- [ ] Videollamadas integradas
- [ ] App móvil
- [ ] Multi-idioma (i18n)

## 🐛 Troubleshooting

**Las páginas de dashboard no cargan:**
- Verificar que los slugs sean exactos: `dashboard-psicologo` y `dashboard-paciente`
- Ir a Ajustes → Enlaces permanentes y guardar

**Error 403 en AJAX:**
- Limpiar caché del navegador
- Verificar que el usuario tenga los capabilities correctos

**No se crean las tablas:**
- Desactivar y reactivar el plugin
- Verificar permisos de base de datos

## 📄 Licencia

MIT License - Uso libre

## 👨‍💻 Autor

[Tu Nombre]