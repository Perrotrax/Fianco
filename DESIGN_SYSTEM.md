# 🎨 SISTEMA DE DISEÑO - TICKELIA PREMIUM 2026

## Propuesta Visual Completa - UX/UI Profesional Fintech

---

## 1. FILOSOFÍA DE DISEÑO

### Principios Centrales
- **Minimalismo Sofisticado**: Menos es más - cada elemento tiene propósito
- **Dark-First**: Reduce fatiga ocular (preferencia en fintech moderno)
- **Micro-interacciones**: Feedback visual en cada acción
- **Accesibilidad WCAG AA+**: Contraste mínimo 4.5:1 para textos
- **Espaciado Generoso**: 8px base grid para claridad visual
- **Tipografía Clara**: Jerarquía visual evidente
- **Transiciones Suaves**: 200-300ms para fluidez

### Inspiración (Apps Referencia)
- **Notion**: Minimalismo limpio, espaciado generoso
- **Linear**: Dark mode profesional, tipografía nítida
- **Stripe**: Sofisticación y confianza
- **Revolut**: Modernidad con claridad
- **Wise**: Accesibilidad y funcionalidad

---

## 2. PALETA DE COLORES

### Dark Mode (Primario para Fintech)
```
Fondos:
  - bg-pure:      #0A0A0A  (negro puro - OLED friendly)
  - bg-primary:   #111111  (contenedores principales)
  - bg-secondary: #1A1A1A  (elementos secundarios)
  - bg-tertiary:  #252525  (hover states)
  - bg-elevated:  #2D2D2D  (modals, popovers)

Textos:
  - text-primary:   #F5F5F5 (99% white - menos quema visual)
  - text-secondary: #E0E0E0 (87% white - secundario)
  - text-tertiary:  #A8A8A8 (65% white - muted)
  - text-disabled:  #757575 (45% white)

Accents (Primarios):
  - accent-blue:    #3B82F6 (principal)
  - accent-green:   #10B981 (éxito, ingresos)
  - accent-amber:   #F59E0B (advertencia, presupuesto)
  - accent-red:     #EF4444 (error, gastos)

Grises (Neutros):
  - neutral-50:     #FAFAFA
  - neutral-100:    #F3F4F6
  - neutral-200:    #E5E7EB
  - neutral-300:    #D1D5DB
  - neutral-400:    #9CA3AF
  - neutral-500:    #6B7280
  - neutral-600:    #4B5563
  - neutral-700:    #374151
  - neutral-800:    #1F2937
  - neutral-900:    #111827

Bordes:
  - border-light:   #2D2D2D (líneas sutiles)
  - border-medium:  #3F3F3F (líneas normales)
  - border-strong:  #4F4F4F (líneas prominentes)
```

### Light Mode (Alternativo)
```
Fondos:
  - bg-pure:      #FFFFFF
  - bg-primary:   #F9FAFB
  - bg-secondary: #F3F4F6
  - bg-tertiary:  #E5E7EB
  - bg-elevated:  #FFFFFF

Textos:
  - text-primary:   #0F172A
  - text-secondary: #1E293B
  - text-tertiary:  #64748B

Bordes:
  - border-light:   #E2E8F0
  - border-medium:  #CBD5E1
  - border-strong:  #94A3B8
```

### Semántica de Colores
```
Success:  #10B981 - Ingresos confirmados, transacciones aprobadas
Warning:  #F59E0B - Presupuesto cerca del límite, avisos
Error:    #EF4444 - Gastos excesivos, errores
Info:     #3B82F6 - Información general
Neutral:  #6B7280 - Elementos secundarios
```

---

## 3. TIPOGRAFÍA

### Fuentes Recomendadas
```
Primaria: "Inter", -apple-system, BlinkMacSystemFont, sans-serif
  - Razón: Moderna, legible, amplia soporte de pesos (100-900)
  - Alternativa: "Geist", "Outfit", "Segoe UI"

Monoespaciada: "JetBrains Mono", "Fira Code"
  - Uso: Números, códigos, valores exactos
```

