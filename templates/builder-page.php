<?php
/**
 * Created by PhpStorm.
 * User: manik
 * Date: 6/6/17
 * Time: 11:10 PM
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <!--[if lt IE 9]>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/html5.js"></script>
    <![endif]-->

    <?php wp_head(); ?>
</head>
<body <?php body_class('site-builder-page'); ?>>

<?php include WSB_TEMPLATES_DIR.'/builder.php'; ?>
<?php wp_footer(); ?>
</body>
</html>