<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link  https://notchpay.co
 * @since 1.0.0
 *
 * @package    NotchPay_Give
 * @subpackage NotchPay_Give/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    NotchPay_Give
 * @subpackage NotchPay_Give/includes
 * @author     Notch Pay <hello@notchpay.co>
 */
class NotchPay_Give_i18n
{


    /**
     * Load the plugin text domain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() 
    {

        load_plugin_textdomain(
            'notchpay-give',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }



}