### Escala de Tipografía (Base: 16px)
```
Display XL:   48px / 1.2 / 700 (hero titles)
Display L:    40px / 1.2 / 700 (page titles)
Display M:    32px / 1.3 / 700 (section titles)
Display S:    24px / 1.3 / 600 (subsection titles)

Heading XL:   20px / 1.4 / 700 (cards headers)
Heading L:    18px / 1.4 / 600 (subheaders)
Heading M:    16px / 1.5 / 600 (labels)
Heading S:    14px / 1.5 / 600 (small labels)

Body XL:      18px / 1.6 / 400 (large text)
Body L:       16px / 1.6 / 400 (default text)
Body M:       14px / 1.5 / 400 (secondary text)
Body S:       12px / 1.5 / 400 (small text)

Caption:      11px / 1.4 / 500 (captions)
```

### Font Weights
```
Regular:  400  - textos normales
Medium:   500  - labels, secondary buttons
Semibold: 600  - headers pequeños, énfasis
Bold:     700  - titles, main buttons
```

---

## 4. SISTEMA DE ESPACIADO (8px Base Grid)

```
xs:  4px    - líneas muy pequeñas
sm:  8px    - padding botones, gaps pequeños
md:  16px   - padding cards, gaps normales
lg:  24px   - padding sections, gaps grandes
xl:  32px   - padding container
2xl: 48px   - gaps entre sections principales
3xl: 64px   - padding body
4xl: 96px   - gaps máximos
```

---

## 5. SOMBRAS Y ELEVACIÓN

```
Subtle:   0 1px 2px 0 rgba(0,0,0,0.05)
Light:    0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06)
Medium:   0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)
Strong:   0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)
Deep:     0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)
```

---

## 6. BORDES Y RADIOS

```
Radios:
  sm:  4px
  md:  8px   - default
  lg:  12px
  xl:  16px
  2xl: 20px
  full: 9999px

Ancho Bordes:
  1px - por defecto
  2px - emphasis (selected)
  3px - focus states
```

---

## 7. TRANSICIONES

```
Fade:        opacity 200ms cubic-bezier(0.4, 0, 0.2, 1)
Scale:       transform 200ms cubic-bezier(0.4, 0, 0.2, 1)
Slide:       transform 300ms cubic-bezier(0.34, 1.56, 0.64, 1)
Elevation:   box-shadow 200ms cubic-bezier(0.4, 0, 0.2, 1)
```

---

## 8. COMPONENTES PRINCIPALES

### 8.1 HEADER SUPERIOR
```
Height:     64px (responsive 56px en mobile)
Posición:   Sticky top
Estructura: Logo | Search | Actions
Características:
  - Borde inferior sutil (1px)
  - Fondo semi-transparente + blur (backdrop-filter)
  - Altura consistente en scroll
  - User menu en esquina superior derecha
```

### 8.2 SIDEBAR (NAVEGACIÓN)
```
Width:      280px (desktop), 64px (collapsed), 100% (mobile overlay)
Posición:   Fixed left
Características:
  - Logo/Avatar en top
  - Menu items con iconos + textos
  - Active state con accent color + left border 3px
  - Hover state con bg color sutil
  - Bottom section: User profile info
  - Colapsable en desktop, drawer en mobile
  - Smooth transition collapse/expand
```

### 8.3 CARDS
```
Estructura:
  - Borde: 1px border-light
  - Radio: 12px
  - Padding: 20px
  - Sombra: Subtle
  - Hover: Elevar (Strong shadow) + border-medium

Variantes:
  - Card Base: información general
  - Stat Card: valores grandes (números)
  - Interactive Card: clickeable
  - Elevated Card: modal, destacado
```

### 8.4 BUTTONS
```
Variantes Primarias:
  - Primary:      bg-accent-blue, text-white, radius-md, 40px height
  - Secondary:    border + text-primary, bg-transparent
  - Ghost:        sin border, text-secondary, hover bg-tertiary
  - Danger:       bg-accent-red (para destructivas)

Tamaños:
  - sm: 32px height, 12px padding
  - md: 40px height, 16px padding (default)
  - lg: 48px height, 20px padding
```

