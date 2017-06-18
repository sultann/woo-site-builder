<?php
// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

function woo_site_builder_get_product_categories() {
    $args               = array(
        'taxonomy'     => 'product_cat',
        'orderby'      => 'name',
        'show_count'   => 0,
        'pad_counts'   => 0,
        'hierarchical' => 0,
        'hide_empty'   => 0
    );
    $woo_all_categories = get_categories( $args );

	foreach ($woo_all_categories as $key=>$cat){
		if (strpos(strtolower($cat->name), 'child theme') !== false) {
			unset($woo_all_categories[$key]);
		}
	}

    return $woo_all_categories;
}

function woo_site_builder_get_products_by_category( $slug ) {
    $products = [];
    $args     = array( 'post_type'      => 'product',
        'stock'          => 1,
        'posts_per_page' => 2,
        'product_cat'    => $slug,
        'orderby'        => 'date',
        'order'          => 'ASC'
    );
    $loop     = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post();
        global $product;
        $products[] = $product;
    endwhile;
    wp_reset_query();
    if ( ! empty( $products ) ) {
        return $products;
    }

    return false;


}




add_filter( 'woocommerce_locate_template', 'woo_site_builder_woocommerce_locate_template', 10, 3 );



function woo_site_builder_woocommerce_locate_template( $template, $template_name, $template_path ) {

	global $woocommerce;



	$_template = $template;

	if ( ! $template_path ) $template_path = $woocommerce->template_url;

	$plugin_path  = trailingslashit( WSB_TEMPLATES_DIR );



	// Look within passed path within the theme - this is priority

	$template = locate_template(

		array(

			$template_path . $template_name,

			$template_name

		)

	);



	// Modification: Get the template from this plugin, if it exists

	if ( ! $template && file_exists( $plugin_path . $template_name ) )

		$template = $plugin_path . $template_name;



	// Use default template

	if ( ! $template )

		$template = $_template;



	// Return what we found

	return $template;

}