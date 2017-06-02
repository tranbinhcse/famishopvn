<?php
class ControllerExtensionModuleSoSociallogin extends Controller {
	public function index() {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('so_sociallogin');

		$this->load->language('extension/module/so_sociallogin');
		$this->load->model('account/customer');
		
		$data['heading_title'] = $this->language->get('heading_title');
		if(isset($this->request->get['route']))
		{
			$this->session->data['route']=$this->request->get['route'];
		}
		$data['text_tax'] = $this->language->get('text_tax');

		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
		$data['warning']='';
		if(isset($this->session->data['warning']))
		{
			$data['warning']=$this->session->data['warning'];
			unset($this->session->data['warning']);
		}
			
		if ($setting['so_sociallogin_fbimage']) {
			$fbicon = $this->model_tool_image->resize($setting['so_sociallogin_fbimage'], $setting['so_sociallogin_width'],$setting['so_sociallogin_height']);
		} else {
			$fbicon = $this->model_tool_image->resize('placeholder.png', $setting['so_sociallogin_width'],$setting['so_sociallogin_height']);
		}
			
		if ($setting['so_sociallogin_twitimage']) {
			$twiticon = $this->model_tool_image->resize($setting['so_sociallogin_twitimage'], $setting['so_sociallogin_width'],$setting['so_sociallogin_height']);
		} else {
			$twiticon = $this->model_tool_image->resize('placeholder.png', $setting['so_sociallogin_width'],$setting['so_sociallogin_height']);
		}
			
		if ($setting['so_sociallogin_googleimage']) {
			$googleicon = $this->model_tool_image->resize($setting['googleimage'], $setting['width'],$setting['height']);
		} else {
			$googleicon = $this->model_tool_image->resize('placeholder.png', $setting['width'],$setting['height']);
		}
		
		if ($setting['so_sociallogin_linkdinimage']) {
			$linkdinicon = $this->model_tool_image->resize($setting['so_sociallogin_linkdinimage'], $setting['so_sociallogin_width'],$setting['so_sociallogin_height']);
		} else {
			$linkdinicon = $this->model_tool_image->resize('placeholder.png', $setting['so_sociallogin_width'],$setting['so_sociallogin_height']);
		}
							
		$data['iconwidth'] 		= $setting['so_sociallogin_width'];
		$data['iconheight'] 	= $setting['so_sociallogin_height'];
		$data['status']  		= $setting['so_sociallogin_status'];
		$data['fbimage']   		= $fbicon;
		$data['twitimage']  	= $twiticon;
		$data['googleimage'] 	= $googleicon;
		$data['linkdinimage'] 	= $linkdinicon;
		$data['fbstatus'] 	  	= $setting['so_sociallogin_fbstatus'];
		$data['twittertitle'] 	= $setting['so_sociallogin_twittertitle'];
		$data['googletitle']  	= $setting['so_sociallogin_googletitle'];
		$data['linkedintitle'] 	= $setting['so_sociallogin_linkedintitle'];
		$data['fbtitle']      	= $setting['so_sociallogin_fbtitle'];
		$data['twitstatus']    	= $setting['so_sociallogin_twitstatus'];
		$data['googlestatus']   = $setting['so_sociallogin_googlestatus'];
		$data['linkstatus']    	= $setting['linkstatus'];
				
		//Facebook Libery file inculde	
		require_once(DIR_SYSTEM . 'library/so_social/fb/facebook.php');
		
		// Google Libery file inculde
		require_once DIR_SYSTEM.'library/so_social/src/Google_Client.php';
		require_once DIR_SYSTEM.'library/so_social/src/contrib/Google_Oauth2Service.php';
		
		//Facebook  Login link code
		$fbconnect = new Facebook(array(
				'appId'  => $setting['fbapikey'],
				'secret' => $setting['fbsecretapi'],
		));
		
		$data['fblink'] = $fbconnect->getLoginUrl(
				array(
					'scope' 		=> 'email,user_birthday,user_location,user_hometown',
					'redirect_uri'  => $this->url->link('extension/module/so_sociallogin/FacebookLogin', '', 'SSL')
				)
		);
		//Facebook  Login link code
		
		
		/* Twitter Login */						
		$data['twitlink'] =  $this->url->link('extension/module/so_sociallogin/TwitterLogin', '', 'SSL');
			
		/* Linkedin Login */
		$data['linkdinlink'] = $this->url->link('extension/module/so_sociallogin/LinkedinLogin', '', 'SSL');
		 /* Linkedin Login */
		
		/* Google Login link code */
		$gClient = new Google_Client();
		$gClient->setApplicationName($data['googletitle']);
		$gClient->setClientId($setting['googleapikey']);
		$gClient->setClientSecret($setting['googlesecretapi']);
		$gClient->setRedirectUri($this->url->link('extension/module/so_sociallogin/GoogleLogin', '', 'SSL'));
		$google_oauthV2= new Google_Oauth2Service($gClient);
		$data['googlelink']  = $gClient->createAuthUrl();
		/* Google Login link code */
		
		if(!$this->customer->isLogged())
		{
			if(VERSION >= '2.2.0.0'){
            	return $this->load->view('extension/module/so_sociallogin', $data);
	        }elseif (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/so_sociallogin.tpl')) {
	            return $this->load->view($this->config->get('config_template') . '/template/module/so_sociallogin.tpl', $data);
	        } else {
	            return $this->load->view('default/template/module/so_sociallogin.tpl', $data);
	        }
		}
	}
	