### 8.5 INPUTS
```
Structure:
  - Label arriba (12px, text-tertiary)
  - Input field (40px height, 12px padding)
  - Border-light, focus border-accent-blue + ring effect
  - Placeholder: text-tertiary
  - Error state: border-accent-red, bg-red tint
  - Focus ring: 2px accent-blue con opacity 0.1
```

### 8.6 TABLES
```
Estructura:
  - Header row: bg-secondary, text-secondary, border-bottom 1px
  - Data rows: border-bottom subtle
  - Hover row: bg-tertiary
  - Sticky header en scroll
  - Responsive: Stack en mobile (card-like)
  - Acciones en columna final (edit, delete icons)
```

### 8.7 FORMS
```
Generales:
  - Max-width: 600px (forms largos)
  - Spacing entre inputs: 20px
  - Labels boldeadas (600 weight)
  - Helper text: 12px, text-tertiary
  - Error messages: text-accent-red
  - Validation states visual clara
```

### 8.8 MODALS/DIALOGS
```
Características:
  - Backdrop: bg black, opacity 0.5
  - Modal: bg-elevated, radius-lg, max-width 500px
  - Header: bordered bottom, padding 24px
  - Body: padding 24px
  - Footer: buttons aligned right, padding 24px, border top
  - Animación entrada: fade + scale (200ms)
```

### 8.9 NOTIFICACIONES (Toast/Alerts)
```
Posición:      bottom-right (desktop), full-width (mobile)
Tipos:
  - Success: bg-green con icon
  - Error: bg-red con icon
  - Warning: bg-amber con icon
  - Info: bg-blue con icon
Auto-close: 5000ms (configurables)
Animación: slide in from bottom
```

---

## 9. ESTRUCTURA DE PANTALLAS

### 9.1 DASHBOARD PRINCIPAL
```
┌─────────────────────────────────────────────┐
│ HEADER (64px)                               │
├────────────────┬──────────────────────────┬─┤
│                │                          │S│
│   SIDEBAR      │    CONTENT AREA          │I│
│   (280px)      │                          │D│
│                ├──────────────────────────┤E│
│                │ PAGE TITLE               │B│
│                ├──────────────────────────┤A│
│                │ METRIC CARDS GRID (2x2)  │R│
│                │ [Card] [Card]            │(C│
│                │ [Card] [Card]            │O│
│                ├──────────────────────────┤L│
│                │ CHARTS GRID (2 col)      │L│
│                │ [Chart 1]  [Chart 2]     │A│
│                │ [Chart 3]  [Chart 4]     │P│
│                ├──────────────────────────┤S│
│                │ RECENT TRANSACTIONS      │E│
│                │ [Table/List]             │D│
│                └──────────────────────────┘)│
└────────────────┴──────────────────────────┴─┘

Componentes Clave:
1. Hero Section: Saldo actual grande, balance mes
2. Metric Cards: Ingresos/Gastos/Ahorros/Presupuesto
3. Charts: Gráficas interactivas (Chart.js)
4. Recent Transactions: Tabla scrollable
5. Budget Progress: Barras visuales por categoría
```

### 9.2 REGISTRO/GASTO
```
┌──────────────────────────────┐
│ TOP FORM SECTION (sticky)    │
│ [Input] [Select] [Button]    │
└──────────────────────────────┘
     ↓ Resumen rápido
┌──────────────────────────────┐
│ FILTER BAR (sticky)          │
│ [Search] [Category] [Date]   │
└──────────────────────────────┘
     ↓ Resultados
┌──────────────────────────────┐
│ TRANSACTION CARDS / TABLE    │
│ [Card con info principal]    │
│ [Actions: edit, delete]      │
└──────────────────────────────┘

Móvil:
- Form stacked
- Table → Cards
- Sticky top button (+)
```

### 9.3 CATEGORÍAS Y LÍMITES
```
Grid de Cards:
┌─────────┐ ┌─────────┐ ┌─────────┐
│Category │ │Category │ │Category │
│[Chart]  │ │[Chart]  │ │[Chart]  │
│Usage    │ │Usage    │ │Usage    │
└─────────┘ └─────────┘ └─────────┘

Features:
- Edit limit al click
- Color asignado por categoría
- Progress bar visual
- Editable con inline forms
```

