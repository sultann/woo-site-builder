<?php
if ( !defined( 'ABSPATH' ) ) exit;

class WSB_Builder{


//	protected $transient_name;
//	protected $transient;
//	protected $user_reference;
//	protected $blocks;
//	protected $price;
//	protected $products;
//	protected $project;
//	protected $project_page;
//	protected $cart;
	protected $last_page;
	protected $error = [];

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

		add_action('woocommerce_thankyou', [$this, 'add_builder_products_to_order'], 111, 1);
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


	function  add_builder_products_to_order($order_id){
		if(!empty(WC()->session->get( 'side_builder' ))){
			$user_reference = WC()->session->get( 'side_builder' );

			$project = get_transient($this->make_transient_name($user_reference));
			if(empty($project)) return ;
			$pages = [];
			foreach ($project as $page){
				if(isset($page['image_link'])){
					$pages[] = $page['image_link'];
				}
			}

			update_post_meta($order_id, 'builder_pages', $pages);
			setcookie('user_reference', '', time() - (86400 * 30), '/');
		}

	}



	function is_page_in_project($page, $project) {
		foreach ($project as $key => $val) {
			if ($val['page'] === $page) {
				return $key;
			}
		}
		return null;
	}



	function save_builder_project(){
		$has_blocks = true;
		$has_page = true;

		if(!isset($_POST['user_reference']) || trim($_POST['user_reference']) == ''){
			wp_send_json(['error' => 'Could not find User reference']);
			wp_die();

		}

		if(!isset($_POST['name']) || trim($_POST['name']) == ''){
			$this->error[] = 'Could not find any project Name';
			$has_page = false;


		}

		if(!isset($_POST['blocks']) || trim($_POST['blocks']) == ''){
			$this->error[] = 'No blocks received';
			$has_blocks = false;
		}


//		Default Parameters must be in request
//		$image_link = '';
//		$blocks='';
//		$page='';
//		$user_reference = '';

		$blocks='';
		$image_link = '';

		$user_reference = esc_attr($_POST['user_reference']);

		$project = $this->get_project($user_reference);

		$temp_transient = $user_reference.'_last_page';

		if($has_page){
			$page = esc_attr($_POST['name']);
			set_transient($temp_transient, $page, 12 * HOUR_IN_SECONDS);

		}else{
			$page = get_transient($temp_transient)?get_transient($temp_transient):'';
			error_log('got page name '. $page);
			delete_transient($temp_transient);
		}

		error_log('page name '. $page);


		if(!$has_blocks){
			error_log('No blocks');
			$project = $this->remove_page($project, $page, $user_reference);
		}else{
			$image_link = $this->make_image($page, $user_reference);
			$blocks=esc_attr($_POST['blocks']);
			$project = $this->update_project($user_reference, $page, $blocks, $image_link);

		}









		wp_send_json([
			'success' => empty($this->error)?true:false,
			'data' => $project,
			'price' => $this->update_cart($project, $user_reference),
			'error' => $this->error
		]);




		wp_die();
	}




	function update_project($user_reference, $page, $blocks, $image_link){


		$project =  $this->get_project($user_reference);

		//if project page already then update

		$key = $this->is_page_in_project($page,$project);

		if($key !== null){
			$project[$key]['blocks'] = $blocks;
			$project[$key]['image_link'] =  $image_link;
			$project[$key]['page'] = $page;
		}else{
			$data = array();
			$data['blocks'] = $blocks;
			$data['image_link'] =  $image_link;
			$data['page'] = $page;
			$project[] = $data;
		}

		$this->save_project($project, $user_reference);

		return $project;
	}


	function remove_page($project, $page, $user_reference){
	error_log('remove page');
		$key = $this->is_page_in_project($page, $project);
		if($key !== null){
			error_log('found key'. $key);
			if(isset($project[$key]['image_link'])){
				error_log('found image'. $project[$key]['image_link']);
				$file_name = basename($project[$key]['image_link']);
				$wp_upload = wp_upload_dir();
				$file_path  = trailingslashit($wp_upload['path']).$file_name;

				if(file_exists($file_path)){
					error_log('found file and removing it');
					@unlink($file_path);
				}

				error_log('removing image ');
			}
			unset($project[$key]);
		}

		$project = $this->save_project($project, $user_reference);

		return $project;

	}


	function get_project($user_reference){
		$project = [];
		$transient_name = $this->make_transient_name($user_reference);
		$saved_project = get_transient($transient_name);

		if($saved_project){
			$project = $saved_project;
		}else{
			set_transient( $transient_name,  $project, 12 * HOUR_IN_SECONDS );
		}

		return $project;

	}

	function save_project($project, $user_reference){
		$transient_name = $this->make_transient_name($user_reference);
		set_transient( $transient_name,  $project, 12 * HOUR_IN_SECONDS );

		return $project;
	}


	function get_products($project){

		if(empty($project)){
			return [];
		}
		error_log('getting products');
		error_log(print_r($project, true));
		$product_ids = wp_list_pluck($project, 'blocks');
		$products = [];
		foreach ($product_ids as $product_id){
			$new_arr = [];
			$new_arr = explode(',', $product_id);
			$products = array_merge($products, $new_arr);
		}

		$products = array_unique($products);

		return $products;
	}


	function make_transient_name($user_reference){
		return 'site_builder_'.$user_reference;
	}


	function make_image($page_name, $user_reference){
		$image_name = $user_reference.'-'.$page_name.'.jpg';
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

		return $image_link;

	}



	function update_cart($project, $user_reference){
		error_log('updating cart');
		$product_ids = $this->get_products($project);
		$products = [];
		$price = 0;
		foreach ($product_ids as $product_id){
			$new_arry = [];
			$new_arry = explode(',', $product_id);
			$products = array_merge($products, $new_arry);
		}


		if(!empty($products)){
			WC()->cart->empty_cart();
			foreach ($products as $product){
				WC()->cart->add_to_cart($product);
			}

			$price = WC()->cart->cart_contents_total;

			WC()->session->set('side_builder', $user_reference);
		}



		return $price;






	}




}
new WSB_Builder();

function wps_site_builder_page(){

	include WSB_TEMPLATES_DIR.'/site-builder.php';
}