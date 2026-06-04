# 🧭 Excursiones — Plugin WordPress
Plugin de gestión completa de excursiones para WordPress. Permite publicar y administrar excursiones con reservas online, control de pasajeros, paradas de autobús y panel de gestión frontend integrado.

---

## ✨ Características principales
- **Catálogo público** de excursiones con tarjetas visuales, imagen destacada, precio, fecha de salida, duración y ubicación
- **Filtrado y búsqueda** en tiempo real: por nombre, tipo de excursión (taxonomía) y orden de precio ascendente/descendente
- **Formulario de reserva** por excursión: plazas, fecha, parada de autobús, datos de hotel y número de habitación, punto de recogida adicional y titular
- **Historial de reservas** para el cliente mediante shortcode `[mis_reservas]`
- **Panel de administración frontend** (sin necesidad de acceder al backend de WordPress) con tres secciones: crear excursión, ver/gestionar reservas y configurar paradas de autobús
- **Gestión de estados** de reserva: pendiente, confirmada o cancelada, directamente desde el listado
- **Subida de imagen de portada** desde el panel frontend
- **Custom Post Types** propios para excursiones y reservas, con metadatos protegidos y nonces en todos los formularios

---

## 📁 Estructura del plugin
```
2026-excursiones/
├── excursiones.php              # Archivo principal, constantes y carga de módulos
├── includes/
│   ├── cpt.php                  # Registro de CPTs: excursiones y reservas
│   ├── taxonomias.php           # Taxonomía jerárquica: tipo_excursion
│   ├── metabox.php              # Metaboxes de admin para excursiones y reservas
│   └── frontend.php            # Toda la lógica de cara al usuario
├── templates/
│   └── archive-excursiones.php  # Plantilla del archivo/catálogo de excursiones
└── assets/
    ├── css/excursiones.css      # Estilos (fuentes Cormorant Garamond + Outfit)
    └── js/excursiones-filters.js # Filtrado y búsqueda en tiempo real (vanilla JS)
```

---

## 🚀 Instalación
1. Descarga o clona el repositorio dentro de `/wp-content/plugins/`
2. Ve a **WordPress Admin → Plugins → Plugins instalados**
3. Activa **Excursiones**
4. Crea las páginas necesarias y añade los shortcodes correspondientes (ver abajo)

---

## ⚙️ Configuración

### Páginas y shortcodes

| Página               | Shortcode                        | Descripción                              |
|----------------------|----------------------------------|------------------------------------------|
| Mis Reservas         | `[mis_reservas]`                 | Historial de reservas del cliente logado |
| Panel de Gestión     | `[dashboard_admin_excursiones]`  | Panel frontend exclusivo para admins     |

El catálogo de excursiones es accesible automáticamente en `/excursiones/` gracias al archivo con `has_archive = true`.

### Paradas de autobús

Desde el panel frontend → pestaña **Rutas y Paradas**, el administrador puede añadir o eliminar las paradas de recogida disponibles. Estas paradas aparecen como selector obligatorio en el formulario de reserva de cada excursión.

---

## 📖 Cómo funciona

### Para los clientes

1. Navegan al catálogo de excursiones y usan los filtros por tipo o búsqueda por nombre
2. Hacen clic en **Reservar plaza** en la tarjeta de la excursión que les interesa
3. Completan el formulario (pasajeros, fecha, parada de autobús, datos de hotel, nombre y teléfono)
4. El sistema calcula el total en tiempo real y guarda la reserva
5. Pueden consultar su historial en la página con `[mis_reservas]`

### Para los administradores

Acceden a la página con `[dashboard_admin_excursiones]` y disponen de tres pestañas:

- **Crear Excursión**: título, precio, plazas máximas, fecha de salida, duración, ubicación e imagen de portada
- **Ver Reservas**: listado completo con referencia, titular, teléfono, excursión, fecha, pasajeros, datos de recogida y alojamiento, importe total y estado. Permite cambiar el estado o eliminar la reserva directamente
- **Rutas y Paradas**: gestión de las paradas de autobús disponibles para los formularios de reserva

---

## 🔒 Seguridad
- Verificación de nonces en todos los formularios
- Capacidades WordPress comprobadas antes de cada acción (`manage_options`, `is_user_logged_in`)
- Sanitización de todos los inputs del usuario
- Las reservas son privadas (`public => false`), no accesibles desde el frontend directamente

---

## 🗺️ Hoja de ruta — Próximas funcionalidades
- [ ] **Exportación a PDF** — Confirmación de reserva descargable con el resumen completo (excursión, pasajeros, importe, parada asignada)
- [ ] **Contacto del vendedor/guía** — Asignar un responsable a cada excursión con nombre, teléfono y email visible en la ficha y en la reserva
- [ ] **Notificaciones por email** — Confirmación automática al cliente y aviso al administrador en cada nueva reserva
- [ ] **Control de aforo en tiempo real** — Mostrar plazas disponibles restantes y bloquear el formulario cuando se alcanza el máximo
- [ ] **Panel de estadísticas** — Ingresos por excursión, ocupación media, reservas por período
- [ ] - [ ] **Valoraciones y reseñas**


---

## 📋 Requisitos

- WordPress 5.8 o superior
- PHP 7.4 o superior
- Tema compatible con `get_header()` / `get_footer()` para la plantilla de archivo

---

*Versión 1.1 — 2026*