	//facebook
	public function FacebookLogin() {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('so_sociallogin');
		
		if(isset($this->session->data['route']))
		{
			$location = $this->url->link($this->session->data['route'], "", 'SSL');
		}
		else
		{
			$location = $this->url->link("account/account", "", 'SSL');
		}
		
		if ($this->customer->isLogged())	
			$this->response->redirect($location);
		 
		if(!isset($this->fbconnect)){
			require_once(DIR_SYSTEM . 'library/so_social/fb/facebook.php');
	
			$this->fbconnect = new Facebook(array(
					'appId'  => $setting['so_sociallogin_fbapikey'],
					'secret' => $setting['so_sociallogin_fbsecretapi'],
			));
		}
	
		$_SERVER_CLEANED = $_SERVER;
		$_SERVER = $this->clean_decode($_SERVER);
	
		$fbuser = $this->fbconnect->getUser();
		
		$fbuser_profile = null;
		if ($fbuser){
			try {
				$fbuser_profile = $this->fbconnect->api("/$fbuser?fields=email,first_name,last_name");
			} catch (FacebookApiException $e) {
				error_log($e);
				$fbuser = null;
			}
		}		
	
		$_SERVER = $_SERVER_CLEANED;
	
		if($fbuser_profile['id'] && $fbuser_profile['email']){
			$this->load->model('account/customer');
	
			$email = $fbuser_profile['email'];
			
			$customer_info = $this->model_account_customer->getCustomerByEmail($email);
			
			if(!empty($customer_info)){
				if ($customer_info && !$customer_info['approved']) {
					$this->session->data['warning'] = 'Customer not Approved';
				}
				else
				{
					if($this->customer->login($email, '', true)){
						$this->response->redirect($location);
					}
				}				
			}			
	 		else{
				$password 					= rand();	
				$customerdata				= array();
				$customerdata['email'] 		= $fbuser_profile['email'];
				$customerdata['password'] 	= $password;
				$customerdata['firstname'] 	= isset($fbuser_profile['first_name']) ? $fbuser_profile['first_name'] : '';
				$customerdata['lastname'] 	= isset($fbuser_profile['last_name']) ? $fbuser_profile['last_name'] : '';
				$customerdata['fax'] 		= '';
				$customerdata['telephone'] 	= '';
				$customerdata['company'] 	= '';
				$customerdata['company_id'] = '';
				$customerdata['tax_id'] 	= '';
				$customerdata['address_1'] 	= '';
				$customerdata['address_2'] 	= '';
				$customerdata['city'] 		= '';
				$customerdata['city_id'] 	= '';
				$customerdata['postcode'] 	= '';
				$customerdata['country_id'] = 0;
				$customerdata['zone_id'] 	= 0;
				$this->model_account_customer->addCustomer($customerdata);
				if($this->customer->login($email, $password, true)){
					$this->response->redirect($location);
				}
			}
		}else{			
			$this->session->data['warning'] = 'Please Varify facebook App';
		}
		$location=	$this->url->link("account/login", "", 'SSL');
		
		$this->response->redirect($location);
		
	}
	
