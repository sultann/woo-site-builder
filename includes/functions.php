<?php


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

	error_log($plugin_path . $template_name);

	if ( ! $template && file_exists( $plugin_path . $template_name ) )

		$template = $plugin_path . $template_name;



	// Use default template

	if ( ! $template )

		$template = $_template;



	// Return what we found

	return $template;

}