<?php
/**
 * INFINITE SCROLL PARA WOOCOMMERCE
 */

// Ocultar el paginador nativo de Woo en tienda/archivos
add_action( 'wp', function () {
    if ( is_shop() || is_product_taxonomy() || is_post_type_archive('product') || is_page_template('page-rent.php') ) {
        remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
    }
});

// Encolar JS y pasarle el contexto actual (taxonomía de marca/categoría, etc.)
add_action('wp_enqueue_scripts', function () {
    if ( is_shop() || is_product_taxonomy() || is_post_type_archive('product') || is_page_template('page-rent.php') || is_product_category() ) {
        wp_enqueue_script(
            'infinite-scroll',
            get_stylesheet_directory_uri() . '/assets/js/infinite-scroll.js',
            [],
            null,
            true
        );
        wp_localize_script('infinite-scroll', 'LOUE', [
            'ajax' => admin_url('admin-ajax.php'),
        ]);
  }
});

// Endpoint AJAX (usa los query_vars de la página)
add_action('wp_ajax_infinite_scroll',        'infinite_scroll_cb');
add_action('wp_ajax_nopriv_infinite_scroll', 'infinite_scroll_cb');
function infinite_scroll_cb(){
  $vars = json_decode( stripslashes( $_POST['query_vars'] ?? '{}' ), true ) ?: [];

  $page = max(1, (int)($_POST['page'] ?? 1));
  $pp   = max(1, (int)($_POST['per_page'] ?? 24));

  $vars['post_type']      = 'product';
  $vars['post_status']    = 'publish';
  $vars['posts_per_page'] = $pp + 1;   // +1 para saber si hay más
  $vars['paged']          = $page;
  $vars['no_found_rows']  = true;      // rendimiento

  $q = new WP_Query($vars);

  ob_start();
  $count = 0;
  while ( $q->have_posts() ) {
    $q->the_post();
    $count++;
    if ( $count <= $pp ) {
      echo '<div class="col text-center">';
      wc_get_template_part( 'content', 'product' );
      echo '</div>';
    }
  }
  wp_reset_postdata();

  wp_send_json_success([
    'html'     => ob_get_clean(),
    'has_more' => ($count > $pp),
    'next'     => $page + 1
  ]);
}

// Desactivar <link rel="next">/<link rel="prev"> de Yoast
add_filter('wpseo_next_rel_link', '__return_false');
add_filter('wpseo_prev_rel_link', '__return_false');
