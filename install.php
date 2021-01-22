<?php
/**
 * Storms WooCommerce Calculo ST Install
 *
 * Installing Storms WooCommerce Calculo ST
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Tabela de feriados - storms_holidays
 * Id 		INT NOT NULL AUTO_INCREMENT,
 * date 	varchar(3),						// Data do feriado
 * name 	varchar(100),					// Nome do feriado
 * type 	varchar(30),					// Tipo do feriado: Municipal, Estadual, Federal
 * country 	varchar(3),						// Pais
 * uf 		varchar(3),						// Estado
 * city 	varchar(30),					// Cidade
 */
function storms_holidays_create_database_table() {
	global $wpdb;

	$holidays_db_version = '1.0.0';

	$holidays_table = $wpdb->prefix . 'storms_holidays';
	$charset_collate = $wpdb->get_charset_collate();

	// Check to see if the table exists already, if not, then create it
	if( $wpdb->get_var( "SHOW TABLES LIKE '{$holidays_table}'" ) != $holidays_table ) {

		$sql = "CREATE TABLE $holidays_table (
				  ID INT NOT NULL AUTO_INCREMENT,
				  date date NOT NULL,
				  name varchar(50) NOT NULL,
				  type varchar(10) NOT NULL,
				  country varchar(3) NOT NULL,
				  state varchar(3),
				  city varchar(30),
				  PRIMARY KEY ( Id )
    			)    $charset_collate;";

		// Modifies the database based on specified SQL statements
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta($sql);

		// Keep the version of our current database table
		add_option( 'storms_holidays_db_version', $holidays_db_version );
	}
}

storms_holidays_create_database_table();
