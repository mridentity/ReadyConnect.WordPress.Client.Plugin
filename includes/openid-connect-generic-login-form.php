<?php

class OpenID_Connect_Generic_Login_Form {

	private $settings;
	private $client_wrapper;

	/**
	 * @param $settings
	 * @param $client_wrapper
	 */
	function __construct( $settings, $client_wrapper ){
		$this->settings = $settings;
		$this->client_wrapper = $client_wrapper;
	}

	/**
	 * @param $settings
	 * @param $client_wrapper
	 *
	 * @return \OpenID_Connect_Generic_Login_Form
	 */
	static public function register( $settings, $client_wrapper ){
		$login_form = new self( $settings, $client_wrapper );
		
		// alter the login form as dictated by settings
		add_filter( 'login_message', array( $login_form, 'handle_login_page' ), 99 );
		
		return $login_form;
	}
	
	/**
	 * Implements filter login_message
	 *
	 * @param $message
	 * @return string
	 */
	function handle_login_page( $message ) {
		$settings = $this->settings;

		// errors and auto login can't happen at the same time
		if ( isset( $_GET['login-error'] ) ) {
			$message = $this->make_error_output( $_GET['login-error'], $_GET['message'] );
		}
		else if ( $settings->login_type == 'auto' ) {
			wp_redirect( $this->client_wrapper->get_authentication_url() );
			exit;
		}
		
		// login button is appended to existing messages in case of error
		if ( $settings->login_type == 'button' ) {
			$message .= $this->make_login_button();
		}

		return $message;
	}
	
	/**
	 * Display an error message to the user
	 *
	 * @param $error_code
	 *
	 * @return string
	 */
	function make_error_output( $error_code, $error_message ) {

		ob_start();
		?>
		<div id="login_error">
			<strong><?php _e( 'ERROR'); ?>: </strong>
			<?php print $error_message; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Create a login button (link)
	 *
	 * @return string
	 */
	function make_login_button() {
		$text = apply_filters( 'openid-connect-generic-login-button-text', __( 'Login with ReadyConnect' ) );
		$href = $this->client_wrapper->get_authentication_url();

		ob_start();
		?>
		<div class="openid-connect-login-button" style="margin: 1em 0; text-align: center;">
			<a class="button button-large" href="<?php print esc_url( $href ); ?>"><?php print $text; ?></a>
		</div>
		<?php
		return ob_get_clean();
	}
}
