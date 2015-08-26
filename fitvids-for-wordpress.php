<?php
/*
Plugin Name: FitVids for WordPress
Plugin URI: http://wordpress.org/extend/plugins/fitvids-for-wordpress/
Description: This plugin makes videos responsive using the FitVids jQuery plugin on WordPress.
Version: 2.1
Tags: videos, fitvids, responsive
Author URI: http://kevindees.cc

/--------------------------------------------------------------------\
|                                                                    |
| License: GPL                                                       |
|                                                                    |
| FitVids for WordPress - makes videos responsive.           |
| Copyright (C) 2012, Kevin Dees,                                    |
| http://kevindees.cc                                               |
| All rights reserved.                                               |
|                                                                    |
| This program is free software; you can redistribute it and/or      |
| modify it under the terms of the GNU General Public License        |
| as published by the Free Software Foundation; either version 2     |
| of the License, or (at your option) any later version.             |
|                                                                    |
| This program is distributed in the hope that it will be useful,    |
| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
| GNU General Public License for more details.                       |
|                                                                    |
| You should have received a copy of the GNU General Public License  |
| along with this program; if not, write to the                      |
| Free Software Foundation, Inc.                                     |
| 51 Franklin Street, Fifth Floor                                    |
| Boston, MA  02110-1301, USA                                        |   
|                                                                    |
\--------------------------------------------------------------------/
*/

// protect yourself
if ( !function_exists( 'add_action') ) {
	echo "Hi there! Nice try. Come again.";
	exit;
}