### 9.4 CONFIGURACIÓN
```
Tabs/Sections:
- Perfil: Info usuario, avatar, email
- Preferencias: Tema (light/dark), idioma
- Presupuestos: Crear/editar límites
- Integraciones: Conectar apps
- Seguridad: 2FA, WebAuthn, cambiar password
- Notificaciones: Preferencias de alertas
```

---

## 10. FLUJOS DE INTERACCIÓN

### 10.1 Agregar Gasto
```
1. Usuario hace click en botón "+" o "Add Expense"
2. Modal/Form abre con fade + scale
3. Campos: Cantidad | Categoría | Descripción | Fecha
4. Validación en tiempo real (feedback visual)
5. Submit → Toast success → Modal cierra
6. Lista se actualiza automáticamente
```

### 10.2 Editar Gasto
```
1. Usuario hace click en icon edit en row/card
2. Modal abre con datos pre-rellenados
3. Mismos campos editables
4. Submit → Update sin reload
5. Row animación de highlight para feedback
```

### 10.3 Buscar/Filtrar
```
1. Input de búsqueda en top (sticky)
2. Filter results en tiempo real (debounced 300ms)
3. Chips/tags para filtros activos
4. Reset button disponible
5. Resultados se animan con fade-in
```

---

## 11. MICRO-INTERACCIONES

### 11.1 Hover Effects
- Cards: +2px shadow, subtle bg change
- Buttons: -2px transform (press effect)
- Links: underline decoration
- Rows: bg-tertiary

### 11.2 Loading States
- Skeleton loaders (pulsing effect)
- Spinner centered
- 300ms fade transition

### 11.3 Validation
- Green checkmark ✓ (success)
- Red X (error)
- Inline error messages
- Focus ring on inputs

### 11.4 Animations
- Page transitions: fade (200ms)
- Modal open: scale 0.95→1.0 + fade
- List items: stagger animation (50ms between)
- Success feedback: confetti particles (optional)

---

## 12. RESPONSIVE BREAKPOINTS

```
Desktop:  1440px+ (sidebar 280px visible)
Tablet:   1024px  (sidebar colapsable)
Mobile:   768px   (sidebar drawer overlay)
Small:    480px   (full-screen optimized)

Media Queries:
@media (max-width: 1024px) {
  - Sidebar colapsable (64px collapsed)
  - 2-col grids → 1 col
  - Charts responsive
}

@media (max-width: 768px) {
  - Sidebar full overlay (100vw)
  - Header 56px (más compacto)
  - Forms stacked
  - Tables → Cards
  - Padding reducido
}

@media (max-width: 480px) {
  - Padding 12px
  - Font sizes -10%
  - Números más pequeños
  - Buttons full-width (contexto)
```

---

## 13. ACCESIBILIDAD (WCAG 2.1 AA)

### Requerimientos Mínimos
- Contraste texto 4.5:1 (normal), 3:1 (large)
- Focus states visibles (outline 2px)
- Aria labels en iconos
- Keyboard navigation completa (Tab, Enter, Esc)
- Color no único medio de información
- Alt text en imágenes
- Semantic HTML (buttons, links, headings)

### Features de Accesibilidad
```
- Dark mode built-in (AMOLED friendly)
- High contrast mode disponible
- Font size adjustable
- Reduced motion option (prefers-reduced-motion)
- Screen reader optimized
- Skip to content link
- Keyboard shortcuts (optional)
```

---

## 14. MODO CLARO vs OSCURO

### Estrategia de Implementación
```css
:root {
  /* Dark mode (default) */
}

@media (prefers-color-scheme: light) {
  :root {
    /* Light mode overrides */
  }
}

body.light-mode {
  /* Forced light mode */
}

body.dark-mode {
  /* Forced dark mode */
}
```

### Preferencia del Usuario
- Detectar `prefers-color-scheme` automáticamente
- Guardar preferencia en localStorage
- Toggle en settings/header
- Transición suave 200ms

---

## 15. ICONOGRAFÍA

### Fuente Recomendada
- **Heroicons** v2: Moderna, 24px base, stroke 1.5
- Alternativa: **Feather Icons**, **Lucide**, **Phosphor**