	// google
	public function GoogleLogin() {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('so_sociallogin');
		
		if(isset($this->session->data['route']))
		{
			$location = $this->url->link($this->session->data['route'], "", 'SSL');
		}
		else
		{
			$location = $this->url->link("account/account", "", 'SSL');
		}

		// Google Libery file inculde
		require_once DIR_SYSTEM.'library/so_social/src/Google_Client.php';
		require_once DIR_SYSTEM.'library/so_social/src/contrib/Google_Oauth2Service.php';
		
		/* Google Login link code */
		$gClient = new Google_Client();
		$gClient->setApplicationName($setting['so_sociallogin_googletitle']);
		$gClient->setClientId($setting['so_sociallogin_googleapikey']);
		$gClient->setClientSecret($setting['so_sociallogin_googlesecretapi']);
		$gClient->setRedirectUri($this->url->link('extension/module/so_sociallogin/GoogleLogin', '', 'SSL'));
		$google_oauthV2 = new Google_Oauth2Service($gClient);
		/* Google Login link code */
		
		if(isset($this->request->get['code'])){
			$gClient->authenticate();
			$this->session->data['googletoken'] = $gClient->getAccessToken();
		}
			
		if (isset($this->session->data['googletoken'])) {
			$gClient->setAccessToken($this->session->data['googletoken']);
		}
		
		if ($gClient->getAccessToken()) {
			$userProfile = $google_oauthV2->userinfo->get();
			$this->session->data['googletoken'] = $gClient->getAccessToken();
						
			$this->load->model('account/customer');
	
			$email = $userProfile['email'];
			
			$customer_info = $this->model_account_customer->getCustomerByEmail($email);
			
			if(!empty($customer_info)){				
				if ($customer_info && !$customer_info['approved']) {
					$this->session->data['warning'] = 'Customer not Approved';
				}
				else
				{
					if($this->customer->login($email, '', true)){
						$this->response->redirect($location);
					}
				}				
			}else{	
				$names						= explode(' ',$userProfile['name']);				
				$password 					= rand();	
				$customerdata				= array();
				$customerdata['email'] 		= $userProfile['email'];
				$customerdata['password'] 	= $password;
				$customerdata['firstname'] 	= isset($names[0]) ? $names[0] : '';
				$customerdata['lastname'] 	= isset($names[1]) ? $names[1] : '';
				$customerdata['fax'] 		= '';
				$customerdata['telephone'] 	= '';
				$customerdata['company'] 	= '';
				$customerdata['company_id'] = '';
				$customerdata['tax_id'] 	= '';
				$customerdata['address_1'] 	= '';
				$customerdata['address_2'] 	= '';
				$customerdata['city'] 		= '';
				$customerdata['city_id'] 	= '';
				$customerdata['postcode'] 	= '';
				$customerdata['country_id'] = 0;
				$customerdata['zone_id'] 	= 0;

				$this->model_account_customer->addCustomer($customerdata);
				if($this->customer->login($email, $password, true)){
					$this->response->redirect($location);
				}
			}
		}
		else {
			die("error access token !");
		}
	}
	
