<?php
/**structural*/

$categories = woo_site_builder_get_product_categories();

$category_slugs = wp_list_pluck($categories, 'slug');
global $woocommerce;
?>
<div class="woo-page-builder">







    <div id="menu" class="hidden">
        <div id="sideMenu" class="ui-droppable">
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
        <div id="subMenu" class="hidden">
	        <?php
	        foreach ($category_slugs as $category_slug){
		        $products = woo_site_builder_get_products_by_category($category_slug);

		        if($products):
			        echo "<ul id='$category_slug'>";
			        foreach ($products as $product):
				        $product_image = wp_get_attachment_url(get_post_thumbnail_id($product->get_id()));
				        if(!$product_image) continue;

				        echo '<li class="structural-elements" data-category="'.$category_slug.'" data-productid="'.$product->get_id().'"><span>'.get_the_title($product->get_id()).'</span><img src="'.$product_image.'"></li>';

			        endforeach;
			        echo "</ul>";
		        endif;


	        }
	        ?>
        </div>
        <div class="status">
            <div style="width:40%"></div>
        </div>


    </div>


    <div class="toggle"></div>
    <div class="add-page"></div>

    <!--- CONTENT -->
    <div id="blocksHolder" class="hide-ui">
        <div class="woo-page-builder-container">
            <!--- Name -->
            <div class="name project-page-name">
                <div>
                    <input type="text" id="project" placeholder="Project Name" value="Untitled Page">
                    <span id="clear" class="clear"></span>
                </div>
            </div>
            <!--- Project -->
            <div class="browser" id="builder">
                <ul id="blocks"></ul>
            </div>
            <!--- Footer -->
            <div class="footer">
                <a id="builder-checkout" class="button" href="<?php echo $woocommerce->cart->get_checkout_url();?>">Checkout</a>
            </div>

            <div class="pages-preview">

            </div>

        </div>
    </div>

    <div class="builder-cart" style="display: none;"><span class="cart-icon"><img src="<?php echo WSB_ASSETS.'/images/shopping-cart.svg'; ?>" alt=""></span><span class="cart-text">CART</span><span class="cart-price">$10</span></div>
</div>