<?php

add_action('wp_enqueue_scripts', 'partner_frontend_scripts');

function partner_frontend_scripts()
{

    $min = (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '10.0.0.3'))) ? '' : '.min';


    if (empty($min)) :
        wp_enqueue_script('partner-livereload', 'http://localhost:35729/livereload.js?snipver=1', array(), null, true);
    endif;

    $script_version = empty($min) ? rand(1, 1000000) : SCRIPT_VERSION;

    // wp_enqueue_script('tinymce-script', 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js', array(), null, true);

    wp_enqueue_editor();

    wp_register_script('partner-script', PARTNER_URL . 'assets/js/partner' . $min . '.js', array('jquery'), $script_version, true);

    wp_enqueue_script('partner-script');

    wp_localize_script('partner-script', 'ajax_object', array('partner_nonce' => wp_create_nonce('partner-nonce'), 'ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_style('partner-style', PARTNER_URL . 'assets/css/partner.css', array(), $script_version, 'all');
}

add_action('wp_enqueue_scripts', 'partner_admin_scripts');
add_action('admin_enqueue_scripts', 'partner_admin_scripts');

function partner_admin_scripts()
{
    if (!is_user_logged_in())
        return;


    $min = (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '10.0.0.3'))) ? '' : '.min';
    $script_version = empty($min) ? rand(1, 1000000) : SCRIPT_VERSION;

    wp_register_script('partner-admin-script', PARTNER_URL . 'assets/js/partner-admin' . $min . '.js', array('jquery'), $script_version, true);
    wp_enqueue_script('partner-admin-script');
    wp_localize_script('partner-admin-script', 'ajax_object', array('partner_nonce' => wp_create_nonce('partner-nonce'), 'ajax_url' => admin_url('admin-ajax.php')));
}
