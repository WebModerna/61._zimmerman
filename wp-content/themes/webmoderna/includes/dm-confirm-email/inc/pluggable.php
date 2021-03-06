<?php

// This is to override the pluggable functions
if( !function_exists( 'wp_new_user_notification' ) ) : 
	function wp_new_user_notification($user_id, $plaintext_pass = '')
	{
		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$message  = sprintf( __( 'Hay una registración de un usuario nuevo en tu sitio web %s:', 'emyth' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Nombre de usuario: %s', 'emyth' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'Correo Electrónico: %s', 'emyth' ), $user->user_email ) . "\r\n";

		@wp_mail( get_option( 'admin_email '), sprintf( __( '[%s] Nuevo usuario registrado', 'emyth' ), $blogname ), $message );

		if ( empty( $plaintext_pass ) )
			return;

		/**
		 * Start Custom
		 */
		// Get options
		$options = get_option( DmConfirmEmail::PLUGIN_ALIAS );

		// Email Content
		if( isset( $options['user_pass_message'] ) && !empty( $options['user_pass_message'] ) )
		{
			$message = html_entity_decode( DmConfirmEmail::parser( $options['user_pass_message'], '', $user->user_login, $plaintext_pass ) );
		}
		else
		{
			$message  = sprintf( __( 'Nombre de usuario: %s', 'emyth' ), $user->user_login ) . "\r\n";
			$message .= sprintf( __( 'Contraseña: %s', 'emyth' ), $plaintext_pass ) . "\r\n";
			$message .= wp_login_url() . "\r\n";
		}

		// Email Subject
		if( isset( $options['user_pass_subject'] ) && !empty($options['user_pass_subject'] ) )
		{
			$subject = DmConfirmEmail::parser( $options['user_pass_subject'], '', $user->user_login, $plaintext_pass );
		}
		else
		{
			$subject = '{$blogname} ' . __( 'Tu nombre de usuario y contraseña', 'emyth' );
		}

		// Add filter to make email content type html
		add_filter( 'wp_mail_content_type', 'dmSetEmailContentType' );

		wp_mail( $user->user_email, $subject, $message );

		// Remove filter
		remove_filter( 'wp_mail_content_type', 'dmSetEmailContentType' );
		/**
		 * End Custom
		 */
	}
endif;

/**
 * Set email content as html
 *
 * @return string
 */
function dmSetEmailContentType()
{
	return 'text/html';
}