<?php

/**
 * DripEmail API v1 client library
 *
 * @author Voltroid <care@voltroid.com>
 *
 */
class DripEmail
{

	private $api_key;
	private $code;
	private $api_url = 'https://api.getdrip.com/v2';
	private $timeout = 8;
	public $http_status;

	/**
	* X-Domain header value if empty header will be not provided
	* @var string|null
	*/
	private $enterprise_domain = null;

	/**
	* X-APP-ID header value if empty header will be not provided
	* @var string|null
	*/
	private $app_id = null;

	/**
	* Set api key and optionally API endpoint
	* @param	$api_key
	* @param null $api_url
	*/
	public function __construct($acc, $api_key)
	{
		$this->key = array(
			'acc' => $acc,
			'api' => $api_key
		);
	}

	/**
	* We can modify internal settings
	* @param $key
	* @param $value
	*/
	function __set($key, $value)
	{
		$this->{$key} = $value;
	}

	/**
	* get Contact Forms
	*
	* @return mixed
	*/
	public function accounts()
	{
		return $this->call('accounts/'.$this->key['acc']);
	}
	/**
	* get Contact Forms
	*
	* @return mixed
	*/
	public function getGroups()
	{
		return $this->call('groups');
	}
	/**
	* get Custom Fields
	*
	* @return mixed
	*/
	public function getCustomFields()
	{
		return $this->call($this->key['acc'].'/custom_field_identifiers');
	}
	/**
	* post Contact
	*
	* @return mixed
	*/
	public function addContact($data,$list)
	{
		return $this->call($this->key['acc'].'/subscribers','POST',$data);
	}
	/**
	* post Contact
	*
	* @return mixed
	*/
	public function getContact($email)
	{
			return $this->call($this->key['acc'].'/subscribers/'.$email);
	}
	/**
	* Curl run request
	*
	* @param null $api_method
	* @param string $http_method
	* @param array $params
	* @return mixed
	* @throws Exception
	*/
	private function call($api_method = null, $http_method = 'GET', $params = array())
	{
		$params = json_encode($params);

		$url = $this->api_url . '/' . $api_method;
		
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_ENCODING => 'gzip,deflate',
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $this->timeout,
			CURLOPT_HEADER => false,
			CURLOPT_USERAGENT => 'PHP DripEmail client v1',
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $this->key['api'].':',
            CURLOPT_HTTPHEADER => array('Content-Type: application/vnd.api+json')
		);


		if(strlen(ini_get('curl.cainfo')) === 0) {
			$options[CURLOPT_CAINFO] = dirname(__FILE__).'/cacert.pem';
		}

		if ($http_method == 'POST') {
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = $params;
		} else if ($http_method == 'DELETE') {
			$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
		}

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response=array();
		$response['data'] = json_decode(curl_exec($curl),true);
		$response['http_status'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);
		return $response;
	}

	/**
	* @param array $params
	*
	* @return string
	*/
	private function setParams($params = array())
	{
		$result = array();
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$result[$key] = $value;
			}
		}
		return http_build_query($result);
	}

}