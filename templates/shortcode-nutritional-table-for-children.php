<h2><?php _e( 'Nutrition Information', 'nuttab' ); ?></h2>

<table class="nutritional_table" summary="<?php esc_attr_e( 'Nutrition Information', 'nuttab' ); ?>">
	<thead>
		<tr>
			<td><!-- --></td>
			<th scope="col" class="per_x" colspan="<?php esc_attr_e( count( $products ) ) ?>"><?php _e( 'Per 100g', 'nuttab' ); ?></th>
		</tr>
		<tr>
			<th scope="col"><?php _e( 'Typical Values', 'nuttab' ); ?></th>
			<?php foreach( $products AS & $product ) : ?>
				<th scope="col"><?php echo $product[ 'title' ]; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $this->get_elements() AS $key => $name ) : ?>
		<tr>
			<th scope="row"><?php _e( $name ); ?></th>
			<?php foreach( $products AS & $product ) : ?>
				<td><?php echo $product[ 'elements' ][ $key ]; ?></td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>