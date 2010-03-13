<?php 

// ======================================================================================
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
// 
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// ======================================================================================
// @author     Simon Wheatley (http://simonwheatley.co.uk)
// @version    1.0
// @copyright  Copyright &copy; 2010 Simon Wheatley, All Rights Reserved
// @copyright  Some parts Copyright &copy; 2007 John Godley, All Rights Reserved
// ======================================================================================
// 1.0     - Initial release
// 1.01    - Added add_shortcode
// ======================================================================================


/**
 * Wraps up several useful functions for WordPress plugins and provides a method to separate
 * display HTML from PHP code.
 *
 * <h4>Display Rendering</h4>
 * 
 * The class uses a similar technique to Ruby On Rails views, whereby the display HTML is kept
 * in a separate directory and file from the main code.  A display is 'rendered' (sent to the browser)
 * or 'captured' (returned to the calling function).
 * 
 * Template files are separated into two areas: admin and user.  Admin templates are only for display in
 * the WordPress admin interface, while user templates are typically for display on the site (although neither
 * of these are enforced).  All templates are PHP code, but are referred to without .php extension.
 * 
 * The reason for this separation is that one golden rule of plugin creation is that someone will 
 * always want to change the formatting and style of your output.  Rather than forcing them to 
 * modify the plugin (bad), or modify files within the plugin (equally bad), the class allows 
 * user templates to be overridden with files contained within the theme.
 *
 * An additional benefit is that it leads to code re-use, especially with regards to Ajax (i.e. 
 * your display code can be called from many locations)
 * 
 * @package WordPress plugin base library
 * @author Simon Wheatley
 * @copyright Copyright (C) Simon Wheatley (except where noted)
 **/
class NutritionalTables_Plugin {

	/**
	 * 
	 *
	 * @var string
	 **/
	protected $plugin_folder;

	/**
	 * Initiate!
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function setup() {
		$this->plugin_folder = dirname( plugin_basename( __FILE__ ) );
	}
	
	/**
	 * Register a WordPress action and map it back to the calling object
	 *
	 * @param string $action Name of the action
	 * @param string $function Function name (optional)
	 * @param int $priority WordPress priority (optional)
	 * @param int $accepted_args Number of arguments the function accepts (optional)
	 * @return void
	 * @author © John Godley
	 **/
	function add_action ($action, $function = '', $priority = 10, $accepted_args = 1) {
		add_action ($action, array (&$this, $function == '' ? $action : $function), $priority, $accepted_args);
	}


	/**
	 * Register a WordPress filter and map it back to the calling object
	 *
	 * @param string $action Name of the action
	 * @param string $function Function name (optional)
	 * @param int $priority WordPress priority (optional)
	 * @param int $accepted_args Number of arguments the function accepts (optional)
	 * @return void
	 * @author © John Godley
	 **/
	function add_filter ($filter, $function = '', $priority = 10, $accepted_args = 1) {
		add_filter ($filter, array (&$this, $function == '' ? $filter : $function), $priority, $accepted_args);
	}


	/**
	 * Special activation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location (i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'activate'
	 * @return void
	 * @author © John Godley
	 **/
	function register_activation ($pluginfile, $function = '') {
		add_action ('activate_'.basename (dirname ($pluginfile)).'/'.basename ($pluginfile), array (&$this, $function == '' ? 'activate' : $function));
	}
	
	
	/**
	 * Special deactivation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location (i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'deactivate'
	 * @return void
	 * @author © John Godley
	 **/
	function register_deactivation ($pluginfile, $function = '') {
		add_action ('deactivate_'.basename (dirname ($pluginfile)).'/'.basename ($pluginfile), array (&$this, $function == '' ? 'deactivate' : $function));
	}

