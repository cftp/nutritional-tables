<?php 
/*
Plugin Name: Nutritional Tables
Plugin URI: http://www.simonwheatley.co.uk/wordpress/nt
Description: Adds a metabox to enter nutritional tables for a page, you can then insert the tables with a shortcode. One table per page.
Version: 0.9
Author: Sweet Interaction Ltd
Author URI: http://www.sweetinteraction.com/ 
*/

/**
 * WordPress plugin which adds a metabox to enter nutritional tables for a
 * page, you can then insert the tables with a shortcode. One table per page.
 *
 * @package NutritionalTables
 */

/*  Copyright 2010 Simon Wheatley

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require_once( 'plugin.php' );

/**
 * WordPress plugin class which adds a metabox to enter nutritional tables for a 
 * page, you can then insert the tables with a shortcode. One table per page.
 *
 * @package default
 * @author Simon Wheatley
 **/
class NutritionalTables extends NutritionalTables_Plugin {
	
	/**
	 * 
	 *
	 * @var array
	 **/
	protected $elements;

	/**
	 * Initiate!
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function __construct() {
		$this->setup();
		$this->add_meta_box( 'nutritional_table', __('Nutritional Table'), 'metabox_nutritional_table', 'page', 'normal', 'high' );
		$this->add_action( 'save_post', null, null, 2 );
		$this->add_shortcode( 'nutritional_table', 'shortcode_nutritional_table' );
		$this->elements = array(
			'energy' => __( 'Energy', 'nt'),
			'protein' => __( 'Protein', 'nt'),
			'carbs' => __( 'Carbohydrates', 'nt'),
			'carbs_sugars' => __( ' - of which sugars', 'nt'),
			'fat' => __( 'Fats', 'nt'),
			'fat_sat' => __( ' - of which saturates', 'nt'),
			'fibre' => __( 'Fibre', 'nt'),
			'sodium' => __( 'Sodium', 'nt'),
		);
	}
	
	// HOOKS AND ALL THAT
	// ==================
	
	/**
	 * Provides a metabox to enter nutritional information.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function metabox_nutritional_table() {
		wp_nonce_field( 'nutritional_table', '_nutritional_table_nonce' );
		global $post;
		$vars = get_post_meta( $post->ID, '_nutritional_table', true );
		$this->render_admin( 'metabox.php', $vars );
	}
	
	/**
	 * Hooks the WordPress save post action (also called when pages are saved).
	 *
	 * @param int $post_ID The ID of the post being saved 
	 * @param object $post The post object being saved 
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function save_post( $post_ID, $post ) {
		$this->process_metabox( $post_ID );
	}
	
	/**
	 * Adds the shortcode into the page.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function shortcode_nutritional_table( $attr, $content = null ) {
		if ( is_array( $attr ) && in_array( 'for_my_children', $attr ) )
			$attr[ 'for_my_children' ] = true;
		
		extract(shortcode_atts(array(
			'for_my_children'	=> false,
		), $attr));
		
		if ( $for_my_children )
			return $this->nutrition_table_for_children();
		else
			return $this->nutrition_table();
	}

	// UTILITIES
	// =========

	/**
	 * Maybe process the metabox.
	 *
	 * @param int $post_ID The post ID for the metabox's post 
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function process_metabox( $post_ID ) {
		// Something to do?
		$do_something = (bool) @ $_POST[ '_nutritional_table_nonce' ];
		if ( ! $do_something ) return;
		// Authorised to do it?
		check_admin_referer( 'nutritional_table', '_nutritional_table_nonce' );
		// OK. Let's go...
		$meta = get_post_meta( $post_ID, '_nutritional_table', true );
		foreach ( $this->elements AS $key => $name ) {
			$meta[ $key ] = @ $_POST[ 'nt_' . $key ];
		}
		update_post_meta( $post_ID, '_nutritional_table', $meta );
	}
	
	/**
	 * Render the nutrition table for the current post/page.
	 *
	 * @return string The HTML for the nutrition table
	 * @author Simon Wheatley
	 **/
	protected function nutrition_table() {
		$vars = array();
		$vars[ 'elements' ] = get_post_meta( get_the_ID(), '_nutritional_table', true );
		$vars[ 'key' ] = $this->elements;
		return $this->capture( 'shortcode-nutritional-table.php', $vars );
	}
	
	/**
	 * Render the combined nutrition table for the children of the current post/page.
	 *
	 * @return string The HTML for the nutrition table
	 * @author Simon Wheatley
	 **/
	protected function nutrition_table_for_children() {
		global $wpdb;
		$sql = " SELECT ID FROM $wpdb->posts WHERE post_type = 'page' AND post_parent = %d AND post_type = 'page' AND post_status = 'publish' ORDER BY menu_order ASC ";
		$prepared_sql = $wpdb->prepare( $sql, get_the_ID() );
		$kids = $wpdb->get_col( $prepared_sql );

		$vars = array(
			'products' => array(),
		);
		foreach( $kids AS & $ID )
			$vars[ 'products' ][] = array(
				'title' => get_the_title( $ID ),
				'elements' => get_post_meta( $ID, '_nutritional_table', true ),
			);
		$vars[ 'key' ] = $this->elements;
		return $this->capture( 'shortcode-nutritional-table-for-children.php', $vars );
	}

} // END AjaxCommentUpdate class 

$nutritional_tables = new NutritionalTables();

?>