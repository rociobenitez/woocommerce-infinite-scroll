<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

get_header();

// ACF fields for current term
$current_term = get_queried_object(); 
$fields = function_exists('get_fields') ? get_fields($current_term) : [];

// JSON encode query vars for JS use
$json_vars = wp_json_encode( $wp_query->query_vars );
?>

<!-- Main content -->
<div class="woocommerce-shop my-5">
    <div class="container container-products mb-5">
        <?php
        /**
         * Hook: woocommerce_before_main_content
         * 
         * @hooked woocommerce_breadcrumb - 20
         */
        //do_action('woocommerce_before_main_content');
        ?>
        <div class="row">
            <div class="shop-filters-horizontal">
                <?php // Here go the product filters ?>
            </div>

            <!-- Products -->
            <div class="col-12">
                <?php if ( woocommerce_product_loop() ) : ?>

                    <!-- Order & Filters -->
                    <div class="shop-filters d-flex justify-content-between justify-content-lg-end align-items-center gap-3 mb-4">
                        <?php do_action( 'woocommerce_before_shop_loop' ); ?>
                    </div>

                    <!-- List Products -->
                    <div class="grid-products row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5"
                        data-query="<?php echo esc_attr( $json_vars ); ?>"
                        data-per-page="<?php echo esc_attr( wc_get_loop_prop( 'per_page' ) ); ?>">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <div class="col text-center">
                                <?php wc_get_template_part( 'content', 'product' ); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php do_action( 'woocommerce_after_shop_loop' ); ?>
                <?php else : ?>
                    <p class="text-center"><?php esc_html_e( 'No se encontraron productos.', 'woocommerce' ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
