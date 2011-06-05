<?php wp_nonce_field( 'nutritional_table', '_nutritional_table_nonce' ); ?>
<p>You can insert this table into your page by typing <code>[nutritional_table]</code> into the editor above. The values should be per 100g.</p>
	<?php $i = 50; ?>
	<?php foreach( $this->elements AS $key => $name ) : ?>
		<p>
			<label for="<?php esc_attr_e( 'nt_' . $key ) ?>"><?php echo $name; ?></label><br />
			<input type="text" name="<?php esc_attr_e( 'nt_' . $key ) ?>" value="<?php esc_attr_e( $$key ); ?>" id="<?php esc_attr_e( 'nt_' . $key ) ?>" tabindex="<?php esc_attr_e( $i++ ); ?>" /><br />
		</p>
	<?php endforeach; ?>