### Tamaños Estándar
```
xs:  16px  (decorativo)
sm:  20px  (labels)
md:  24px  (buttons)
lg:  32px  (hero sections)
xl:  48px  (full-page icons)
```

### Colores
- Iconos default: text-secondary
- Active/highlight: accent-blue
- Success: accent-green
- Error: accent-red

---

## 16. EJEMPLOS DE COLORES POR CONTEXTO

### Categorías de Gastos (Colores Asignados)
```
Alimentación:    #F59E0B (Amber)
Transporte:      #3B82F6 (Blue)
Entretenimiento: #EC4899 (Pink)
Servicios:       #8B5CF6 (Purple)
Salud:           #06B6D4 (Cyan)
Educación:       #14B8A6 (Teal)
Hogar:           #F87171 (Red)
Otro:            #6B7280 (Gray)
```

### Estados de Transacciones
```
Completada:      Green #10B981
Pendiente:       Amber #F59E0B
Cancelada:       Red #EF4444
En Revisión:     Blue #3B82F6
```

---

## 17. MEJORES PRÁCTICAS 2026

### Performance
- CSS-in-JS o Tailwind (utility-first)
- Lazy loading de componentes
- Code splitting
- Optimizar imágenes (WebP)
- Critical CSS inlined

### Developer Experience
- Component library documentado
- Storybook para componentes
- Design tokens exportables
- Git workflow (branches feature)

### User Experience
- Progressive Enhancement
- Offline capability (Service Workers)
- PWA capabilities
- Real-time updates (WebSockets)
- Undo/Redo para destructivas

### Security
- CSRF protection
- XSS prevention (sanitize)
- Rate limiting en APIs
- SSL/TLS enforced

---

## 18. EJEMPLOS DE TRANSICIONES

### Page Load
```
Fade in 300ms easeInOut → Content ready
Skeleton loaders mientras cargan datos
Stagger animations en listas
```

### Button Press
```
Scale 1 → 0.95 (100ms)
Scale 0.95 → 1 (100ms)
Ripple effect (opcional material design)
```

### Form Submission
```
Button disabled + loading spinner
Toast notification al completar
Auto-hide form o redirect
```

---

## 19. APLICACIÓN A MOBILE

### Optimizaciones Específicas
- Touch targets: mínimo 44x44px
- Bottom navigation tab bar
- Swipe gestures (left/right para navegar)
- Floating action button (+ agregar)
- Modal full-screen (tablets)
- Reducir padding/margin
- Font sizes legibles sin zoom

---

## 20. NEXT STEPS IMPLEMENTACIÓN

### Fase 1: Fundación (Semana 1)
- [ ] CSS base con variables de diseño
- [ ] Componentes HTML semánticos
- [ ] Media queries responsive
- [ ] Dark/Light mode toggle

### Fase 2: Componentes (Semana 2)
- [ ] Cards, Buttons, Inputs
- [ ] Forms validación
- [ ] Tables responsive
- [ ] Modals/Dialogs

### Fase 3: Funcionalidad (Semana 3)
- [ ] Dashboard layout
- [ ] Charts integración
- [ ] Transacciones CRUD
- [ ] Filtros/búsqueda

### Fase 4: Polish (Semana 4)
- [ ] Micro-interacciones
- [ ] Animaciones
- [ ] Notificaciones
- [ ] Testing responsivo

---

## 📋 RESUMEN ARQUITECTURA

```
Design System
├── Color Tokens (24 variables)
├── Typography Scale (13 sizes)
├── Spacing System (8 tokens)
├── Shadow Elevation (5 levels)
├── Border Radius (5 sizes)
├── Components Library
│   ├── Layout (Header, Sidebar, Grid)
│   ├── Forms (Input, Select, Textarea)
│   ├── Interactive (Button, Link, Tab)
│   ├── Data (Table, Card, List)
│   └── Feedback (Modal, Toast, Spinner)
├── Animation Tokens (4 tipos)
├── Responsive Breakpoints (5 breakpoints)
└── Accessibility Features (WCAG AA+)
```

---

**Versión:** 1.0  
**Fecha:** Junio 2026  
**Autor:** UX/UI Design System  
**Última Actualización:** 2026-06-21
