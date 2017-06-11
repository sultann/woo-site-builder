<?php
/**structural*/
ob_start();
$categories = woo_site_builder_get_product_categories();

$category_slugs = wp_list_pluck($categories, 'slug');
global $woocommerce;
?>
<script src="//localhost:35729/livereload.js"></script>
<div id="woo-site-builder">
    <div id="builder-main-menu">
        <div id="builder-category-menu">
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
        <div id="builder-elements-menu" class="hidden">
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

        <div id="builder-cart" style="display: none;"><div class="shopping-cart-inside"><i class="fa fa-shopping-cart" aria-hidden="true"></i> <div class="prince-wrapper"> <span class="builder-currecncy">$</span><span class="builder-price"></span></div></div></div>

    </div>
    <div class="woo-builder-container-main">

        <div class="woo-builder-container">
            <div class="tools-button-group">
                <button class="builder-button small preview">Preview</button>
                <button class="builder-button small create-page">Add New Page</button>
                <button class="builder-button small remove-page">Remove Page</button>
                <button class="builder-button small">Save</button>
                <a href="<?php echo get_site_url(); ?>" class="builder-button small" style="text-transform: uppercase;">Home Page</a>
                <button class="builder-button small">Checkout</button>
            </div>


            <div id="page-name"></div>
            <div class="builder-project-canvas">
                <ul id="builder-blocks" class="builder-project-canvas-inside empty container">

                </ul>





            </div>

            <div id="builder-footer">
                <button id="builder-save" class="builder-button ">Save</button>
                <a id="builder-checkout" class="builder-button" href="<?php echo $woocommerce->cart->get_checkout_url();?>" style="display: none;">Checkout</a>
            </div>


            <div class="pages-preview">

            </div>
        </div>
    </div>

</div>


