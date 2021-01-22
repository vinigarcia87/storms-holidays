
<div class="wrap">

	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<a href="<?php echo esc_html( add_query_arg( [ 'page' => 'storms_holidays_add_edit' ], admin_url( 'options-general.php' ) ) ) ?>" class="page-title-action">
		<?php echo esc_html_x( 'Adicionar Novo', 'storms' ); ?></a>
	<hr class="wp-header-end">

	<div id="storms-wp-list-table-demo">
		<div id="storms-post-body">
			<form id="storms-user-list-form" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php
				$this->holidays_list_table->search_box( __( 'Procurar', 'storms' ), 'storms-holiday-find' );
				$this->holidays_list_table->display();
				?>
			</form>
		</div>
	</div>

</div><!-- .wrap -->
