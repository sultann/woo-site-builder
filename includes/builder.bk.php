<?php
if ( !defined( 'ABSPATH' ) ) exit;

class WSB_Builder{


	protected $transient_name;
	protected $user_reference;
	protected $blocks;
	protected $price;
	protected $products;
	protected $full_project;
	protected $cart;

	/**
	 * WSB_Builder constructor.
	 */
	public function __construct() {
		add_action('wp_enqueue_scripts', [$this, 'load_assets']);
		add_action('init', [$this, 'init']);
		add_action( 'wp_ajax_nopriv_save_builder_project', [$this, 'save_builder_project'] );
		add_action( 'wp_ajax_save_builder_project', [$this, 'save_builder_project'] );
		add_action( 'wp_ajax_nopriv_remove_builder_project', [$this, 'remove_builder_project'] );
		add_action( 'wp_ajax_remove_builder_project', [$this, 'remove_builder_project'] );
	}


	function load_assets(){
		wp_enqueue_style('site-builder-main', WSB_ASSETS.'/css/woo-site-builder.css', [], date('i'));
		wp_register_script('site-builder-main', WSB_ASSETS.'/js/woo-site-builder.js', ['jquery'], date('i'), true);
		wp_localize_script('site-builder-main', 'wsb', ['ajaxurl' => admin_url( 'admin-ajax.php' )]);
		wp_enqueue_script('site-builder-main');
	}




	function init(){
		add_shortcode('woo_site_builder', [$this, 'woo_site_builder_callback']);
	}

	/**
	 *
	 */
	function woo_site_builder_callback(){
		include WSB_TEMPLATES_DIR.'/builder-page.php';
	}



	function searchForProjectName($name, $array) {
		foreach ($array as $key => $val) {
			if ($val['name'] === $name) {
				return $key;
			}
		}
		return null;
	}



	function save_builder_project(){
		$has_blocks = true;
		if(!isset($_POST['user_reference']) || trim($_POST['user_reference']) == ''){
			wp_send_json(['error' => 'Could not find User reference']);
			wp_die();

		}

		if(!isset($_POST['name']) || trim($_POST['name']) == ''){
			wp_send_json(['error' => 'Could not find any project Name']);
			wp_die();

		}

		if(!isset($_POST['blocks']) || trim($_POST['blocks']) == ''){
			$has_blocks = false;
		}



		$user_reference = esc_attr($_POST['user_reference']);

		$transient_name = 'site_builder_'.$user_reference;

		$blocks = esc_attr($_POST['blocks']);
		$name = esc_attr($_POST['name']);


		//if we receive blocks then make the image
		$image_name = $user_reference.'-'.$name.'.jpg';
		$image_link = '';
		if($has_blocks){

			if(isset($_POST['img']) && trim($_POST['img']) !== ''){

				$upload_dir       = wp_upload_dir();
				$upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

				// open the output file for writing
				$full_path = $upload_path.$image_name;
				if(is_file($full_path)){
					@unlink($full_path);
				}
				$ifp = fopen( $full_path, 'wb' );

				// split the string on commas
				// $data[ 0 ] == "data:image/png;base64"
				// $data[ 1 ] == <actual base64 string>
				$data = explode( ',', $_POST['img'] );

				// we could add validation here with ensuring count( $data ) > 1
				fwrite( $ifp, base64_decode( $data[ 1 ] ) );

				// clean up the file resource
				fclose( $ifp );

				$image_link = trailingslashit($upload_dir['url']).$image_name;

			}

		}



		$transient = get_transient($transient_name);

		if(empty($transient)){
			$data = array();
			$data['user_reference'] = $user_reference;
			$data['blocks'] = $blocks;
			$data['image_link'] = $image_link;
			$data['name'] = $name;
			$transient[] = $data;
			set_transient( $transient_name,  json_encode($transient), 12 * HOUR_IN_SECONDS );
			$decoded_transient = $transient;
		}else{
			$decoded_transient = json_decode($transient, true);

			$key = $this->searchForProjectName($name, $decoded_transient);

			if($key !== null){
				error_log('updating');
				$decoded_transient[$key]['user_reference'] = $user_reference;
				$decoded_transient[$key]['blocks'] = $blocks;
				$decoded_transient[$key]['image_link'] = $image_link;
				$decoded_transient[$key]['name'] = $name;
			}else{
				error_log('new ');
			$data = array();
			$data['user_reference'] = $user_reference;
			$data['blocks'] = $blocks;
			$data['image_link'] = $image_link;
			$data['name'] = $name;
			$decoded_transient[] = $data;
			}
			set_transient( $transient_name,  json_encode($decoded_transient), 12 * HOUR_IN_SECONDS );
		}







		$products = [];
		$price = false;

		if($decoded_transient){

			$product_ids = wp_list_pluck($decoded_transient, 'blocks');
			error_log('prod cuts ids');
			error_log(print_r($product_ids, true));
			foreach ($product_ids as $product_id){
				$new_arry = [];
				$new_arry = explode(',', $product_id);
				$products = array_merge($products, $new_arry);
			}
		}


		$products = array_unique($products);

		global $woocommerce;
		WC()->cart->empty_cart();
		foreach ($products as $product){
			WC()->cart->add_to_cart($product);
		}

		$price = $woocommerce->cart->total;
		error_log(print_r(WC()->cart->get_cart_total(), true));

		wp_send_json([
			'success' => true,
			'data' => $decoded_transient,
			'products' => $products,
			'price' => WC()->cart->cart_contents_total,
		]);


		wp_die();
	}


	function remove_builder_project(){
		if(!isset($_POST['user_reference']) || trim($_POST['user_reference']) == ''){
			wp_send_json(['error' => 'Could not find User reference']);
			wp_die();

		}

		if(!isset($_POST['name']) || trim($_POST['name']) == ''){
			wp_send_json(['error' => 'Could not find any project Name']);
			wp_die();

		}

		$name = esc_attr($_POST['name']);
		$user_reference = esc_attr($_POST['user_reference']);

		$transient_name = 'site_builder_'.$user_reference;

		$transient = get_transient($transient_name);


		if($transient){
			$decoded_transient = json_decode($transient, true);

			$key = $this->searchForProjectName($name, $decoded_transient);

			if($key !== null){
				unset($decoded_transient[$key]);
				set_transient( $transient_name,  json_encode($decoded_transient), 12 * HOUR_IN_SECONDS );

				wp_send_json([
					'success' => true,
					'data' => $decoded_transient,
					'products' => $products,
					'price' => WC()->cart->cart_contents_total,
				]);
			}


		}


	}

	function new_builder_project(){

	}


	function make_image(){

	}


	function remove_product(){

	}


	function add_product(){

	}


	function remove_project(){

	}


	function get_total(){

	}









}
new WSB_Builder();

function wps_site_builder_page(){

	include WSB_TEMPLATES_DIR.'/site-builder.php';
}