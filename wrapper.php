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
			case 'ac':
                require_once('ac/ActiveCampaign.class.php');
				$this->wrap = new ActiveCampaign($key[0],$key[1]);
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
					case 'ac':
						if(!(int)$this->wrap->credentials_test())
							$resp=array('status' => 0);
						else{
							$resp=array('status' => 1,'data' => $this->wrap->api("account/view"));
						}
						echo json_encode($resp);
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
			case 'ac':
				$t = $this->wrap->api("list/list?ids=all");
				$l = array();
				if($t->result_code){
					for ($i=0; isset($t->$i) ; $i++) {
						array_push($l, array(
							'id' => $t->$i->id,
							'name' => $t->$i->name
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
			case 'ac':
				$t = $this->wrap->api("list/field/view?ids=all");
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
						'id'=>'fname',
						'name'=>'First Name',
						'label'=>'First Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'lname',
						'name'=>'Last Name',
						'label'=>'Last Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'phone',
						'name'=>'Phone',
						'label'=>'Phone',
						'type'=>'text',
						'format'=>'number',
						'icon'=>'idef'
					)
				);
				if($t->result_code){
					for ($i=0; isset($t->$i) ; $i++) {
						array_push($l, array(
							'id' => $t->$i->id,
							'name' => $t->$i->title,
							'label' => $t->$i->title,
							'type' => $this->typesel('ac',$t->$i->element),
							'format' => $this->formatsel('ac',$t->$i->element),
							'extras' => $this->extsel('ac',$t->$i->options),
							'icon'=>'idef',
							'nof' => ($t->$i->element == 'date' ? 0 : 1)
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
            case 'ac':
                switch ($t) {
                    case 'date':
                    case 'hidden':return 'text';
                        break;
                    default:return $t;
                        break;
                }
                break;
            default:
                break;
        }
    }
	function formatsel($s,$t){
		switch ($s) {
            case 'ac':
                switch ($t) {
                    case 'date':return 'date';
                        break;
                    default:return 'text';
                        break;
                }
                break;
            default:
                break;
        }
    }
	function extsel($s,$t,$type=null){
		$a = array();
		switch ($s) {
            case 'ac':
                    foreach ($t as $k => $v) {
                        array_push($a, array('name' => $v->name));
                    }
                break;
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
			case 'ac':
				$user = $data;
				if(isset($user['fname'])){
					$user['first_name'] = $user['fname'];
					unset($user['fname']);
				}
				if(isset($user['lname'])){
					$user['last_name'] = $user['lname'];
					unset($user['lname']);
				}
				$user['p['.$form['list']['id'].']'] = $form['list']['id'];
				$user['status['.$form['list']['id'].']'] = 1;
				$e = $this->wrap->api("contact/add", $user);
				if($e->result_code)
					return '1';//subscribed
				else{
					$i=0;
					if($e->$i)
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
			case 'ac':
				$e = $this->wrap->api("contact/view?email=".$data['email']);
				if($e->result_code)
					return 1;
				return 0;
				break;
			default:
				break;
		}

	}

}



?>
