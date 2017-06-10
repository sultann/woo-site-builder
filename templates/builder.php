<?php
/**structural*/
ob_start();
$categories = woo_site_builder_get_product_categories();

$category_slugs = wp_list_pluck($categories, 'slug');
global $woocommerce;
?>