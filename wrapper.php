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
			case 'be':
                require_once('be/BenchmarkEmail.php');
				$this->wrap = new benchmarkemail_api($key[0]);
				break;
			case 'cc':
                require_once('cc/ConstantContact.class.php');
				$this->wrap = new ConstantContact($key[0],$key[1]);
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
					case 'be':
						echo json_encode($this->wrap->lists());
						break;
					case 'cc':
						echo json_encode($this->wrap->accounts());
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
			case 'be':
				$t = $this->wrap->lists();
				$l = array();
				if(count($t) > 0){
					foreach ($t as $v) {
						array_push($l, array(
							'id' => $v['id'],
							'name' => $v['listname']
						));
					}
				}
				echo json_encode($l);
				break;
			case 'cc':
				$t = (array) $this->wrap->getLists()->data;
				$l = array();
				if(count($t) > 0){
					foreach ($t as $v) {
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
			case 'be':
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
						'name'=>'first name',
						'label'=>'First Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'lname',
						'name'=>'last name',
						'label'=>'Last Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'middlename',
						'name'=>'middle name',
						'label'=>'Middle Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'field1',
						'name'=>'address',
						'label'=>'Address',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field2',
						'name'=>'city',
						'label'=>'City',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field3',
						'name'=>'state',
						'label'=>'State',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field4',
						'name'=>'zip',
						'label'=>'Zip',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field5',
						'name'=>'country',
						'label'=>'Country',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field6',
						'name'=>'phone',
						'label'=>'Phone',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field7',
						'name'=>'fax',
						'label'=>'Fax',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field8',
						'name'=>'cell phone',
						'label'=>'Cell Phone',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field9',
						'name'=>'company name',
						'label'=>'Company Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field10',
						'name'=>'job title',
						'label'=>'Job Title',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field11',
						'name'=>'business phone',
						'label'=>'Business Phone',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field12',
						'name'=>'business fax',
						'label'=>'Business Fax',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field13',
						'name'=>'business address',
						'label'=>'Business Address',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field14',
						'name'=>'business city',
						'label'=>'Business City',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field15',
						'name'=>'business state',
						'label'=>'Business State',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field16',
						'name'=>'business zip',
						'label'=>'Business Zip',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field17',
						'name'=>'business country',
						'label'=>'Business Country',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field18',
						'name'=>'notes',
						'label'=>'Notes',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field19',
						'name'=>'date 1',
						'label'=>'Date 1',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field20',
						'name'=>'date 2',
						'label'=>'Date 2',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field21',
						'name'=>'extra 3',
						'label'=>'Extra 3',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field22',
						'name'=>'extra 4',
						'label'=>'Extra 4',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field23',
						'name'=>'extra 5',
						'label'=>'Extra 5',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					),
					array(
						'id'=>'field24',
						'name'=>'extra 6',
						'label'=>'Extra 6',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef',
						'typesel'=>'single',
						'nof'=>1
					)
				);
				echo json_encode($l);
				break;
			case 'cc':
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
						'id'=>'middle_name',
						'name'=>'Middle Name',
						'label'=>'Middle Name',
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
						'id'=>'prefix_name',
						'name'=>'Prefix Name',
						'label'=>'Prefix Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'job_title',
						'name'=>'Job Title',
						'label'=>'Job Title',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'company_name',
						'name'=>'Company Name',
						'label'=>'Company Name',
						'type'=>'text',
						'format'=>'text',
						'icon'=>'idef'
					),
					array(
						'id'=>'home_phone',
						'name'=>'Home Phone',
						'label'=>'Home Phone',
						'type'=>'text',
						'format'=>'number',
						'icon'=>'idef'
					),
					array(
						'id'=>'work_phone',
						'name'=>'Work Phone',
						'label'=>'Work Phone',
						'type'=>'text',
						'format'=>'number',
						'icon'=>'idef'
					),
					array(
						'id'=>'cell_phone',
						'name'=>'Cell Phone',
						'label'=>'Cell Phone',
						'type'=>'text',
						'format'=>'number',
						'icon'=>'idef'
					)
				);
				for ($i=1; $i <= 15; $i++) {
					array_push($l, array(
						'id' => 'custom_field_'.$i,
						'name' => 'custom_field_'.$i,
						'label' => 'CustomField'.$i,
						'type' => 'text',
						'typesel'=>'single',
						'format' => 'text',
						'icon'=>'idef',
						'nof'=>1
					));
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
			case 'be':
				$user = $data;
				if(isset($user['fname'])){
					$user['firstname'] = $user['fname'];
					unset($user['fname']);
				}
				if(isset($user['lname'])){
					$user['lastname'] = $user['lname'];
					unset($user['lname']);
				}
				$t = $this->wrap->find($user['email'],$form['list']['id']);
				if(!empty($t))return '2';//already
				$e = $this->wrap->addContact($user,$form['list']['id']);
				if($e == '1')return '1';//subscribed
				return '0';//error
				break;
			case 'cc':
				$user = array(
					'email_addresses'	=> array(
						array('email_address'	=> $data['email'])
						),
					'lists'	=> array(
						array('id' => $form['list']['id'])
					)
				);
				if(isset($user['fname'])){
					$user['first_name'] = $user['fname'];
					unset($user['fname']);
				}
				if(isset($user['lname'])){
					$user['last_name'] = $user['lname'];
					unset($user['lname']);
				}
				unset($data['email']);
				$custom = array();
				foreach ($data as $key => $value) {
					if(substr( $key, 0, 6 ) === "custom")
						array_push($custom, array(
							'name' => $key,
							'value' => $value
						));
					else
						$user[$key] = $value;
				}
				$user['custom_fields'] = $custom;

				$e = $this->wrap->addContact($user);
				if($e->http_status == 201)
					return '1';//subscribed
				elseif($e->http_status == 409)
					return '2';//already
				else
					return '0';//error
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
			case 'be':
				$user = $data['email'];
				$t = $this->wrap->find($user,$form['list']['id']);
				if(!empty($t))return 1;
				return 0;
				break;
			case 'cc':
				$user = array(
					'email' => $data['email']
				);
				$e = $this->wrap->getContact($user);
				if(count($e->data['results']) && $e->data['results'][0]['status'] == 'ACTIVE')
					return 1;
				return 0;
				break;
			default:
				break;
		}

	}

}



?>
