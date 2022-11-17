<?php

/**
 * Plugin Name: Partner
 * Plugin URI: https://agencialaf.com
 * Description: Descrição do Partner.
 * Version: 0.0.14
 * Author: Ingo Stramm
 * Text Domain: partner
 * License: GPLv2
 */

defined('ABSPATH') or die('No script kiddies please!');

define('PARTNER_DIR', plugin_dir_path(__FILE__));
define('PARTNER_URL', plugin_dir_url(__FILE__));

function partner_debug($debug)
{
    echo '<pre>';
    var_dump($debug);
    echo '</pre>';
}

require_once 'tgm/tgm.php';
require_once 'classes/classes.php';
require_once 'scripts.php';
require_once 'partner-settings.php';
require_once 'partner-functions.php';
require_once 'partner-adminbar.php';
require_once 'partner-cronograma.php';
require_once 'partner-user.php';
require_once 'partner-shortcode.php';

require 'plugin-update-checker-4.10/plugin-update-checker.php';
$updateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://raw.githubusercontent.com/IngoStramm/partner/master/info.json',
    __FILE__,
    'partner'
);
