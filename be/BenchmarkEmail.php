<?php

class benchmarkemail_api {
	private $token;
	private $apiurl = 'https://api.benchmarkemail.com/1.3/';
	/**
	* Set api key and optionally API endpoint
	* @param	$api_key
	* @param null $api_url
	*/
	public function __construct($tok)
	{
	$this->token = $tok;
	}
	// Executes Query with Time Tracking
	private function query() {
		$timeout = 20;
		ini_set( 'default_socket_timeout', $timeout );
		require_once( ABSPATH . WPINC . '/class-IXR.php' );

		// Connect and Communicate
		$client = new IXR_Client( $this->apiurl, false, 443, $timeout );
		$args = func_get_args();
		call_user_func_array( array( $client, 'query' ), $args );
		$response = $client->getResponse();

		// Otherwise Respond
		return $response;
	}

	// Lookup Lists For Account
	public function lists() {
		$response = $this->query( 'listGet', $this->token, '', 1, 100, 'name', 'asc' );
		return $response;
	}

	// Get Existing Subscriber Data
	public function find( $email, $listID ) {
		$response = $this->query( 'listGetContacts', $this->token, $listID, $email, 1, 100, 'name', 'asc' );
		return $response;
	}

	// Add or Update Subscriber
	public function addContact( $data, $listID ) {
		$response = $this->query( 'listAddContactsOptin', $this->token, $listID, array( $data ), '1' );
		return $response;
	}
}

?>