<?php

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class for displaying registered WordPress Holidays
 * in a WordPress-like Admin Table with row actions to
 * perform user meta operations
 *
 * @see https://premium.wpmudev.org/blog/wordpress-admin-tables/
 * @see https://developer.wordpress.org/reference/classes/wp_list_table/
 */
class storms_holidays_list_table extends WP_List_Table {

	public function get_columns() {
		$table_columns = array(
			'cb'				=> '<input type="checkbox" />', // To display the checkbox.
			'date'		=> __( 'Data', 'storms' ),
			'name'		=> __( 'Nome', 'storms' ),
			'type'		=> __( 'Tipo', 'storms' ),
			'country' 	=> __( 'País', 'storms' ),
			'state'		=> __( 'Estado', 'storms' ),
			'city'		=> __( 'Cidade', 'storms' ),
		);
		return $table_columns;
	}

	public function no_items() {
		_e( 'Nenhum feriado cadastrado', 'storms' );
	}

	/**
	 * Query, filter data, handle sorting, pagination, and any other data-manipulation required prior to rendering
	 */
	public function prepare_items() {

		// Check if a search was performed.
		$holiday_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		// Check and process any actions such as bulk actions.
		$this->handle_table_actions();

		// Used by WordPress to build and fetch the _column_headers property
		$this->_column_headers = $this->get_column_info();
		$table_data = $this->fetch_table_data();

		// Code to handle data operations like sorting and filtering
		if( $holiday_search_key ) {
			$table_data = $this->filter_table_data( $table_data, $holiday_search_key );
		}

		// Start by assigning your data to the items variable
		$this->items = $table_data;

		// Code to handle pagination
		$this->items = $this->paginate_table_data( $table_data );
	}

	/**
	 * Returns an associative array containing the bulk action.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		/*
         * on hitting apply in bulk actions the url paramas are set as
         * ?action=bulk-download&paged=1&action2=-1
         *
         * action and action2 are set based on the triggers above and below the table
         */
		$actions = array(
			'bulk-remove' => __( 'Remover feriados', 'storms' ),
		);
		return $actions;
	}

	public function handle_table_actions() {

		$this->handle_table_actions_single();
		$this->handle_table_actions_bulk();
	}

	public function handle_table_actions_single() {
		global $wpdb;

		// Check for individual row actions
		$the_table_action = $this->current_action();

		if ( 'remove' === $the_table_action ) {
			// Verify the nonce
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			if ( wp_verify_nonce( $nonce, 'remove_holiday_nonce' ) ) {

				try {
					$holidays_ids = [ $_REQUEST['holiday_id'] ];

					$wpdb_table = $wpdb->prefix . 'storms_holidays';
					$sql = "DELETE FROM $wpdb_table WHERE ID IN ( %s )";
					$wpdb->query( $wpdb->prepare( $sql, implode( ', ', $holidays_ids ) ) );

				} catch (Exception $e) {
					return __( 'Erro ao remover o feriado selecionado', 'storms' );
				}

			}
		}
	}

	public function handle_table_actions_bulk() {
		global $wpdb;

		/*
         * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
         * action - is set if checkbox from top-most select-all is set, otherwise returns -1
         * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
         */
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'bulk-remove' ) ||
			( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk-remove' ) ) {

			/*
             * Note: the nonce field is set by the parent class
             * wp_nonce_field( 'bulk-' . $this->_args['plural'] );
             */
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			if ( wp_verify_nonce( $nonce, ( 'bulk-' . $this->_args['plural'] ) ) ) { // verify the nonce.

				try {
					$holidays_ids = $_REQUEST['holidays'];

					$wpdb_table = $wpdb->prefix . 'storms_holidays';
					$sql = "DELETE FROM $wpdb_table WHERE ID IN ( %s )";
					$wpdb->query( $wpdb->prepare( $sql, implode( ', ', $holidays_ids ) ) );

				} catch (Exception $e) {
					return __( 'Erro ao remover os feriados selecionados', 'storms' );
				}
			}
		}
	}

	/*
	 * Method for rendering the date column.
	 * Adds row action links to the date column.
	 */
	protected function column_date( $item ) {

		// Row action to view usermeta
		$query_args_remove_holiday = array(
			'page'			=>  wp_unslash( $_REQUEST['page'] ),
			'action'		=> 'remove',
			'holiday_id'	=> absint( $item['ID'] ),
			'_wpnonce'		=> wp_create_nonce( 'remove_holiday_nonce' ),
		);
		$admin_page_url =  admin_url( 'options-general.php' );
		$remove_holiday_link = esc_url( add_query_arg( $query_args_remove_holiday, $admin_page_url ) );
		$actions['remove'] = '<a href="' . $remove_holiday_link . '">' . __( 'Remover', 'storms' ) . '</a>';

		// similarly add row actions for add usermeta.

		$row_value = '<strong>' . $item['date'] . '</strong>';
		return $row_value . $this->row_actions( $actions );
	}

	/**
	 * Filter the table data based on the search key
	 *
	 * @param $table_data
	 * @param $search_key
	 * @return array
	 */
	public function filter_table_data( $table_data, $search_key ) {

		$filtered_table_data = array_values( array_filter( $table_data, function( $row ) use( $search_key ) {
			foreach( $row as $row_val ) {
				if( stripos( $row_val, $search_key ) !== false ) {
					return true;
				}
			}
		} ) );

		return $filtered_table_data;

	}

	public function paginate_table_data( $table_data ) {

		// TODO 'holidays_per_page' configuration is not working
		$holidays_per_page = $this->get_items_per_page( 'holidays_per_page', 25 );
		$table_page = $this->get_pagenum();

		// Set the pagination arguments
		$total_holidays = count( $table_data );
		$this->set_pagination_args( array (
			'total_items'  => $total_holidays,
			'per_page'     => $holidays_per_page,
			'total_pages'  => ceil( $total_holidays / $holidays_per_page )
		) );

		// Provide the ordered data to the List Table
		// We need to manually slice the data based on the current pagination
		return array_slice( $table_data, ( ( $table_page - 1 ) * $holidays_per_page ), $holidays_per_page );
	}

	public function fetch_table_data() {
		global $wpdb;

		$wpdb_table = $wpdb->prefix . 'storms_holidays';
		$orderby = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'date';
		$order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'ASC';
		$current_year = date( 'Y' );

		$query = "SELECT DATE_FORMAT(date,'%d/%m/%Y') as date, name, type, country, state, city, ID
                  FROM $wpdb_table
				  WHERE DATE_FORMAT(date,'%Y') >= $current_year
                  ORDER BY $orderby $order";

		// Query output_type will be an associative array with ARRAY_A.
		$holidays_results = $wpdb->get_results( $query, ARRAY_A  );

		// Return result array to prepare_items.
		return $holidays_results;
	}

	public function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	/**
	 * Get value for checkbox column.
	 *
	 * @param object $item  A row's data.
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<label class="screen-reader-text" for="holiday_' . $item['ID'] . '">' . sprintf( __( 'Select %s' ), $item['name'] ) . '</label>'
			. "<input type='checkbox' name='holidays[]' id='holiday_{$item['ID']}' value='{$item['ID']}' />"
		);
	}

	/**
	 * Specify which columns should have the sort icon.
	 * Actual sorting still needs to be done by prepare_items
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array (
			'ID' 		=> array( 'ID', true ),
			'date'		=> 'date',
			'country'	=> 'country',
			'state'		=> 'state',
			'city'		=> 'city',
		);
		return $sortable_columns;
	}

}
