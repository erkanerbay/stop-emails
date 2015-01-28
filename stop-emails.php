<?php
/*
 * Plugin Name: Stop Emails
 * Plugin URI: http://salferrarello.com/stop-emails-wordpress-plugin/
 * Description: Stops outgoing emails sent using wp_mail() function
 * Any calls to wp_mail() will fail silently (i.e. WordPress
 * will operate as if the email were sent successfully
 * but no email will actually be sent).
 * NOTE: If using the PHP mail() function directly, this
 * plugin will NOT stop the emails.
 * Version: 0.8.0
 * Author: Sal Ferrarello
 * Author URI: http://salferrarello.com/
 * Text Domain: stop-emails
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// load PHPMailer class, so we can sub-class it
require_once ABSPATH . WPINC . '/class-phpmailer.php';

/**
 * Subclass of PHPMailer to prevent Sending.
 *
 * This subclass of PHPMailer replaces the send() method
 * with a method that does not send.
 * This subclass is based on the WP Core MockPHPMailer
 * subclass found in phpunit/includes/mock-mailer.php
 *
 * @since 0.8.0
 * @see PHPMailer
 */
class Fe_Stop_Emails_Fake_PHPMailer extends PHPMailer {
	var $mock_sent = array();

	/**
	 * Replacement send() method that does not send.
	 *
	 * Unlike the PHPMailer send method,
	 * this method never class the method postSend(),
	 * which is where the email is actually sent
	 *
	 * @since 0.8.0
	 * @return bool
	 */
	function send() {
		try {
			if ( ! $this->preSend() )
				return false;

			$this->mock_sent[] = array(
				'to'     => $this->to,
				'cc'     => $this->cc,
				'bcc'    => $this->bcc,
				'header' => $this->MIMEHeader,
				'body'   => $this->MIMEBody,
			);

			return true;
		} catch ( phpmailerException $e ) {
			return false;
		}
	}
}

/**
 * Stop Emails Plugin
 *
 * Prevents emails from being sent and provides basic logging.
 * Replaces PHPMailer global instance $phpmailer with an instance
 * of the sub-class Fe_Stop_Emails_Fake_PHPMailer
 *
 * @since 0.8.0
 */
class Fe_Stop_Emails {
	public function __construct() {
		$this->add_hooks();
	}

	public function add_hooks() {
		add_action( 'plugins_loaded', array( $this, 'replace_phpmailer' ) );
	}

	public function replace_phpmailer( $phpmailer ) {
		global $phpmailer;
		return $this->replace_w_fake_phpmailer( $phpmailer );
	}

	public function replace_w_fake_phpmailer( &$obj = null ) {
		$obj = new Fe_Stop_Emails_Fake_PHPMailer;

		return $obj;
	}
}

new Fe_Stop_Emails;
