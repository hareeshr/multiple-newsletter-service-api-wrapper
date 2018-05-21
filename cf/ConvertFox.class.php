<?php

/**
 * ConvertKit API v3 client library
 *
 * @author Voltroid <care@voltroid.com>
 *
 */
class ConvertFox
{

	private $api_key;
	private $code;
	private $api_url = 'https://api.convertfox.com';
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
	public function __construct($api_key)
	{
		$this->api_key = $api_key;
	}

	/**
	* get test
	*
	* @return mixed
	*/
	public function test()
	{
		return $this->call('users');
	}

	/**
	* get Contact Forms
	*
	* @return mixed
	*/
	public function getForms()
	{
		return $this->call('forms');
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
		return $this->call('forms/'.$list.'/subscribe','POST',$data);
	}
	/**
	* post Contact
	*
	* @return mixed
	*/
	public function getContact($email)
	{
			return $this->call('subscribers','GET',array('email_address'=>$email));
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

		// $api = http_build_query(array_merge($params,$this->api_key));
		// $params = json_encode($params);

		// $url = $this->api_url . '/' . $api_method;
		$url = $this->api_url . '/' . $api_method . "/40815191";
		// $url = $this->api_url . '/' . $api_method . "/stanleyjobs8@gmail.com";
		// $url = $this->api_url . '/project';

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_ENCODING => 'gzip,deflate',
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $this->timeout,
			CURLOPT_HEADER => false,
			CURLOPT_USERAGENT => 'PHP ConvertFox client v1',
			CURLOPT_HTTPHEADER => array(
				'Authorization:Bearer ' . $this->api_key,
				'Content-Type: application/json'
			)
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

$key = 'srAsAJOBx3VuCs98Z+xdhVvRVJmn1adGaFd2q0Vurojb606gzeWWTnFaYeJ57eaGZo8=';
$api = new ConvertFox($key);
$res = $api->test();
echo json_encode($res);
