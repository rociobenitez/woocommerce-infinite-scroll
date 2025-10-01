# WooCommerce Infinite Scroll

Una implementación de scroll infinito para productos de WooCommerce que reemplaza la paginación tradicional con una experiencia de usuario fluida y continua.

## Características

- **Scroll infinito optimizado** para tiendas WooCommerce
- **Carga progresiva** de productos sin interrupciones
- **Loader visual** con indicadores de accesibilidad
- **Intersection Observer API** para detección eficiente del viewport
- **Manejo de errores** robusto y recuperación de fallos
- **Accesibilidad** integrada (ARIA attributes, screen readers)
- **Bootstrap compatible** (fácil adaptación a CSS nativo)
- **Performance optimizada** con debouncing y memoria eficiente

## Requisitos

- WordPress 6.0+
- WooCommerce 9.0+
- PHP 7.4+
- Navegadores modernos con soporte para:
  - `IntersectionObserver`
  - `fetch()`
  - `URLSearchParams`

## Instalación

### 1. Copiar archivos del template

Copia el archivo de template a tu tema activo:

```bash
cp woocommerce/archive-product.php /path/to/your/theme/woocommerce/
```

### 2. Incluir el JavaScript

Agrega el script en tu tema (`functions.php`):

```php
function enqueue_infinite_scroll() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_script(
            'infinite-scroll',
            get_template_directory_uri() . '/js/infinite-scroll.js',
            [],
            '1.0.0',
            true
        );

        // Localizar variables AJAX
        wp_localize_script('infinite-scroll', 'LOUE', [
            'ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('infinite_scroll_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_infinite_scroll');
```

### 3. Incluir estilos CSS

Agrega los estilos del loader en tu archivo CSS principal.

### 4. Implementar el endpoint AJAX

Agrega las funciones de manejo AJAX en tu archivo `functions.php`.

## Estructura del código

### JavaScript principal (`infinite-scroll.js`)

El script está organizado en módulos funcionales:

#### 1. **Inicialización y Configuración**

```javascript
const grid = document.querySelector(".grid-products.row");
const queryVars = grid.dataset.query || "{}";
const perPage = parseInt(grid.dataset.perPage || "24", 10);
```

#### 2. **Control de Estado**

- `page`: Página actual de productos
- `loading`: Previene cargas simultáneas
- `hasMore`: Indica si existen más productos

#### 3. **Elementos del DOM**

- **Loader**: Indicador visual de carga con accesibilidad
- **Sentinel**: Elemento invisible que activa la carga

#### 4. **Función de Carga (`loadMore`)**

- Validación de estado
- Petición AJAX optimizada
- Manejo de errores robusto
- Actualización del DOM

#### 5. **Intersection Observer**

- Detección eficiente del viewport
- Margen de activación configurable (800px)
- Desconexión automática al finalizar

### Template PHP (`archive-product.php`)

#### Características del Template

- **Compatibilidad**: Bootstrap 5 (adaptable a CSS nativo)
- **Data Attributes**: Configuración JavaScript embebida
- **Hooks WooCommerce**: Mantiene compatibilidad con plugins
- **SEO Friendly**: Estructura semántica correcta

#### Elementos Clave

```php
<div class="grid-products row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5"
     data-query="<?php echo esc_attr( $json_vars ); ?>"
     data-per-page="<?php echo esc_attr( wc_get_loop_prop( 'per_page' ) ); ?>">
```

## Configuración Avanzada

### Personalizar el Loader

Modifica el HTML del loader en el JavaScript:

```javascript
loader.innerHTML = `
    <div class="custom-spinner">
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
    </div>
    <p>Cargando productos...</p>
`;
```

### Ajustar la Sensibilidad de Carga

Cambia el `rootMargin` del Intersection Observer:

```javascript
io = new IntersectionObserver(
  (entries) => {
    if (entries[0].isIntersecting) loadMore();
  },
  { rootMargin: "400px 0px" } // Carga cuando faltan 400px
);
```

### Adaptación a CSS Nativo

Reemplaza las clases de Bootstrap en el template:

```php
<!-- De esto -->
<div class="grid-products row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">

<!-- A esto -->
<div class="grid-products products-grid">
```

Y agrega CSS personalizado:

```css
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}
```

## Debugging y solución de problemas

### Activar Modo Debug

Agrega logging en el JavaScript:

```javascript
const DEBUG = true; // Cambiar a false en producción

function debugLog(message, data = null) {
  if (DEBUG) {
    console.log(`[Infinite Scroll] ${message}`, data);
  }
}
```

### Problemas Comunes

#### 1. **No se cargan más productos**

- Verificar que el endpoint AJAX esté registrado
- Comprobar la respuesta en Developer Tools > Network
- Validar que `has_more` sea `true` en la respuesta

#### 2. **Productos duplicados**

- Revisar que la query no incluya productos ya mostrados
- Verificar el valor de `paged` en la consulta

#### 3. **Loader no aparece**

- Comprobar que el CSS del loader esté cargado
- Verificar que no haya conflictos de z-index

## Performance y Optimización

### Métricas recomendadas

- **Time to First Load**: < 2 segundos
- **Subsequent Loads**: < 1 segundo
- **Memory Usage**: Monitizar crecimiento del DOM

### Optimizaciones implementadas

1. **Lazy Loading**: Solo carga cuando es necesario
2. **Debouncing**: Evita cargas excesivas
3. **Memory Management**: Cleanup automático de observers
4. **Error Recovery**: Manejo graceful de fallos de red

## Compatibilidad de Navegadores

| Navegador | Versión Mínima | Notas                                |
| --------- | -------------- | ------------------------------------ |
| Chrome    | 58+            | Soporte completo                     |
| Firefox   | 55+            | Soporte completo                     |
| Safari    | 12+            | Soporte completo                     |
| Edge      | 16+            | Soporte completo                     |
| IE        | ❌             | No compatible (IntersectionObserver) |

## Contribuciones

Las contribuciones son bienvenidas. 😊

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.
