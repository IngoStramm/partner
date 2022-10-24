<?php

function partner_register_user_profile_metabox()
{
    $prefix = 'partner_user_';

    $cmb_user = new_cmb2_box(array(
        'id'               => $prefix . 'edit',
        'title'            => esc_html__('User Profile Metabox', 'partner'), // Doesn't output for user boxes
        'object_types'     => array('user'), // Tells CMB2 to use user_meta vs post_meta
        'show_names'       => true,
        'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Cliente', 'partner'),
        'desc'    => esc_html__('Selecione qual é o cliente deste usuário.', 'partner'),
        'id'      => $prefix . 'cliente',
        'type'    => 'select',
        'options' => 'partner_list_clientes'
    ));

    $cmb_user->add_field(array(
        'name'    => esc_html__('Shortcode Google Drive', 'partner'),
        // 'desc'    => esc_html__('Selecione qual é o cliente deste usuário.', 'partner'),
        'id'      => $prefix . 'shortcode_gdrive',
        'type'    => 'text'
    ));
}

add_action('cmb2_admin_init', 'partner_register_user_profile_metabox');

/**
 * partner_list_clientes
 *
 * @return array
 */
function partner_list_clientes()
{
    $googlesheet_url = partner_get_option('googlesheet_url');

    if (!$googlesheet_url || !is_string($googlesheet_url))
        return;

    $googlesheet_url = html_entity_decode($googlesheet_url);
    $rows = partner_return_googlesheet_data($googlesheet_url);

    if (is_null($rows) || empty($rows))
        return;

    if (!is_array($rows))
        return $rows;

    $rows = array_slice($rows, 1);
    $clientes = [];

    $clientes[] = __('Selecione um cliente', 'partner');

    foreach ($rows as $row) {
        if (!in_array($row[0], $clientes))
            $clientes[$row[0]] = $row[0];
    }
    return $clientes;
}

// add_action('wp_head', 'partner_list_clientes');
