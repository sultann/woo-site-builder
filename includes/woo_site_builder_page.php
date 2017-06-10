<?php

/**
 * User: manik
 * Date: 6/10/17
 * Time: 11:39 PM
 */
class Woo_Site_builder_Page
{


    /**
     * Woo_Site_builder_Page constructor.
     */
    public function __construct()
    {
        add_action('page_template', [$this, 'show_builder_page']);

    }


    public function show_builder_page($template){
        if(is_page('site-builder')){
            $template = trailingslashit(WSB_TEMPLATES_DIR). 'builder-page.php';
//            add_filter('show_admin_bar', '__return_false');
        }

        return $template;

    }



}

new Woo_Site_builder_Page();