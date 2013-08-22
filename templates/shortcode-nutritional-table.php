<h2><?php _e( 'Nutrition Information', 'nuttab' ); ?></h2>

<table class="nutritional_table" summary="<?php esc_attr_e( 'Nutrition Information', 'nuttab' ); ?>">
	<thead>
		<tr>
			<th scope="col"><?php _e( 'Typical Values', 'nuttab' ); ?></th>
			<th scope="col"><?php _e( 'Per 100g', 'nuttab' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $elements AS $element => $value ) : ?>
		<tr>
			<th scope="row"><?php _e( $key[ $element ], 'nuttab' ); ?></th>
			<td><?php echo esc_html( $value ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>