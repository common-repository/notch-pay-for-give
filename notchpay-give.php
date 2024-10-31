<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://notchpay.co
 * @since   1.0.3
 * @package NotchPay_Give
 *
 * @wordpress-plugin
 * Plugin Name:       Notch Pay for Give
 * Plugin URI:        http://wordpress.org/plugins/notch-pay-for-give
 * Description:       Notch Pay integration for accepting donation with local payments and mobile money
 * Version:           1.0.5
 * Author:            Notch Pay
 * Author URI:        https://notchpay.co
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       notchpay-give
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('NP_GIVE_PLUGIN_NAME_VERSION', '2.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-notchpay-give-activator.php
 */
function notchpay_give_activate()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-notchpay-give-activator.php';
    NotchPay_Give_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-notchpay-give-deactivator.php
 */
function notchpay_give_deactivate()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-notchpay-give-deactivator.php';
    NotchPay_Give_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'notchpay_give_activate');
register_deactivation_hook(__FILE__, 'notchpay_give_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-notchpay-give.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function notchpay_give_run()
{

    $plugin = new NotchPay_Give();
    $plugin->run();
}
notchpay_give_run();
