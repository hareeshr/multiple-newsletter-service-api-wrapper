<?php

/**
 * VerticalResponse API v1 client library
 *
 * @author Voltroid <care@voltroid.com>
 *
 */
class VerticalResponse
{

	private $api_key;
	private $code;
	private $api_url = 'https://vrapi.verticalresponse.com/api/v1';
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
	public function __construct($api_key, $sec, $code = null)
	{
		$this->api_key = $api_key;
		$this->sec = $sec;

		if (!empty($code)) {
			$this->code = $code;
		}
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
	* Get Access Token Validity
	*
	* @return mixed
	*/
	public function getAccessToken()
	{
		$resp = wp_remote_get("http".(is_ssl()?'s':'')."://vrapi.verticalresponse.com/api/v1/oauth/access_token?client_id=".$this->api_key."&client_secret=".$this->sec."&redirect_uri=".OPPOINT_PLUGIN_URL."service/vr/redirect.php&code=".$this->code);
		$body = json_decode(strstr($resp['body'],'{"'),true);
		if(isset($body["error"]))
			return array('status'=>0);
		else
			return array('status'=>1,'code'=>$body);
	}

	/**
	* get Contact Lists
	*
	* @return mixed
	*/
	public function getLists()
	{
		return $this->call('lists');
	}
	/**
	* get Custom Fields
	*
	* @return mixed
	*/
	public function getCustomFields()
	{
		return $this->call('custom_fields');
	}
	/**
	* post Contact
	*
	* @return mixed
	*/
	public function addContact($data,$list)
	{
		return $this->call('lists/'.$list.'/contacts','POST',$data);
	}
	/**
	* post Contact
	*
	* @return mixed
	*/
	public function getContact($data)
	{
		return $this->call('contacts','GET',$data);
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
		if (empty($api_method)) {
			return (object)array(
				'httpStatus' => '400',
				'code' => '1010',
				'codeDescription' => 'Error in external resources',
				'message' => 'Invalid api method'
			);
		}
		if($params && $http_method == 'GET')
			$gp = http_build_query($params);
		$params = json_encode($params);

		$url = $this->api_url . '/' . $api_method.(isset($gp)? '?' . $gp : '');

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_ENCODING => 'gzip,deflate',
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $this->timeout,
			CURLOPT_HEADER => false,
			CURLOPT_USERAGENT => 'PHP VerticalResponse client 0.0.1',
			CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $this->code, 'Content-Type: application/json')
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
		return (object)$response;
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