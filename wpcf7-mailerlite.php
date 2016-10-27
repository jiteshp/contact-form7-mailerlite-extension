<?php
/**
 * Plugin Name: Contact Form 7 MailerLite Extension
 * Plugin URI: https://www.fastbizmarketing.com/contact-form-7-mailerlite/
 * Description: Automatically add subscribers to your MailerLite account from contact form 7.
 * Version: 1.0.0
 * Author: Jitesh Patil <jitesh.patil@gmail.com>
 * Author URI: https://www.fastbizmarketing.com
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume 
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
 
/**
 *	Contact Form 7 MailerLite Extension class.
 */
class FBM_WPCF7_MailerLite {
	/**
	 *	Holds the singleton instance of this class.
	 */
	static $instance = false;
	
	/**
	 *	Holds the setting options class instance.
	 */
	private $options = null;
	
	/**
	 *	Returns the singleton instance of this class.
	 *
	 *	@return JP_Razorpay_Button
	 */
	public static function get_instance() {
		if( ! self::$instance ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	/**
	 *	Class constructor.
	 *
	 *	@return void
	 */
	private function __construct() {
		// Load theme text domain.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		// Register plugin option settings.
		add_action( 'admin_init', array( $this, 'register_option_settings' ) );

		// Load plugin option settings.
		$this->load_options();
		
		// Add plugin option settins menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Add Contact Form 7 panel for MailerLite.
		add_filter( 'wpcf7_editor_panels', array( $this, 'add_mailerlite_panel' ) );
		
		// Save the MailerLite group property to Contact Form 7.
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'save_mailerlite_group' ) );
		
		// Save the subscriber to MailerLite.
		add_action( 'wpcf7_before_send_mail', array( $this, 'save_mailerlite_subscriber' ) );
	}
	
	/**
	 *	Make the plugin ready for translation.
	 *
	 *	@return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 
			'fbm_wpcf7_mailerlite', 
			false, 
			dirname( plugin_basename( __FILE__ ) ) . '/languages' 
		);
	}
	
	/**
	 *	Register plugin option settings.
	 *
	 *	@return void
	 */
	public function register_option_settings() {
		register_setting(  
			'fbm_wpcf7_mailerlite',
			'fbm_wpcf7_mailerlite_options'
		);
	}
	
	/**
	 *	Load plugin options.
	 *
	 *	@return void
	 */
	public function load_options() {
		$this->options = get_option( 'fbm_wpcf7_mailerlite_options', array(
			'api_key' => '',
		) );
	}
	 
	/**
	 *	Add admin menu for plugin option settings menu.
	 *
	 *	@return void
	 */
	public function add_admin_menu() {
		add_submenu_page(  
			'wpcf7',												// under 'Contact' menu
			__( 'MailerLite Settings', 'fbm_wpcf7_mailerlite' ),	// page title
			__( 'MailerLite Settings', 'fbm_wpcf7_mailerlite' ),	// menu title
			'manage_options',										// capablility
			'mailerlite-settings',									// slug
			array( $this, 'add_settings_page' )						// callback funtion to build page
		);
	}
	
	/**
	 *	Add plugin options settings page.
	 *
	 *	@return void
	 */
	public function add_settings_page() {
		if( ! isset( $_REQUEST['settings-updated'] ) ) {
			$_REQUEST['settings-updated'] = false;
		}
		
		include_once( plugin_dir_path( __FILE__ ) . '/templates/options.php' );
	}
	
	/**
	 *	Add a MailerLite panel to Contact Form 7 editor.
	 *
	 *	@return void
	 */
	public function add_mailerlite_panel( $panels ) {
		$mailerlite_panel = array(
			'mailerlite-extension' => array(
				'title'		=> __( 'MailerLite', 'fbm_wpcf7_mailerlite' ),
				'callback'	=> array( $this, 'add_mailerlite_panel_content' ),
			),
		);
		
		$panels = array_merge( $panels, $mailerlite_panel );
		
		return $panels;
	}
	
	/**
	 *	Add a MailerLite panel content.
	 *
	 *	@return void
	 */
	public function add_mailerlite_panel_content( $contact_form ) {
		// Fetch the MailerLite groups.
		require_once( plugin_dir_path( __FILE__ ) . '/api/autoload.php' );
		
		$mailerlite = new \MailerLiteApi\MailerLite( $this->options['api_key'] );
		$groups_api = $mailerlite->groups();
		
		$result = $groups_api->get();
		
		include_once( plugin_dir_path( __FILE__ ) . '/templates/panel.php' );
	}
	
	/**
	 *	Save the MailerLite group property.
	 *
	 *	@param array $properties
	 *	@return void
	 */
	public function save_mailerlite_group( $properties ) {
		if( ! isset( $properties['fbm_wpcf7_mailerlite_group'] ) ) {
			$properties['fbm_wpcf7_mailerlite_group'] = '';
		}
		
		if( isset( $_POST['fbm_wpcf7_mailerlite_group'] ) ) {
			$properties['fbm_wpcf7_mailerlite_group'] = $_POST['fbm_wpcf7_mailerlite_group'];
		}
		
		return $properties;
	}
	
	/**
	 *	Save the subscriber to MailerLite.
	 *
	 *	@param WPCF7_ContactForm $contact_form
	 *	@return void
	 */
	public function save_mailerlite_subscriber( $contact_form ) {
		$mailerlite_group = $contact_form->prop( 'fbm_wpcf7_mailerlite_group' );
		$submission = WPCF7_Submission::get_instance();
		
		if( $submission && $mailerlite_group ) {
			$posted_data = $submission->get_posted_data();
			
			// Save subscriber email to MailerLite.
			if( isset( $posted_data['your-email'] ) ) {
				$subscriber = array(
					'email' => $posted_data['your-email'],
				);
				
				// Fetch the MailerLite groups.
				require_once( plugin_dir_path( __FILE__ ) . '/api/autoload.php' );
				
				$mailerlite = new \MailerLiteApi\MailerLite( $this->options['api_key'] );
				$groups_api = $mailerlite->groups();
				
				$result = $groups_api->addSubscriber( $mailerlite_group, $subscriber );
			}
		}
	}
}

/**
 *	Run the plugin.
 */
$fbm_wpcf7_mailerlite = FBM_WPCF7_MailerLite::get_instance();