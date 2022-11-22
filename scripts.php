<?php

add_action('wp_enqueue_scripts', 'partner_frontend_scripts');

function partner_frontend_scripts()
{

    $min = (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '10.0.0.3'))) ? '' : '.min';

    if (empty($min)) :
        wp_enqueue_script('partner-livereload', 'http://localhost:35729/livereload.js?snipver=1', array(), null, true);
    endif;

    wp_register_script('partner-script', PARTNER_URL . 'assets/js/partner' . $min . '.js', array('jquery'), '1.0.0', true);

    wp_enqueue_script('partner-script');

    wp_localize_script('partner-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_style('partner-style', PARTNER_URL . 'assets/css/partner.css', array(), '1.0.6', 'all');
}

add_action('wp_enqueue_scripts', 'partner_admin_scripts');
add_action('admin_enqueue_scripts', 'partner_admin_scripts');

function partner_admin_scripts()
{
    if (!is_user_logged_in())
        return;

    $min = (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '10.0.0.3'))) ? '' : '.min';

    wp_register_script('partner-admin-script', PARTNER_URL . 'assets/js/partner-admin' . $min . '.js', array('jquery'), '1.0.1', true);
    wp_enqueue_script('partner-admin-script');
    wp_localize_script('partner-admin-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}