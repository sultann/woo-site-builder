<?php

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