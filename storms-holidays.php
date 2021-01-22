<?php
/**
 * Plugin Name: Storms Holidays
 * Plugin URI: https://github.com/vinigarcia87/storms-holidays
 * Description: Wordpress Holidays management
 * Author: Storms Websolutions - Vinicius Garcia
 * Author URI: http://storms.com.br/
 * Copyright: (c) Copyright 2012-2020, Storms Websolutions
 * License: GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * Version: 1.0
 *
 * Text Domain: storms
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define BasePath for Storms Framework
if ( !defined( 'STORMS_HOLIDAYS_PATH' ) ) {
	define( 'STORMS_HOLIDAYS_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Criamos as tabelas necessarias para o plugin
 */
function storms_holidays_install() {
	include __DIR__ . '/install.php';
}
register_activation_hook( __FILE__, 'storms_holidays_install' );

if ( is_admin() ) {
	include STORMS_HOLIDAYS_PATH . '/includes/storms-holidays-list-table.php';
	include STORMS_HOLIDAYS_PATH . '/includes/storms-holidays-backend.php';
}
