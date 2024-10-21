<?php
/**
 * Email Controller class for managing and sending emails
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Core\Emails;

use EStore\Ext\Helpers;

/**
 * Special class that manages email dispatching
 */
final class EmailController {

	#region Private Fields.

	/**
	 * Singleton instance of a class.
	 * @var EmailController $instance
	 */
	private static EmailController $instance;

	#endregion.


	#region Public Properties.

	/**
	 * Initializes and returns singleton instance of the EmailController class.
	 */
	public static function get_instance(): EmailController {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new EmailController();
		}

		return self::$instance;
	}

	#endregion.


	#region Construction Methods.

	/**
	 * Construction method available only within class.
	 */
	private function __construct() { }

	#endregion


	#region Private Methods.


	#endregion


	#region Public Methods.

	/**
	 * Sends an email with account activation link.
	 *
	 * @param string $email           Email of a user with inactive account.
	 * @param string $activation_code Activation code for user's account.
	 *
	 * @return bool Whether the email was sent successfully.
	 */
	public function send_account_activation_email( string $email, string $activation_code ): bool {
		$template_args = [
			'site_name'       => get_bloginfo(),
			'email-name'      => 'activation',
			'activation-link' => get_site_url() . '/login?user=' . $email . '&activation_code=' . $activation_code,
		];

		$mail_headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		return wp_mail(
			$email,
			__( 'Активація Акаунту', 'estore-theme' ),
			Helpers::render_template_to_string( 'template-parts/emails/email', 'template', $template_args ),
			$mail_headers
		);
	}

	/**
	 * Sends an email with password recovery link.
	 *
	 * @param string $email         Email of a user who requested password recovery.
	 * @param string $recovery_code Recovery code for user's account.
	 *
	 * @return bool Whether the email was sent successfully.
	 */
	public function send_password_recovery_email( string $email, string $recovery_code ): bool {
		$template_args = [
			'site_name'     => get_bloginfo(),
			'email-name'    => 'recovery',
			'recovery-link' => get_site_url() . '/recovery?user=' . $email . '&recovery_code=' . $recovery_code,
		];

		$mail_headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		return wp_mail(
			$email,
			__( 'Відновлення Паролю', 'estore-theme' ),
			Helpers::render_template_to_string( 'template-parts/emails/email', 'template', $template_args ),
			$mail_headers
		);
	}

	public function send_email_update_notice_email( string $email ): bool {

	}

	/**
	 * Sends and email notifying user about account password update.
	 *
	 * @param string $email Email of a user whose password was changes.
	 *
	 * @return bool Whether the email was sent successfully.
	 */
	public function send_password_update_notice_email( string $email ): bool {
		$template_args = [
			'site_name'  => get_bloginfo(),
			'email-name' => 'password-reset',
		];

		$mail_headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		return wp_mail(
			$email,
			__( 'Пароль до Вашого акаунту було оновлено', 'estore-theme' ),
			Helpers::render_template_to_string( 'template-parts/emails/email', 'template', $template_args ),
			$mail_headers
		);
	}

	/**
	 * Sends an email with notices about user purchase.
	 *
	 * @param string $email     Email of a user making a purchase.
	 * @param array  $user_cart List of purchased items.
	 *
	 * @return bool Whether the email was successfully sent.
	 **/
	public function send_purchase_notice_email( string $email, array $user_cart ): bool {
		$template_args = [
			'site_name'  => get_bloginfo(),
			'user_cart'  => $user_cart,
			'email-name' => 'purchase',
		];

		$mail_headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		return wp_mail(
			$email,
			__( 'Дякуємо за покупку!', 'estore-theme' ),
			Helpers::render_template_to_string( 'template-parts/emails/email', 'template', $template_args ),
			$mail_headers
		);
	}

	#endregion
}