class fitvids_wp {
	// when object is created
	function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action('admin_menu', array($this, 'menu')); // add item to menu
		add_action('wp_enqueue_scripts', array($this, 'fitvids_scripts')); // add fit vids to site
		add_filter( 'option_page_capability_fitvids-wp', array( $this, 'save_settings_capability' ) );
	}

	/**
	 * Save Settings Capability
	 *
	 * The Fitvids settings page requires the 'switch_themes' capability.
	 * As we use the WordPress Settings API which posts data to options.php we
	 * need to let the options page allow this capability when saving the settings.
	 *
	 * @return  string  Capability.
	 */
	public function save_settings_capability() {

		return 'switch_themes';

	}

	/**
	 * Register Settings
	 */
	public function register_settings() {

		// Add Settings Section
		add_settings_section(
			'fitvids_wp_settings',
			'',
			create_function( '', 'return "";' ),
			'fitvids-wp'
		);

		// Add Selector Field
		add_settings_field(
			'fitvids_wp_selector',
			__( 'jQuery Selector', 'fitvids-for-wordpress' ),
			array( $this, 'fitvids_wp_selector_field' ),
			'fitvids-wp',
			'fitvids_wp_settings'
		);

		// Add Custom Selector Field
		add_settings_field(
			'fitvids_wp_custom_selector',
			__( 'FitVids Custom Selector', 'fitvids-for-wordpress' ),
			array( $this, 'fitvids_wp_custom_selector_field' ),
			'fitvids-wp',
			'fitvids_wp_settings'
		);

		// Add jQuery Field
		add_settings_field(
			'fitvids_wp_jq',
			__( 'jQuery', 'fitvids-for-wordpress' ),
			array( $this, 'fitvids_wp_jq_field' ),
			'fitvids-wp',
			'fitvids_wp_settings'
		);

		register_setting( 'fitvids-wp', 'fitvids_wp_selector', 'sanitize_text_field' );
		register_setting( 'fitvids-wp', 'fitvids_wp_custom_selector', 'sanitize_text_field' );
		register_setting( 'fitvids-wp', 'fitvids_wp_jq', 'sanitize_text_field' );

	}

	/**
	 * Selector Field
	 */
	public function fitvids_wp_selector_field() {

		printf( '<p>%s <a href="http://www.w3schools.com/jquery/jquery_selectors.asp" target="_blank">%s</a></p>', esc_html__( 'Add a CSS selector for FitVids to work.', 'fitvids-for-wordpress' ), esc_html__( 'Need help?', 'fitvids-for-wordpress' ) );
		printf( '<p class="code">jQuery(&quot;<input type="text" id="fitvids_wp_selector" name="fitvids_wp_selector" value="%s">&quot;).fitVids();</p>', esc_attr( get_option( 'fitvids_wp_selector' ) ) );

	}

	/**
	 * Custom Selector Field
	 */
	public function fitvids_wp_custom_selector_field() {

		printf( '<p>%s <a href="https://github.com/davatron5000/FitVids.js#add-your-own-video-vendor" target="_blank">%s</a></p>', esc_html__( 'Add a custom selector for FitVids if you are using videos that are not supported by default.', 'fitvids-for-wordpress' ), esc_html__( 'Need help?', 'fitvids-for-wordpress' ) );
		printf( '<p class="code">jQuery().fitVids({ customSelector: &quot;<input id="fitvids_wp_custom_selector" value="%s" name="fitvids_wp_custom_selector" type="text">&quot;});</p>', esc_attr( get_option('fitvids_wp_custom_selector') ) );

	}

	/**
	 * jQuery Field
	 */
	public function fitvids_wp_jq_field() {

		printf( '<p><input id="fitvids_wp_jq" value="true" name="fitvids_wp_jq" type="checkbox"%s /> %s</p>', checked( 'true', get_option( 'fitvids_wp_jq' ), false ), esc_html__( 'Add jQuery 1.7.2 from Google CDN', 'fitvids-for-wordpress' ) );
		printf( '<p class="description">%s<br />%s</p>', esc_html__( 'If you are already running jQuery 1.7+ you will not need to check the box.', 'fitvids-for-wordpress' ), esc_html__( 'Note that some plugins require different versions of jQuery and may have conflicts with FitVids.', 'fitvids-for-wordpress' ) );

	}

	// make menu
	function menu() {
		add_submenu_page('themes.php', 'FitVids for WordPress', 'FitVids', 'switch_themes', __FILE__,array($this, 'settings_page'), '', '');
	}

	// create page for output and input
	function settings_page() {
		?>
	    <div class="icon32" id="icon-themes"><br></div>
	    <div id="fitvids-wp-page" class="wrap">
	    
	    <h2>FitVids for WordPress</h2>
	    
	    <?php
	    // $_POST needs to be sanitized by version 1.0
	   	if( isset($_POST['submit']) && check_admin_referer('fitvids_action','fitvids_ref') ) {
			  $fitvids_wp_message = '';
	   		if($_POST['fitvids_wp_jq'] != '') { $fitvids_wp_message .= 'You have enabled jQuery for your theme.'; }
	   		echo '<div id="message" class="updated below-h2"><p>FitVids is updated. ', $fitvids_wp_message ,'</p></div>';
	   	}
	    ?>

		<form method="post" action="options.php">
			<?php

			settings_fields( 'fitvids-wp' );
			do_settings_sections( 'fitvids-wp' );

			?>
			<p class="submit"><input type="submit" id="fitvids_wp_submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>"></p>
		</form>

	    </div>
	    
	    <?php }
    
    // add FitVids to site
    function fitvids_scripts() {
    	if(get_option('fitvids_wp_jq') == 'true') {
    	wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', '1.0');
			wp_enqueue_script( 'jquery' );
    	}
    	
    	// add fitvids
    	wp_register_script( 'fitvids', plugins_url('/jquery.fitvids.js', __FILE__), array('jquery'), '1.0', true);    	
    	wp_enqueue_script( 'fitvids');
    	add_action('wp_print_footer_scripts', array($this, 'add_fitthem'));
    } // end fitvids_scripts
    
    // slecetor script
    function add_fitthem() { ?>
    	<script type="text/javascript">
    	jQuery(document).ready(function() {
    		jQuery('<?php echo get_option('fitvids_wp_selector'); ?>').fitVids({ customSelector: "<?php echo stripslashes(get_option('fitvids_wp_custom_selector')); ?>"});
    	});
    	</script><?php
    }    
} // end fitvids_wp obj

new fitvids_wp();