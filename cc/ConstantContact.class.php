<?php

/**
 * ConstantContact API v2 client library
 *
 * @author Voltroid <care@voltroid.com>
 *
 */
class ConstantContact
{

    private $api_key;
    private $acc_tok;
    private $api_url = 'https://api.constantcontact.com/v2';
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
     * @param      $api_key
     * @param null $api_url
     */
    public function __construct($api_key, $acc_tok = null)
    {
        $this->api_key = $api_key;

        if (!empty($acc_tok)) {
            $this->acc_tok = $acc_tok;
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
     * get account details
     *
     * @return mixed
     */
    public function accounts()
    {
        return $this->call('account/info');
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
     * post Contact
     *
     * @return mixed
     */
    public function addContact($data)
    {
        return $this->call('contacts','POST',$data,false,true);
    }
    /**
     * post Contact
     *
     * @return mixed
     */
    public function getContact($data)
    {
        return $this->call('contacts','GET',$data,true);
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
    private function call($api_method = null, $http_method = 'GET', $params = array(), $getp=false, $act=false)
    {
        if (empty($api_method)) {
            return (object)array(
                'httpStatus' => '400',
                'code' => '1010',
                'codeDescription' => 'Error in external resources',
                'message' => 'Invalid api method'
            );
        }
        $gp = array(
            'api_key' => $this->api_key
        );
        if($getp)
           $gp['email'] = $params['email'];
        if($act)
           $gp['action_by'] = 'ACTION_BY_VISITOR';
        $gp = http_build_query($gp);
        $params = json_encode($params);
        $url = $this->api_url . '/' . $api_method .  '?' . $gp;

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => 'PHP ConstantContact client 0.0.1',
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $this->acc_tok, 'Content-Type: application/json')
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