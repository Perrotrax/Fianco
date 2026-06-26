# 🎨 Gestor de Gastos - Diseño Moderno v2.0

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Version](https://img.shields.io/badge/Version-2.0-green.svg)](CHANGELOG.md)

Sistema moderno de gestión de gastos personales con diseño profesional, SweetAlerts2 integrado y UI/UX mejorada.

## ✨ Características Principales

### 🎨 Diseño Moderno
- **Tema Dark Profesional** - Colores modernos y atractivos
- **Componentes Reutilizables** - Cards, Botones, Inputs, Tablas, Badges
- **Responsive Design** - Funciona perfectamente en móvil, tablet y desktop
- **Animaciones Suaves** - Transiciones y efectos visuales profesionales
- **Accesibilidad** - Diseño pensado en todos los usuarios

### 🔔 SweetAlerts2 Integrado
- **Alertas Visuales** - Reemplaza los alert() básicos
- **Confirmaciones Elegantes** - Para acciones críticas
- **Loading States** - Indica operaciones en progreso
- **Input Dinámicos** - Formularios dentro de alertas
- **Toast Notifications** - Notificaciones sin bloqueo
- **Animaciones Fluidas** - Entrada y salida elegante

### 🚀 Performance
- **CSS Optimizado** - Variables y estructura limpia
- **JavaScript Eficiente** - Funciones reutilizables
- **CDN Integrado** - Librerías desde jsDelivr
- **Carga Rápida** - Optimizado para velocidad

## 📂 Estructura de Archivos

```
gestor-gastos/
├── css/
│   ├── style.css              # Estilos principales modernos
│   ├── sweetalert.css         # Personalización de SweetAlert2
│   └── admin.css              # Estilos del panel admin
├── js/
│   ├── app.js                 # Funciones globales mejoradas
│   ├── dashboard.js           # Lógica del dashboard
│   ├── gastos.js              # Gestión de gastos
│   └── ...
├── panel/
│   ├── index.php              # Panel administrativo
│   ├── js/
│   │   ├── app.js             # Funciones del panel con SweetAlerts
│   │   ├── dashboard.js
│   │   ├── users.js
│   │   └── ...
│   └── css/
├── api/
│   ├── login.php
│   ├── registro.php
│   ├── add_gasto.php
│   └── ...
├── index.php                  # Página de login mejorada
├── dashboard.php              # Dashboard principal
├── DISEÑO_MODERNO.md          # Documentación del diseño
├── EJEMPLOS_USO.js            # Ejemplos prácticos
└── README.md                  # Este archivo
```

## 🎯 Colores & Variables

### Paleta Principal
```css
--primary: #6366f1      /* Indigo */
--secondary: #8b5cf6    /* Violeta */
--success: #10b981      /* Verde */
--danger: #ef4444       /* Rojo */
--warning: #f59e0b      /* Naranja */
--info: #3b82f6         /* Azul */
```

### Fondos
```css
--bg-body: #0f172a      /* Azul muy oscuro */
--bg-card: #1e293b      /* Azul oscuro */
--bg-light: #334155     /* Gris oscuro */
--bg-lighter: #475569   /* Gris más claro */
```

### Textos
```css
--text-primary: #f1f5f9     /* Blanco azulado */
--text-secondary: #cbd5e1   /* Gris claro */
--text-muted: #94a3b8       /* Gris medio */
```

## 🎯 Componentes

### Botones

```html
<!-- Botón Primario -->
<button class="btn btn-primary">Enviar</button>

<!-- Botón Success -->
<button class="btn btn-success">Confirmar</button>

<!-- Botón Danger -->
<button class="btn btn-danger">Eliminar</button>

<!-- Botón Warning -->
<button class="btn btn-warning">Advertencia</button>

<!-- Botón Secundario -->
<button class="btn btn-secondary">Secundario</button>

<!-- Botón Texto -->
<button class="btn btn-text">Solo Texto</button>

<!-- Tamaños -->
<button class="btn btn-primary btn-sm">Pequeño</button>
<button class="btn btn-primary">Normal</button>
<button class="btn btn-primary btn-lg">Grande</button>

<!-- Full Width -->
<button class="btn btn-primary btn-block">Ancho Completo</button>
```

### Tarjetas

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Título de la Tarjeta</h3>
        <span class="card-subtitle">Subtítulo</span>
    </div>
    <div class="card-body">
        Contenido principal
    </div>
    <div class="card-footer">
        Pie de página
    </div>
</div>
```

### Grid

```html
<!-- Grid de 2 columnas -->
<div class="grid grid-2">
    <div>Item 1</div>
    <div>Item 2</div>
</div>

<!-- Grid de 3 columnas -->
<div class="grid grid-3">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>

<!-- Grid de 4 columnas -->
<div class="grid grid-4">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
    <div>Item 4</div>
</div>
```

### Badges

```html
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Success</span>
<span class="badge badge-danger">Danger</span>
<span class="badge badge-warning">Warning</span>
```

### Alertas

```html
<div class="alert alert-success">✓ Operación exitosa</div>
<div class="alert alert-danger">✗ Error en la operación</div>
<div class="alert alert-warning">⚠ Advertencia importante</div>
<div class="alert alert-primary">ℹ Información</div>
```

## 💻 JavaScript Functions

### Alertas

```javascript
// Éxito
showSuccess('¡Exitoso!', 'La operación se completó', 2000);

// Error
showError('Error', 'Algo salió mal');

// Advertencia
showWarning('Advertencia', 'Por favor verifica');

// Información
showInfo('Información', 'Debes saber esto');

// Confirmación
confirmAction(
    '¿Estás seguro?',
    'Esta acción es irreversible',
    () => {
        // Callback si confirma
        console.log('Confirmado');
    }
);
```

### Validación

```javascript
// Validar email
if (validarEmail('usuario@ejemplo.com')) {
    console.log('Email válido');
}

// Validar password (mínimo 6 caracteres)
if (validarPassword('miPassword123')) {
    console.log('Password válido');
}
```

### API Calls

```javascript
// Fetch con alertas automáticas
const data = await fetchWithAlert(
    'api/get_gastos.php',
    { method: 'GET' },
    true  // Mostrar loading
);

if (data) {
    console.log(data.gastos);
}
```

### Toast Notifications

```javascript
// Toast en esquina inferior derecha
const Toast = Swal.mixin({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

Toast.fire({
    icon: 'success',
    title: 'Gasto agregado correctamente'
});
```

## 📱 Responsive Breakpoints

```css
/* Desktop */
@media (min-width: 1024px) { }

/* Tablet */
@media (max-width: 768px) { }

/* Mobile */
@media (max-width: 480px) { }
```

## 🚀 Cómo Empezar

### 1. Incluir en tu HTML

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Estilos Modernos -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/sweetalert.css">
</head>
<body>
    <!-- Tu contenido aquí -->
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
```

### 2. Usar Componentes

```html
<!-- Botón con SweetAlert -->
<button class="btn btn-primary" onclick="showSuccess('¡Éxito!', 'Operación completada')">
    Click aquí
</button>

<!-- Tarjeta con contenido -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mis Gastos</h3>
    </div>
    <div class="card-body">
        <p>Contenido del gasto aquí</p>
    </div>
</div>
```

## 🎨 Personalización

### Cambiar Colores

Edita las variables CSS en `css/style.css`:

```css
:root {
    --primary: #tu-color-aqui;
    --secondary: #otro-color;
    /* ... más variables ... */
}
```

### Crear Nuevo Botón

```html
<button class="btn" style="background: linear-gradient(135deg, #custom1, #custom2);">
    Botón Personalizado
</button>
```

### Personalizar SweetAlert

```javascript
const customConfig = {
    confirmButtonColor: '#tu-color',
    cancelButtonColor: '#otro-color',
    confirmButtonText: 'Tu Texto',
    customClass: {
        popup: 'tu-clase',
        confirmButton: 'tu-btn'
    }
};

Swal.fire({
    title: 'Título',
    text: 'Mensaje',
    ...customConfig
});
```

## 📚 Documentación Completa

- 📖 [Diseño Moderno](DISEÑO_MODERNO.md) - Guía detallada del sistema
- 💡 [Ejemplos de Uso](EJEMPLOS_USO.js) - 12+ ejemplos prácticos
- 🎨 [Colores & Variables](css/style.css) - Sistema de variables CSS
- 🔧 [Funciones JavaScript](panel/js/app.js) - Funciones reutilizables

## 🔗 Librerías Utilizadas

- **SweetAlert2** - Alertas visuales hermosas
  - CDN: `https://cdn.jsdelivr.net/npm/sweetalert2@11`
  - Docs: https://sweetalert2.github.io

- **Chart.js** - Gráficos y estadísticas
  - CDN: `https://cdn.jsdelivr.net/npm/chart.js`
  - Docs: https://www.chartjs.org

- **Google Fonts** - Tipografía (Outfit)
  - URL: https://fonts.google.com

## 🌟 Características Futuras

- [ ] Tema Light/Dark Toggle
- [ ] Más Animaciones
- [ ] Sistema de Notificaciones en Tiempo Real
- [ ] Exportar Datos a PDF/Excel
- [ ] Gráficos Avanzados
- [ ] Análisis Predictivo
- [ ] Integración Biométrica
- [ ] API REST Completa

## 📊 Estado del Proyecto

| Componente | Estado |
|-----------|--------|
| CSS Moderno | ✅ Completado |
| SweetAlerts | ✅ Completado |
| Botones | ✅ Completado |
| Tarjetas | ✅ Completado |
| Formularios | ✅ Completado |
| Validación | ✅ Completado |
| Responsive | ✅ Completado |
| Animaciones | ✅ Completado |

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autor

**Equipo de Desarrollo Gestor de Gastos**

## 🙏 Agradecimientos

- SweetAlert2 por las alertas visuales
- Google Fonts por la tipografía
- Chart.js por los gráficos
- Comunidad de desarrollo

## 📞 Soporte

¿Tienes preguntas o necesitas ayuda?

- 📧 Email: soporte@gestordegastos.com
- 💬 Chat: Disponible en el sitio web
- 🐛 Issues: Abre una issue en el repositorio

---

**Versión:** 2.0  
**Última actualización:** 2024  
**Estado:** Producción ✅

Hecho con ❤️ para una mejor gestión de finanzas personales.