	/**
	 * Renders a template, looking first for the template file in the theme directory
	 * and afterwards in this plugin's /theme/ directory.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function render( $template_file, $vars = null ) {
		// Maybe override the template with our own file
		$template_file = $this->locate_template( $template_file );
		
		// Ensure we have the same vars as regular WP templates
		global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if ( is_array($wp_query->query_vars) )
			extract($wp_query->query_vars, EXTR_SKIP);

		// Plus our specific template vars
		if ( is_array( $vars ) )
			extract( $vars );
		
		require( $template_file );
	}

	/**
	 * Renders an admin template from this plugin's /templates-admin/ directory.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function render_admin( $template_file, $vars = null ) {
		// Plus our specific template vars
		if ( is_array( $vars ) )
			extract( $vars );
		
		// Try to render
		if ( file_exists( $this->plugin_dir( "templates_admin/$template_file" ) ) ) {
			require( $this->plugin_dir( "templates_admin/$template_file" ) );
		} else {
			$msg = sprintf( __( "This plugin template could not be found: %s" ), $this->plugin_dir( "templates_admin/$template_file" ) );
			error_log( "Plugin template error: $msg" );
			echo "<p style='background-color: #ffa; border: 1px solid red; color: #300; padding: 10px;'>$msg</p>";
		}
			
	}
	
	/**
	 * Renders a section of user display code, returning the rendered markup.
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return void
	 * @author © John Godley
	 **/
	protected function capture( $template_file, $vars = null ) {
		ob_start();
		$this->render( $template_file, $vars );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	/**
	 * Takes a filename and attempts to find that in the designated plugin templates
	 * folder in the theme (defaults to main theme directory, but uses a custom filter
	 * to allow theme devs to specify a sub-folder for all plugin template files using
	 * this system).
	 * 
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH to cope with themes which
	 * inherit from a parent theme by just overloading one file.
	 *
	 * @param string $template_file A template filename to search for 
	 * @return string The path to the template file to use
	 * @author Simon Wheatley
	 **/
	protected function locate_template( $template_file ) {
		$located = '';
		$sub_dir = apply_filters( 'sw_plugin_tpl_dir', '' );
		if ( $sub_dir )
			$sub_dir = trailingslashit( $sub_dir );
		// If there's a tpl in a (child theme or theme with no child)
		if ( file_exists( STYLESHEETPATH . "/$sub_dir" . $template_file ) )
			return STYLESHEETPATH . "/$sub_dir" . $template_file;
		// If there's a tpl in the parent of the current child theme
		else if ( file_exists( TEMPLATEPATH . "/$sub_dir" . $template_file ) )
			return TEMPLATEPATH . "/$sub_dir" . $template_file;
		// Fall back on the bundled plugin template (N.B. no filtered subfolder involved)
		else if ( file_exists( $this->plugin_dir( "templates/$template_file" ) ) )
			return $this->plugin_dir( "templates/$template_file" );
		// Oh dear. We can't find the template.
		$msg = sprintf( __( "This plugin template could not be found: %s" ), $this->plugin_dir( "templates/$template_file" ) );
		error_log( "Verify Age error: $msg" );
		echo "<p style='background-color: #ffa; border: 1px solid red; color: #300; padding: 10px;'>$msg</p>";
	}
	
	/**
	 * Register a WordPress meta box
	 *
	 * @param string $id ID for the box, also used as a function name if none is given
	 * @param string $title Title for the box
	 * @param int $page WordPress priority (optional)
	 * @param string $function Function name (optional)
	 * @param string $context e.g. 'advanced' or 'core' (optional)
	 * @param int $priority Priority, rough effect on the ordering (optional)
	 * @return void
	 * @author © John Godley
	 **/
	function add_meta_box($id, $title, $function = '', $page, $context = 'advanced', $priority = 'default')
	{
		require_once( ABSPATH . 'wp-admin/includes/template.php' );
		add_meta_box( $id, $title, array( &$this, $function == '' ? $id : $function ), $page, $context, $priority );
	}
	

	/**
	 * Add hook for shortcode tag.
	 *
	 * There can only be one hook for each shortcode. Which means that if another
	 * plugin has a similar shortcode, it will override yours or yours will override
	 * theirs depending on which order the plugins are included and/or ran.
	 *
	 * @param string $tag Shortcode tag to be searched in post content.
	 * @param callable $func Hook to run when shortcode is found.
	 */
	protected function add_shortcode( $tag, $function = null ) {
		add_shortcode( $tag, array( &$this, $function == '' ? $tag : $function ) );
	}

	
	/**
	 * Returns the filesystem path for a file/dir within this plugin.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string Filesystem path
	 * @author Simon Wheatley
	 **/
	protected function plugin_dir( $path ) {
		$path = trailingslashit( $this->plugin_folder ) . trim( $path, '/' );
		return WP_PLUGIN_DIR . "/$path";
	}

	/**
	 * Returns the URL for for a file/dir within this plugin.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string URL
	 * @author Simon Wheatley
	 **/
	protected function plugin_url( $path ) {
		$path = trailingslashit( $this->plugin_folder ) . trim( $path, '/' );
		return plugins_url( $path );
	}

} // END *_Plugin class 

?>