	public function TwitterLogin() {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('so_sociallogin');		
		
		$twitapikey = $setting['so_sociallogin_twitapikey'];
		$twitsecretapi = $setting['so_sociallogin_twitsecretapi'];
		require_once DIR_SYSTEM.'library/so_social/twitter/twitteroauth.php';
		
		//Fresh authentication
		$connection = new TwitterOAuth($twitapikey, $twitsecretapi);
		$request_token = $connection->getRequestToken($this->url->link('extension/module/so_sociallogin/TwitterToken', '', 'SSL'));		

		//Received token info from twitter
		$this->session->data['oauth_token'] 		= $request_token['oauth_token'];
		$this->session->data['oauth_token_secret'] 	= $request_token['oauth_token_secret'];

		//Any value other than 200 is failure, so continue only if http code is 200
		if($connection->http_code == '200')
		{
			//redirect user to twitter
			$twitter_url = $connection->getAuthorizeURL($request_token['oauth_token']);
			header('Location: ' . $twitter_url); 
		}else{
			die("error connecting to twitter! try again later!");
		}
	}
	
	public function TwitterToken() {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('so_sociallogin');

		if(isset($this->session->data['route']))
		{
			$location = $this->url->link($this->session->data['route'], "", 'SSL');
		}
		else
		{
			$location = $this->url->link("account/account", "", 'SSL');
		}
		
		require_once DIR_SYSTEM.'library/so_social/twitter/twitteroauth.php';		
		
		if (!empty($this->request->get['oauth_verifier']) && !empty($this->session->data['oauth_token']) && !empty($this->session->data['oauth_token_secret'])) {
			$twitteroauth = new TwitterOAuth($setting['so_sociallogin_twitapikey'], $setting['so_sociallogin_twitsecretapi'], $this->session->data['oauth_token'], $this->session->data['oauth_token_secret']);
			$access_token = $twitteroauth->getAccessToken($this->request->get['oauth_verifier']);
			$this->session->data['access_token'] = $access_token;
			$user_info = $twitteroauth->get('account/verify_credentials');			
			
			if (isset($user_info->error)) {
			
			} else {
				$twiter_id = $user_info->id;
				$name = $user_info->name;
				
				$name_arr 	= explode(" ", $name);
				$f_name 	= array_shift($name_arr);
				$l_name 	= implode(" ", $name_arr);
				
				if(isset($user_info->email))
				{
					$email = $user_info->email;
					$this->response->redirect($this->url->link("account/login", "", 'SSL'));
				}
				else
				{
					$this->session->data['warning'] = 'Need Special varification for twitter';
					$this->response->redirect($this->url->link("account/login", "", 'SSL'));
				}
				
				$redirect = $this->url->link("account/account", "", 'SSL');
				
				$this->load->model('account/customer');
				if($this->customer->login($email, '', true)){
					$this->response->redirect($location);
				}
				
				$customer_info = $this->model_account_customer->getCustomerByEmail($email);
				if(isset($customer_info)){				
					if ($customer_info && !$customer_info['approved']) {
						$this->session->data['warning'] = 'Customer not Approved';
					}
				} else{	
					$this->request->post['email'] 	= $email;
					$password 						= $this->get_password($user_info->id);	
					$insertentry					= array();
					$insertentry['email'] 			= $user_info->email;
					$insertentry['password'] 		= $password;
					$insertentry['firstname'] 		= isset($user_info->first_name) ? $user_info->first_name : $user_info->name;
					$insertentry['lastname'] 		= isset($user_info->last_name) ? $user_info->last_name : '';
					$insertentry['fax'] 			= '';
					$insertentry['telephone'] 		= '';
					$insertentry['company'] 		= '';
					$insertentry['company_id'] 		= '';
					$insertentry['tax_id'] 			= '';
					$insertentry['address_1'] 		= '';
					$insertentry['address_2'] 		= '';
					$insertentry['city'] 			= '';
					$insertentry['city_id'] 		= '';
					$insertentry['postcode'] 		= '';
					$insertentry['country_id'] 		= 0;
					$insertentry['zone_id'] 		= 0;
	
					$this->model_account_customer->addCustomer($insertentry);
					$this->config->set('config_customer_approval',$config_customer_approval);
	
					if($this->customer->login($email, $password, true)){
						$this->response->redirect($location);
					}
				}
			}
		} else {			
			$this->response->redirect($this->url->link('common/home', '', 'SSL'));
		}
	}
	 
	
	/* LinkedIn */
	public function LinkedinLogin() {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('so_sociallogin');

		if(isset($this->session->data['route']))
		{
			$location = $this->url->link($this->session->data['route'], "", 'SSL');
		}
		else
		{
			$location = $this->url->link("account/account", "", 'SSL');
		}
		
		// linkdin Libery file inculde
		require_once DIR_SYSTEM.'library/so_social/linkedIn/http.php';
		require_once DIR_SYSTEM.'library/so_social/linkedIn/oauth_client.php';
		
		$client = new oauth_client_class;
		$client->debug = false;
		$client->debug_http = true;
		$client->redirect_uri = $this->url->link('extension/module/so_sociallogin/LinkedinLogin', '', 'SSL');
		$client->client_id = $setting['so_sociallogin_linkdinapikey'];
		$application_line = __LINE__;
		$client->client_secret = $setting['so_sociallogin_linkdinsecretapi'];
		if (strlen($client->client_id) == 0 || strlen($client->client_secret) == 0)
		die('Please go to LinkedIn Apps page https://www.linkedin.com/secure/developer?newapp= , '.
			'create an application, and in the line '.$application_line.
			' set the client_id to Consumer key and client_secret with Consumer secret. '.
			'The Callback URL must be '.$client->redirect_uri.' Make sure you enable the '.
			'necessary permissions to execute the API calls your application needs.');
		$client->scope = 'r_basicprofile r_emailaddress';
		if (($success = $client->Initialize())) {
		  	if (($success = $client->Process())) {
				if (strlen($client->authorization_error)) {
			  		$client->error = $client->authorization_error;
			  		$success = false;
				} elseif (strlen($client->access_token)) {
			  		$success = $client->CallAPI(
							'http://api.linkedin.com/v1/people/~:(id,email-address,first-name,last-name,location,picture-url,public-profile-url,formatted-name)', 
							'GET', array(
								'format'=>'json'
							), array('FailOnAccessError'=>true), $user);
				}
		  	}
		   	$success = $client->Finalize($success);
		}
		
		if($success) {			
			$this->load->model('account/customer');
			$email = $user->emailAddress;			
			$customer_info = $this->model_account_customer->getCustomerByEmail($email);
			
			if(!empty($customer_info)){
				if ($customer_info && !$customer_info['approved']) {
					$this->session->data['warning'] = 'Customer not Approved';
				}
				else
				{
					if($this->customer->login($email, '', true)){
						$this->response->redirect($location);					
					}
				}
			}else {
				$password 					= rand();	
				$customerdata				= array();
				$customerdata['email'] 		= $email;
				$customerdata['password'] 	= $password;
				$customerdata['firstname'] 	= isset($user->firstName) ? $user->firstName : '';
				$customerdata['lastname'] 	= isset($user->lastName) ?$user->lastName  : '';
				$customerdata['fax'] 		= '';
				$customerdata['telephone'] 	= '';
				$customerdata['company'] 	= '';
				$customerdata['company_id'] = '';
				$customerdata['tax_id'] 	= '';
				$customerdata['address_1'] 	= '';
				$customerdata['address_2'] 	= '';
				$customerdata['city'] 		= '';
				$customerdata['city_id'] 	= '';
				$customerdata['postcode'] 	= '';
				$customerdata['country_id'] = 0;
				$customerdata['zone_id'] 	= 0;

				$this->model_account_customer->addCustomer($customerdata);
				if($this->customer->login($email, $password, true)){
					$this->response->redirect($location);
				}
			}
		}
	}

	private function clean_decode($server)
	{
		return $server;
	}
	
}