<?php
/**
 * Builder Page Template
 */

$categories = woo_site_builder_get_product_categories();

$category_slugs = wp_list_pluck($categories, 'slug');

?>
<div class="woo-page-builder">


	<!--- MENU -->
	<div id="menu" class="hidden">
		<div id="sideMenu">
			<!--- Main Menu -->
			<?php
			if($categories){
				echo '<ul>';
				foreach ($categories as $category){
					echo '<li data-menu-item="'.$category->slug.'">'.$category->name.'</li>';
				}
				echo '</ul>';
			}
			?>

		</div>
		<!--- Submenu -->
		<div id="subMenu" class="hidden submenu">
			<?php
			 foreach ($category_slugs as $category_slug){
				$products = woo_site_builder_get_products_by_category($category_slug);

				if($products):
					echo "<ul id='$category_slug'>";
						foreach ($products as $product):
							$product_image = wp_get_attachment_url(get_post_thumbnail_id($product->id));
							if(!$product_image) continue;

							echo '<li data-category="'.$category_slug.'" data-name="header" data-number="'.$product->id.'"><span>'.get_the_title($product->id).'</span><img src="'.$product_image.'"></li>';

						endforeach;
					echo "</ul>";
				endif;


			 }
			?>
		</div>


		<!-- Status-->
		<div class="status">
			<div style="width:40%"></div>
		</div>
	</div>


    <!--- TOGGLE -->
    <div class="toggle"></div>
    <div class="add-page"></div>


    <div id="blocksHolder" class="hide-ui">
        <div class="container">
            <!--- Name -->
            <div class="name">
                <div>
                    <input type="text" id="project" placeholder="Project Name">
                    <span id="clear" class="clear"></span>
                </div>
            </div>
            <!--- Project -->
            <div class="browser" id="builder">
                <ul id="blocks"></ul>
            </div>
            <!--- Footer -->
            <div class="footer">
                <div id="save" class="button" data-signature="14fda1928850021e0c15dc26e73c9c76" data-timestamp="1496247734">Save Project</div>
                <!-- After save can buy -->
                <!-- <div id="download" class="button download">Export HTML/CSS</div> -->
            </div>




        </div>
    </div>




</div>