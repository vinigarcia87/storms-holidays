<?php
/**
 * Storms Framework (http://storms.com.br/)
 *
 * @author    Vinicius Garcia | vinicius.garcia@storms.com.br
 * @copyright (c) Copyright 2012-2020, Storms Websolutions
 * @license   GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package   Storms
 * @version   1.0.0
 *
 * Calculo ST Backend
 * Calculo ST backend modifications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'storms_holidays_backend' ) ) {


	class storms_holidays_backend
	{

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '1.0.0';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * @var storms_holidays_list_table
		 */
		protected $holidays_list_table;

		/**
		 * @var object
		 */
		protected $holiday = [];

		/**
		 * @var string
		 */
		protected $wp_nonce;

		/**
		 * @var array
		 */
		protected $message = [];

		/**
		 * @var array
		 */
		protected  $countries_list = [
			'BR' => 'Brasil',
		];

		/**
		 * @var array
		 */
		protected  $states_list = [
			'AC' => 'Acre (AC)',
			'AL' => 'Alagoas (AL)',
			'AP' => 'Amapá (AP)',
			'AM' => 'Amazonas (AM)',
			'BA' => 'Bahia (BA)',
			'CE' => 'Ceará (CE)',
			'DF' => 'Distrito Federal (DF)',
			'ES' => 'Espírito Santo (ES)',
			'GO' => 'Goiás (GO)',
			'MA' => 'Maranhão (MA)',
			'MT' => 'Mato Grosso (MT)',
			'MS' => 'Mato Grosso do Sul (MS)',
			'MG' => 'Minas Gerais (MG)',
			'PA' => 'Pará (PA)',
			'PB' => 'Paraíba (PB)',
			'PR' => 'Paraná (PR)',
			'PE' => 'Pernambuco (PE)',
			'PI' => 'Piauí (PI)',
			'RJ' => 'Rio de Janeiro (RJ)',
			'RN' => 'Rio Grande do Norte (RN)',
			'RS' => 'Rio Grande do Sul (RS)',
			'RO' => 'Rondônia (RO)',
			'RR' => 'Roraima (RR)',
			'SC' => 'Santa Catarina (SC)',
			'SP' => 'São Paulo (SP)',
			'SE' => 'Sergipe (SE)',
			'TO' => 'Tocantins (TO)',
		];

		/**
		 * Return an instance of this class.
		 * Singleton instantiation pattern
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if( null === self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		private function __construct() {

			add_action( 'admin_menu', array( $this, 'add_holidays_admin_page' ) );
		}

		public function emptyMessages() {
			$this->message = [
				'error' => [],
				'success' => [],
			];
		}

		public function addErrorMessage( $error_msg ) {
			if( ! isset( $this->message['error'] ) ) {
				$this->message['error'] = [];
			}
			$this->message['error'][] = $error_msg;
		}

		public function addSuccessMessage( $success_msg ) {
			if( ! isset( $this->message['success'] ) ) {
				$this->message['success'] = [];
			}
			$this->message['success'][] = $success_msg;
		}

		public function getErrorMessage() {
			if( ! isset( $this->message['error'] ) ) {
				$this->message['error'] = [];
			}
			return $this->message['error'];
		}

		public function getSuccessMessage() {
			if( ! isset( $this->message['success'] ) ) {
				$this->message['success'] = [];
			}
			return $this->message['success'];
		}

		public function add_holidays_admin_page() {

			$page_hook = add_submenu_page( 'options-general.php',
							__( 'Feriados', 'storms' ), __( 'Feriados', 'storms' ), 'manage_options',
							'storms_holidays', array( $this, 'holidays_admin_page_list' ) );

			/*
			 * The $page_hook_suffix can be combined with the load-($page_hook) action hook
			 * https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
			 *
			 * The callback below will be called when the respective page is loaded
			 */
			add_action( 'load-'.$page_hook, array( $this, 'holidays_admin_page_screen_options' ) );

			// Add / Edit page
			// https://metabox.io/create-hidden-admin-page/
			// https://wordpress.stackexchange.com/a/203156
			add_submenu_page( null,
				__( 'Adicionar / Editar Feriado', 'storms' ), __( 'Adicionar / Editar Feriado', 'storms' ), 'manage_options',
				'storms_holidays_add_edit', array( $this, 'holidays_admin_page_add_edit' )
			);

			// Select the right wp menu when using the add / edit page
			add_filter( 'submenu_file', function( $submenu_file ) {
				$screen = get_current_screen();

				if ( 'storms_holidays_add_edit' === substr( $screen->id, -strlen( 'storms_holidays_add_edit' ) ) ) {
					$submenu_file = 'storms_holidays';
				}
				return $submenu_file;
			} );

		}

		/**
		 * Screen options for the List Table
		 *
		 * Callback for the load-($page_hook_suffix)
		 * Called when the plugin page is loaded
		 *
		 * @since    1.0.0
		 */
		public function holidays_admin_page_screen_options() {
			$arguments = array(
				'label'		=>	__( 'Feriados por página', 'storms' ),
				'default'	=>	20,
				'option'	=>	'holidays_per_page'
			);
			add_screen_option( 'per_page', $arguments );
			/*
             * Instantiate the Storms Holidays List Table. Creating an instance here will allow the core WP_List_Table class to automatically
             * load the table columns in the screen options panel
             */
			$this->holidays_list_table = new storms_holidays_list_table( 'storms' );
		}

		public function holidays_admin_page_list() {

			$this->holidays_list_table->prepare_items();

			include_once( STORMS_HOLIDAYS_PATH . '/views/admin/list-holidays.php' );
		}

		/**
		 * @param $holiday
		 * @return bool|array
		 */
		private function validate_holiday( $holiday ) {
			global $wpdb;

			$error = false;

			if( empty( $holiday['date'] ) ) {
				$this->addErrorMessage( __( 'Data é um campo obrigatório', 'storms' ) );
				$error = true;
			}
			if( empty( $holiday['name'] ) ) {
				$this->addErrorMessage( __( 'Nome é um campo obrigatório', 'storms' ) );
				$error = true;
			}
			if( empty( $holiday['country'] ) ) {
				$this->addErrorMessage( __( 'País é um campo obrigatório', 'storms' ) );
				$error = true;
			}

			try {
				$oDate = new DateTime( $holiday['date'] );
				$holiday['date'] = $oDate->format( 'Y-m-d H:i:s' );
			} catch ( Exception $e ) {
				$this->addErrorMessage( __( 'Erro ao processar a data informada', 'storms' ) );
				$error = true;
			}

			if( ! empty( $holiday['city'] ) && empty( $holiday['state'] ) ) {
				$this->addErrorMessage( __( 'Informe o estado ao qual a cidade pertence', 'storms' ) );
				$error = true;
			}

			$holiday['type'] = ((!empty($holiday['state']) && !empty($holiday['city'])) ? 'Municipal' :
				((!empty($holiday['state'])) ? 'Estadual' : 'Nacional'));

			// Check if there is a existent holiday on the same date, country, state and city
			$wpdb_table = $wpdb->prefix . 'storms_holidays';
			$where = 'date = %s AND country = %s AND state = %s AND city = %s';
			$query = "SELECT date, name, type, country, state, city, ID
                  	  FROM $wpdb_table WHERE $where";
			$query = $wpdb->prepare( $query, [ $holiday['date'], $holiday['country'], $holiday['state'], $holiday['city'] ] );

			// Query output_type will be an associative array with ARRAY_A.
			$existent_holiday = $wpdb->get_results( $query, ARRAY_A  );

			if( ! empty( $existent_holiday ) ) {
				$infos = date( 'd/m/Y', strtotime( $existent_holiday[0]['date'] ) ) . ' - ' . $existent_holiday[0]['name'] . ' (feriado ' . $existent_holiday[0]['type'] . ')';
				$this->addErrorMessage( sprintf( __( 'Já existe um feriado cadastrado para este dia: %s', 'storms' ), $infos ) );
				$error = true;
			}

			if( $error ) {
				return false;
			}
			return $holiday;
		}

		public function holidays_admin_page_add_edit() {
			global $wpdb;

			$this->emptyMessages();

			if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'storms_holidays' ) ) {

				$holiday = [
					'date'    => sanitize_text_field($_REQUEST['date']),
					'name'    => sanitize_text_field($_REQUEST['name']),
					'country' => sanitize_text_field($_REQUEST['country']),
					'state'   => sanitize_text_field($_REQUEST['state']),
					'city'    => sanitize_text_field($_REQUEST['city']),
				];

				// Validate the holiday
				$holiday = $this->validate_holiday( $holiday );

				if( false !== $holiday ) {

					// Insert the holiday
					$wpdb_table = $wpdb->prefix . 'storms_holidays';
					$inserted = $wpdb->insert($wpdb_table, $holiday);

					if ($inserted) {
						$holiday_id = $wpdb->insert_id;
						$this->addSuccessMessage( __( 'Feriado criado com sucesso', 'storms' ) );
					} else {
						$this->addErrorMessage( __( 'Erro ao criar o novo feriado', 'storms' ) );
					}
				}
			}

			$this->wp_nonce = wp_create_nonce( 'storms_holidays' );
			$this->holiday = [
				'ID'      => '',
				'date'    => '',
				'name'    => '',
				'country' => '',
				'state'   => '',
				'city'    => '',
			];

			include_once( STORMS_HOLIDAYS_PATH . '/views/admin/add_edit_holidays.php' );
		}

	}

	add_action( 'plugins_loaded', array( 'storms_holidays_backend', 'get_instance' ) );

}
