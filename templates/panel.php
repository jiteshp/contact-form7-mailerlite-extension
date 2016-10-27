<h2><?php _e( 'MailerLite', 'fbm_wpcf7_mailerlite' ); ?></h2>

<?php if( isset( $result[0]->error ) && ( 1 == $result[0]->error->code || 302 == $result[0]->error->code ) ): ?>

	<p><?php printf( __( 'Please <a href="%s">set a valid API Key</a> in the settings.', 'fbm_wpcf7_mailerlite' ), esc_url( admin_url( 'admin.php?page=mailerlite-settings' ) ) ); ?></p>

<?php else: ?>

	<p>
		<label for="fbm_wpcf7_mailerlite_group"><?php _e( 'Select a group to which you want to add the subscribers. Leave unselected if you <em>do not</em> want to add subscribers.', 'fbm_wpcf7_mailerlite' ); ?></label>
	</p>

	<select name="fbm_wpcf7_mailerlite_group" id="fbm_wpcf7_mailerlite_group">
		<option value=""><?php _e( '&mdash;', 'fbm_wpcf7_mailerlite' ); ?></option>
		
		<?php
			$properties = $contact_form->get_properties();
			
			foreach( $result as $group ) {
				$selected = selected( $properties['fbm_wpcf7_mailerlite_group'], $group->id, false );
				
				echo '<option value="' . esc_attr( $group->id ) . '"' . $selected . '>' . esc_html( $group->name ) . '</option>';
			}
		?>
	</select>
	
	<br><br><hr>

	<p>
		<?php _e( '<strong>Important</strong>: The subscriber email address is picked up from the <strong><em>your-email</em></strong> tag. Please make sure you have it in your form.', 'fbm_wpcf7_mailerlite' ); ?>
	</p>
<?php endif; ?>