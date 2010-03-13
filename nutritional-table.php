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
 * @package AjaxCommentUpdate
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
			'energy' => 'Energy',
			'protein' => 'Protein',
			'carbs' => 'Carbohydrates',
			'carbs_sugars' => '&nbsp;of which sugars',
			'fat' => 'Fats',
			'fat_sat' => '&nbsp;of which saturates',
			'fibre' => 'Fibre',
			'sodium' => 'Sodium',
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
	public function shortcode_nutritional_table() {
		global $post;
		$vars = get_post_meta( $post->ID, '_nutritional_table', true );
		$vars[ 'key' ] = $this->elements;
		return $this->capture( 'shortcode-nutritional-table.php', $vars );
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

} // END AjaxCommentUpdate class 

$nutritional_tables = new NutritionalTables();

?>