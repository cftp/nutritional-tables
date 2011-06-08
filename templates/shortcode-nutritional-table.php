<h2><?php _e( 'Nutrition Information', 'nt' ); ?></h2>

<table class="nutritional_table" summary="<?php esc_attr_e( 'Nutrition Information', 'nt' ); ?>">
	<thead>
		<tr>
			<th scope="col"><?php _e( 'Typical Values', 'nt' ); ?></th>
			<th scope="col"><?php _e( 'Per 100g', 'nt' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $elements AS $element => $value ) : ?>
		<tr>
			<th scope="row"><?php _e( $key[ $element ], 'nt' ); ?></th>
			<td><?php echo esc_html( $value ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>