<?php
function partner_adminbar_delete_transients_button(WP_Admin_Bar $admin_bar)
{
    $admin_bar->add_menu(array(
        'id'    => 'partner-delete-transients-btn-wrapper',
        'parent' => null,
        'group'  => null,
        'title' => __('Atualizar dados da planilha', 'partner'),
        'href'  => '#',
    ));
}

add_action('admin_bar_menu', 'partner_adminbar_delete_transients_button', 999);

function partner_adminbar_style()
{
?>
    <style>
        #wp-admin-bar-partner-delete-transients-btn-wrapper .ab-item:before {
            content: '\f495';
            top: 2px;
        }
    </style>
<?php
}

add_action('wp_head', 'partner_adminbar_style');
add_action('admin_head', 'partner_adminbar_style');
