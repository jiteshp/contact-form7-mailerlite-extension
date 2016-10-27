<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	
	<p><?php printf( __( 'Set a valid API Key from your <a href="%s" target="_blank">MailerLite Account</a>. The API key is mandatory.', 'fbm_wpcf7_mailerlite' ), esc_url( 'https://app.mailerlite.com/integrations/api/' ) ); ?></p>
	
	<div id="poststuff">
		<div id="post-body">
			<div id="post-body-content">
				<form action="options.php" method="post">
					<?php settings_fields( 'fbm_wpcf7_mailerlite' ); ?>
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<?php _e( 'API Key', 'fbm_wpcf7_mailerlite' ); ?>
							</th>
							<td>
								<input type="text" 
									   class="regular-text" 
									   name="fbm_wpcf7_mailerlite_options[api_key]" 
									   id="fbm_wpcf7_mailerlite_options[api_key]" 
									   value="<?php echo $this->options['api_key']; ?>">
							</td>
						</tr>
					</table>
					
					<?php submit_button(); ?>
				</form>
			</div> <!-- #post-body-content -->
		</div> <!-- #post-body -->
	</div> <!-- #poststuff -->
</div> <!-- .wrap -->