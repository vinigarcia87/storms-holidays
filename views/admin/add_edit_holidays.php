<div class="wrap">

	<h1 class="wp-heading-inline"><?php echo esc_html( __( 'Adicionar / Editar Feriado', 'storms' ) . get_admin_page_title() ); ?></h1>

	<?php

	$msgs = ( empty( $this->getErrorMessage() ) ? $this->getSuccessMessage() : $this->getErrorMessage() );
	if( ! empty( $msgs ) ) : ?>
		<div id="message" class="notice notice-<?php echo esc_html( empty( $this->getErrorMessage() ) ? 'success' : 'error' ) ?>">
			<?php foreach( $msgs as $msg ): ?>
				<p><?php echo esc_html( $msg ) ?></p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<form id="form" method="POST">
		<input type="hidden" name="nonce" value="<?php esc_attr_e( $this->wp_nonce ) ?>"/>

		<input type="hidden" name="id" value="<?php esc_attr_e( $this->holiday['ID'] ) ?>"/>

		<p>
			<label for="date" style="margin-right: 10px; width: 100px; display: inline-block;"><?php _e( 'Data', 'storms' ) ?></label>
			<input id="date" name="date" type="date" value="<?php esc_attr_e( $this->holiday['name'] ) ?>"  min="<?php esc_attr_e( date( 'Y' ) ) ?>-01-01" required style="width: 225px;">
		</p>

		<p>
			<label for="name" style="margin-right: 10px; width: 100px; display: inline-block;"><?php _e( 'Nome', 'storms' ) ?></label>
			<input id="name" name="name" type="text" value="<?php esc_attr_e( $this->holiday['name'] ) ?>" required style="width: 225px;">
		</p>

		<p>
			<label for="country" style="margin-right: 10px; width: 100px; display: inline-block;"><?php _e( 'País', 'storms' ) ?></label>
			<select id="country" name="country" required style="width: 225px;">
				<?php foreach ( $this->countries_list as $key => $value ) : ?>
					<option value="<?php esc_attr_e( $key ) ?>" <?php esc_attr_e( $key === $this->holiday['country'] ? 'selected' : '' ) ?>><?php esc_attr_e( $value ) ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="state" style="margin-right: 10px; width: 100px; display: inline-block;"><?php _e( 'Estado', 'storms' ) ?></label>
			<select id="state" name="state" style="width: 225px;">
				<option value="">Todos os Estados</option>

				<?php foreach ( $this->states_list as $key => $value ) : ?>
					<option value="<?php esc_attr_e( $key ) ?>" <?php esc_attr_e( $key === $this->holiday['state'] ? 'selected' : '' ) ?>><?php esc_attr_e( $value ) ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="city" style="margin-right: 10px; width: 100px; display: inline-block;"><?php _e( 'Cidade', 'storms' ) ?></label>
			<input id="city" name="city" type="text" value="<?php esc_attr_e( $this->holiday['city'] ) ?>" style="width: 225px;">
		</p>

		<br>
		<?php //submit_button() ?>
		<input type="submit" name="submit" value="<?php _e( 'Salvar feriado', 'storms' ) ?>" id="submit" class="button-primary">

		<a href="<?php echo esc_html( add_query_arg( [ 'page' => 'storms_holidays' ], admin_url( 'options-general.php' ) ) ) ?>" style="margin-left: 20px;">
			<?php _e( 'Voltar para lista de feriados', 'storms' ) ?></a>
	</form>

</div><!-- .wrap -->
