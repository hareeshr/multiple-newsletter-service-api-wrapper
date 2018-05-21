<?php
/*
*
* Multi Newsletter Service API Wrapper
* PHP Class
*
*/

class Newsletter_Wrapper {

	function __construct($config){
		$this->config = $config;
		$this->configure();
	}
    function configure(){
        $this->args = array();
        if(isset($this->config['key']))
            $key = $this->config['key'];
        switch ($this->config['id']) {
			case 'aw':
                require_once('aw/aweber_api.php');
				if(!isset($this->config['key'][51])){
					$aweber = new AWeberAPI($this->config['key'][0], $this->config['key'][1]);
					$this->wrap = $aweber->getAccount($this->config['key'][2], $this->config['key'][3]);
				}
				break;

            default:
            # code...
            break;
        }
    }
	function connect($step){
        switch ($step) {
			case 0:
				switch ($this->config['id']) {
					case 'aw':
						$credentials = AWeberAPI::getDataFromAweberID($this->config['key'][51]);
						list($consumerKey, $consumerSecret, $accessKey, $accessSecret) = $credentials;
						echo json_encode($credentials);
						break;
					default:
						break;
				}
				break;
			case 1:
				switch ($this->config['id']) {
					default:
						break;
				}
				break;
			default:
				# code...
				break;
		}
	}
	function getlists(){
		switch ($this->config['id']) {
			case 'aw':
				$t = $this->wrap->lists;
				$l = array();
				if($t->data['total_size'] > 0){
					foreach ($t->data['entries'] as $v) {
						array_push($l, array(
							'id' => $v['id'],
							'name' => $v['name']
						));
					}
				}
				echo json_encode($l);
				break;
			default:
				break;
		}

	}
	function getfields($l){
		switch ($this->config['id']) {
			case 'aw':
				$t = $this->wrap->loadFromUrl('/accounts/'.$this->wrap->id.'/lists/'.$l.'/custom_fields');
				$l = array(
					array(
						'id'=>'email',
						'name'=>'email',
						'label'=>'Email Address',
						'type'=>'text',
						'format'=>'email',
						'req'=>1,
						'icon'=>'idef'
					),
					array(
						'id'=>'name',
						'name'=>'name',
						'label'=>'Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					)
				);
				if($t->data['total_size'] > 0){
					foreach ($t->data['entries'] as $v) {
						array_push($l, array(
							'id' => $v['id'],
							'name' => $v['name'],
							'label' => $v['name'],
							'type'=>'text',
							'format'=>'text',
							'icon'=>'idef',
							'not'=>1,
							'nof'=>1
						));
					}
				}
				echo json_encode($l);
				break;
			default:
				break;
		}

	}
	function typesel($s,$t){
		switch ($s) {
            default:
                break;
        }
    }
	function formatsel($s,$t){
		switch ($s) {
            default:
                break;
        }
    }
	function extsel($s,$t,$type=null){
		$a = array();
		switch ($s) {
            default:
                break;
        }
        return $a;
    }
	function subscribe($form,$data){
		foreach ($form['fields'] as $v) {
			if(isset($v['hidden']) && $v['hidden']){
				switch ($v['type']) {
					case 'radio':
					case 'checkbox':
					case 'select':
					case 'multiselect':
						$a=array();
						foreach ($v['extras'] as $g) {
							if(isset($g['hid']) && $g['hid'])
								array_push($a, $g['name']);
						}
						break;
					case 'text':
					case 'textarea':
						$a=(isset($v['value'])? $v['value']: '');
						break;
					default:
						# code...
						break;
				}
				$data[$v['id']] = $a;
			}
		}
		switch ($this->config['id']) {
			case 'aw':
				try{
					$list = $this->wrap->loadFromUrl('/accounts/'.$this->wrap->id.'/lists/'.$form['list']['id']);
					$user = array(
						'email' => $data['email'],
						'custom_fields' => array()
					);
					unset($data['email']);
					if(isset($data['name'])){
						$user['name'] = $data['name'];
						unset($data['name']);
					}
					$user['custom_fields'] = $data;
					$sub = $list->subscribers;
					$e = $sub->create($user);
					return '1';//subscribed
				} catch(AWeberAPIException $exc) {
					if($exc->message == "email: Subscriber already subscribed.")
						return '2';//already
					else
						return '0';//error
				}
				break;
			default:
				break;
		}

	}
	function verify($form,$data){
		switch ($this->config['id']) {
			case 'aw':
				try{
					$sub = $this->wrap->loadFromUrl('/accounts/'.$this->wrap->id.'/lists/'.$form['list']['id'].'/subscribers');
					$user = array('email' => $data['email']);
					$e = $sub->find($user);
					if($e->data['total_size'] < 1)
						return 0;
					else{
						if($e->data['entries'][0]['status'] == 'subscribed')
							return 1;
						else
							return 0;
					}
				} catch(AWeberAPIException $exc) {
					return 0;
				}
				break;
			default:
				break;
		}

	}

}



?>
