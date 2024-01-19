<?php
namespace App\Controller\Api;
use Cake\Event\Event;
use Cake\Network\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Cake\ORM\TableRegistry;
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['add', 'token','fbLogin','twitterLogin','googlePlus']);
    }
	
    /**
     * Create new user and return id plus JWT token
     */
    public function add() {
	
		if(!empty($this->request->data)){
			foreach ($this->request->data as $key => $value) {
				$this->request->data[$key] = trim($value);
			}
		}
		if (empty($this->request->data['username'])) {
			$errors[] = ['username'=>['_required'=>'Username is required.']];
		}
		if (empty($this->request->data['password'])) {
			$errors[] = ['password'=>['_required'=>'Password is required.']];
		}
		if (empty($this->request->data['email'])) {
			$errors[] = ['email'=>['_required'=>'Email is required']];
		}
		if (empty($this->request->data['first_name'])) {
			$errors[] = ['first_name'=>['_required'=>'First name is required']];
		}
		if (empty($this->request->data['last_name'])) {
			$errors[] = ['last_name'=>['_required'=>'Last name is required']];
		}
		if(!empty($errors)){
			$this->response->statusCode(422);
			$this->set([
				'success' => false,
				'data' => [
					'code' => 422,
					'url' => h($this->request->here()),
					'message' => count($errors).' validation errors occurred',
					'error' => '',
					'errorCount' => count($errors),
					'errors' => $errors,
					],
				'_serialize' => ['success', 'data']]);
				 return ;
		}
	
		$this->Crud->on('afterSave', function(Event $event) {
            if ($event->subject->created) {
				$user_data = $this->Users->get($event->subject->entity->id); // Return user regarding id
				$user_data->first_name = $this->request->data['first_name'];
				if(!empty($this->request->data['last_name'])){
					$user_data->last_name = $this->request->data['last_name'];
				}
				$verification_code = substr(md5(uniqid()), 0, 20);
				$user_data->activation_code = $verification_code;
				$user_data->status = STATUS_INACTIVE;
				$this->Users->save($user_data);
				$this->sinchRegister($user_data->username);
				//email content
				$email = $this->request->data['email'];
				$emailData = TableRegistry::get('EmailTemplates');
				$emailDataResult = $emailData->find()->where(['slug' => 'user_registration']);
				$emailContent = $emailDataResult->first();
				$activation_url = LIVEURL . 'en/users/activate/'. base64_encode($email).'/'.$verification_code;				
				$activation_link	= $activation_url;				
				$to = $email;
				$subject = $emailContent->subject;
				$mail_message_data = $emailContent->description;
				$activation_link	=' <a href="'.$activation_url.'" target="_blank" shape="rect">'.__("activationLink").'</a>';
				$mail_message = str_replace(array('{NAME}','{USERNAME}','{EMAIL}','{ACTIVATION_LINK}','{PASSWORD}'), array($user_data->first_name,$user_data->first_name,$email,$activation_link,$this->request->data['password']), $mail_message_data);
				$from = SITE_EMAIL;					
				parent::sendEmail($from, $to, $subject, $mail_message);	
				//end email
				$this->set('data', [
                    'id' => base64_encode($event->subject->entity->id),
                    'token' => JWT::encode(
                        [
                            'sub' => $event->subject->entity->id,
                            'exp' =>  time() + 604800
                        ],
                        Security::salt()
                    )
                ]);
                $this->Crud->action()->config('serialize.data', 'data');
            }
        });
        return $this->Crud->execute();
    }
	
	function sinchRegister($username = null){
		
		$data_string = array("password" => "q}dDmuqwiqfix","identities"=>array(0=>array("type"=>"username","endpoint"=>$username)));
		$curl = curl_init('https://userapi.sinch.com/v1/users');
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); 
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data_string));                                                                  
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
				curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',
					'Accept: application/json',
					"Authorization: Application ".SINCH_APP_ID
					)                                                                       
			);                    
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		
	}
	
    /**
     * Return JWT token if posted user credentials pass FormAuthenticate
     */
    public function token()
    {
		if($this->request->is('post')){
			$user = $this->Auth->identify();
			if (!$user) {
				throw new UnauthorizedException('Invalid username or password');
			}
			$token = JWT::encode(
						[
							'sub' => $user['id'],
							'exp' =>  time() + 604800
						],
						Security::salt()
					);
			
			$user_data = $this->Users->get($user['id']); // Return user regarding id
			$user_data->token = $token;
			$user_data->last_login = date('Y-m-d H:i:s');
			$this->Users->save($user_data);
			$this->set([
				'success' => true,
				'data' => [
					'token' => $token,
					'userId' => base64_encode($user['id']),
				],
				'_serialize' => ['success', 'data']
			]);
		}
		else
		{
			$this->set([
				'success' => false,
				'data' => [
					'code' =>405,
					'message' =>'Method Not Allowed'
				],
				'_serialize' => ['success', 'data']
			]);
			
		}
    }
	
	
	public function roles()
	{
		if($this->request->is('get')){
			$checkAuth = $this->check_user_authrization();
			if($checkAuth){
				$this->loadModel('Roles');	
				$this->paginate = [
					'fields' => [
						'id', 'name'
					]
				];
				$roles = $this->paginate($this->Roles);
				$this->set([
					'success' => true,
					'data' => $roles,
					'_serialize' => ['data','success']
				]);
			}
			else
			{
				
				$this->set([
					'success' => false,
					'data' => [
						'message' =>'Invalid Access'
					],
					'_serialize' => ['success', 'data']
				]);
			}
			
		}
		else
		{
			$this->set([
				'success' => false,
				'data' => [
					'code' =>405,
					'message' =>'Method Not Allowed'
				],
				'_serialize' => ['success', 'data']
			]);
			
		}
	}
	
	public function logout(){
		if($this->request->is('get')){
			$checkAuth = $this->check_user_authrization();
			if($checkAuth){
				$user = $this->Auth->identify();
				if (!$user) {
					throw new UnauthorizedException('Invalid username or password');
				}
				$user_data = $this->Users->get($user['id']); // Return user regarding id
				$user_data->token = NULL;
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'message' =>'Logout is successfully'
					],
					'_serialize' => ['success', 'data']
				]);
				
			}
			else
			{
				
				$this->set([
					'success' => false,
					'data' => [
						'message' =>'Invalid Access'
					],
					'_serialize' => ['success', 'data']
				]);
			}
		}
		else
		{
			$this->set([
				'success' => false,
				'data' => [
					'code' =>405,
					'message' =>'Method Not Allowed'
				],
				'_serialize' => ['success', 'data']
			]);
			
		}	
		
        
	}
	
	public function fbLogin(){
		if($this->request->is('post')){
			$userData = TableRegistry::get('Users');
			if(!empty($this->request->data)){
				foreach ($this->request->data as $key => $value) {
					$this->request->data[$key] = trim($value);
				}
			}
			if (empty($this->request->data['facebook_id'])) {
				$errors[] = ['facebook_id'=>['_required'=>'Facebook id required']];
			}
			if (empty($this->request->data['first_name'])) {
				$errors[] = ['first_name'=>['_required'=>'First name required']];
			}
			if (empty($this->request->data['email'])) {
				$errors[] = ['email'=>['_required'=>'Email is required']];
			}
			if(!empty($errors)){
				$this->response->statusCode(422);
				$this->set([
					'success' => false,
					'data' => [
						'code' => 422,
						'url' => h($this->request->here()),
						'message' => count($errors).' validation errors occurred',
						'error' => '',
						'errorCount' => count($errors),
						'errors' => $errors,
						],
					'_serialize' => ['success', 'data']]);
					 return ;
			}
			$facebookRowCount = 0;$emailRowCount = 0;$email = "";
			if (!empty($this->request->data['facebook_id'])) {
				$facebookId = trim($this->request->data['facebook_id']);
				$queryFacebook = $userData->find()->where(['facebook_id' => $facebookId,'status'=>STATUS_INACTIVE]);
				$facebookRowCount = $queryFacebook->count();
				if($facebookRowCount != 0)
				{
					$this->response->statusCode(422);
					$this->set([
					'success' => false,
					'data' => [
						'code' => 422,
						'url' => h($this->request->here()),
						'message' => '1 validation errors occurred',
						'error' => '',
						'errorCount' => 1,
						'errors' => 'Your Account blocked by administrator.Please contact to administrator.',
						],
					'_serialize' => ['success', 'data']]);
					 return ;
				}
				$queryFacebook = $userData->find()->where(['facebook_id' => $facebookId,'status'=>STATUS_ACTIVE]);
				$facebookRowCount = $queryFacebook->count();
				
			}
			if (!empty($this->request->data['email'])) {
				$email = trim($this->request->data['email']);
				$queryEmail = $userData->find()->where(['email' => $email,'status'=>STATUS_INACTIVE]);
				$emailRowCount = $queryEmail->count();
				if($emailRowCount != 0)
				{
					$this->response->statusCode(422);
					$this->set([
					'success' => false,
					'data' => [
						'code' => 422,
						'url' => h($this->request->here()),
						'message' => '1 validation errors occurred',
						'error' => '',
						'errorCount' => 1,
						'errors' => 'Your Account blocked by administrator.Please contact to administrator.',
						],
					'_serialize' => ['success', 'data']]);
					 return ;
				}
				
				
				$queryEmail = $userData->find()->where(['email' => $email,'status'=>STATUS_ACTIVE]);
				$emailRowCount = $queryEmail->count();
			}
			$firstName = (!empty(trim($this->request->data['first_name'])))? $this->request->data['first_name']:"";
			$lastName = (!empty(trim($this->request->data['last_name'])))? $this->request->data['last_name']:"";
			if($facebookRowCount == 0 && $emailRowCount == 0){
				$userNewData = $userData->newEntity();
				$userNewData->username = $this->genreateRandomNickname($firstName);
				$userNewData->first_name = $firstName;
				$userNewData->last_name = $lastName;
				$userNewData->facebook_id = $facebookId;
				$userNewData->email = $email;
				$userNewData->status = STATUS_ACTIVE;
				$userNewData->last_login = date('Y-m-d H:i:s');
				$userNewData->created = date('Y-m-d H:i:s');
				$userNewData->modified = date('Y-m-d H:i:s');
				if($userData->save($userNewData)){
					$id = $userNewData->id;
					$token = JWT::encode(
						[
							'sub' => $id,
							'exp' =>  time() + 604800
						],
						Security::salt()
					);
					$user_data = $this->Users->get($id); // Return user regarding id
					$user_data->token = $token;
					$this->Users->save($user_data);
					$this->set([
						'success' => true,
						'data' => [
							'token' => $token,
							'userId' => base64_encode($id),
						],
						'_serialize' => ['success', 'data']
					]);
				}
				if(!empty($userNewData->errors())){
					$this->response->statusCode(422);
					$this->set([
					'success' => false,
					'data' => [
						'code' => 422,
						'url' => h($this->request->here()),
						'message' => count($userNewData->errors()).' validation errors occurred',
						'error' => '',
						'errorCount' => count($userNewData->errors()),
						'errors' => $userNewData->errors(),
						],
					'_serialize' => ['success', 'data']]);
					 return ;
				
				
				}
			}
			else if($facebookRowCount == 1 && $emailRowCount == 1){
				$result = $queryFacebook->toArray()[0];
				$id = $result->id;
				$token = JWT::encode(
                    [
                        'sub' => $id,
                        'exp' =>  time() + 604800
                    ],
                    Security::salt()
                );
				$user_data = $this->Users->get($id); // Return user regarding id
				$user_data->token = $token;
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'token' => $token,
						'userId' => base64_encode($id),
					],
					'_serialize' => ['success', 'data']
				]);
				
			}
			else if($facebookRowCount == 1 && $emailRowCount == 0){
				$result = $queryFacebook->toArray()[0];
				$id = $result->id;
				$token = JWT::encode(
                    [
                        'sub' => $id,
                        'exp' =>  time() + 604800
                    ],
                    Security::salt()
                );
				$user_data = $this->Users->get($id); // Return user regarding id
				$user_data->token = $token;
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'token' => $token,
						'userId' => base64_encode($id),
					],
					'_serialize' => ['success', 'data']
				]);
				
			}
			else if($facebookRowCount == 0 && $emailRowCount == 1){
				$result = $queryEmail->toArray()[0];
				$id = $result->id;
				$token = JWT::encode(
                    [
                        'sub' => $id,
                        'exp' =>  time() + 604800
                    ],
                    Security::salt()
                );
				$user_data = $this->Users->get($id); // Return user regarding id
				$user_data->facebook_id = $facebookId;
				$user_data->token = $token;
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'token' => $token,
						'userId' => base64_encode($id),
					],
					'_serialize' => ['success', 'data']
				]);
				
			}
		
		}
		else
		{
			throw new MethodNotAllowedException();
		}
	}
	
	public function genreateRandomNickname($name=null){
		$userData = TableRegistry::get('Users');
		$queryUser = $userData->find()->where(['username LIKE' => $name]);
		$queryRowCount = $queryUser->count();
		if($queryRowCount > 0)
		{
			$userName = $name.rand(0,100);
			return $userName;
		}
		return $name;
	}
	
	public function twitterLogin(){
		if($this->request->is('post')){
			$userData = TableRegistry::get('Users');
			if(!empty($this->request->data)){
				foreach ($this->request->data as $key => $value) {
					$this->request->data[$key] = trim($value);
				}
			}
			if (empty($this->request->data['twitter_id'])) {
				$errors[] = ['twitter_id'=>['_required'=>'Twitter id is required']];
			}
			if (empty($this->request->data['screen_name'])) {
				$errors[] = ['screen_name'=>['_required'=>'Screen Name is required']];
			}
			if(!empty($errors)){
				$this->response->statusCode(422);
								$this->set([
									'success' => false,
									'data' => [
										'code' => 422,
										'url' => h($this->request->here()),
										'message' => count($errors).' validation errors occurred',
										'error' => '',
										'errorCount' => count($errors),
										'errors' => $errors,
										],
									'_serialize' => ['success', 'data']]);
									 return ;
			}	
			$firstName = (!empty($this->request->data['first_name']))? $this->request->data['first_name']:"";
			if (!empty($this->request->data['twitter_id'])) {
				$twitterId = trim($this->request->data['twitter_id']);
				$userName = trim($this->request->data['screen_name']);
				$queryTwitter = $userData->find()->where(['twitter_id' => $twitterId,'status'=>STATUS_INACTIVE]);
				$twitterRowCount = $queryTwitter->count();
				if($twitterRowCount > 0)
				{
					$this->response->statusCode(422);
					$this->set([
					'success' => false,
					'data' => [
						'code' => 422,
						'url' => h($this->request->here()),
						'message' => '1 validation errors occurred',
						'error' => '',
						'errorCount' => 1,
						'errors' => 'Your Account blocked by administrator.Please contact to administrator.',
						],
					'_serialize' => ['success', 'data']]);
					 return ;
				}
				$queryTwitter = $userData->find()->where(['twitter_id' => $twitterId,'status'=>STATUS_INACTIVE]);
				$twitterRowCount = $queryTwitter->count();
				if($twitterRowCount == 0){
					$userNewData = $userData->newEntity();
					$userNewData->username = $this->genreateRandomNickname($firstName);
					$userNewData->first_name = $firstName;
					$userNewData->twitter_id = $twitterId;
					$userNewData->status = STATUS_ACTIVE;
					$userNewData->last_login = date('Y-m-d H:i:s');
					$userNewData->created = date('Y-m-d H:i:s');
					$userNewData->modified = date('Y-m-d H:i:s');
					$userData->save($userNewData);
					$id = $userNewData->id;
					$token = JWT::encode(
						[
							'sub' => $id,
							'exp' =>  time() + 604800
						],
						Security::salt()
					);
					$user_data = $this->Users->get($id); // Return user regarding id
					$user_data->token = $token;
					$this->Users->save($user_data);
					$this->set([
						'success' => true,
						'data' => [
							'token' => $token,
							'userId' => base64_encode($id),
						],
						'_serialize' => ['success', 'data']
					]);
				}
				else
				{
					$result = $queryTwitter->toArray()[0];
					$id = $result->id;
					$token = JWT::encode(
						[
							'sub' => $id,
							'exp' =>  time() + 604800
						],
						Security::salt()
					);
					$user_data = $this->Users->get($id); // Return user regarding id
					$user_data->token = $token;
					$this->Users->save($user_data);
					$this->set([
						'success' => true,
						'data' => [
							'token' => $token,
							'userId' => base64_encode($id),
						],
						'_serialize' => ['success', 'data']
					]);
				
				}
			}
			
		}
		else
		{
			throw new MethodNotAllowedException();
		}
	}
	
	
	public function googlePlus(){
		if($this->request->is('post')){
			if(!empty($this->request->data)){
				foreach ($this->request->data as $key => $value) {
					$this->request->data[$key] = trim($value);
				}
			}
			$userData = TableRegistry::get('Users');
			if (empty($this->request->data['google_id'])) {
				$errors[] = ['google_id'=>['_required'=>'Google id is required']];
			}
			if (empty($this->request->data['email'])) {
				$errors[] = ['email'=>['_required'=>'Email is required']];
			}
			if (empty($this->request->data['first_name'])) {
				$errors[] = ['first_name'=>['_required'=>'First Name is required']];
			}
			if(!empty($errors)){
				$this->response->statusCode(422);
								$this->set([
									'success' => false,
									'data' => [
										'code' => 422,
										'url' => h($this->request->here()),
										'message' => count($errors).' validation errors occurred',
										'error' => '',
										'errorCount' => count($errors),
										'errors' => $errors,
										],
									'_serialize' => ['success', 'data']]);
									 return ;
			}
			$googleRowCount = 0;$emailRowCount = 0;$email = "";
			if (!empty($this->request->data['google_id'])) {
				$googleId = trim($this->request->data['google_id']);
				$queryGoogle = $userData->find()->where(['googleplus_id' => $googleId,'status'=>STATUS_INACTIVE]);
				$googleRowCount = $queryGoogle->count();
				if($googleRowCount > 0)
				{
					$this->response->statusCode(422);
					$this->set([
					'success' => false,
					'data' => [
						'code' => 422,
						'url' => h($this->request->here()),
						'message' => '1 validation errors occurred',
						'error' => '',
						'errorCount' => 1,
						'errors' => 'Your Account blocked by administrator.Please contact to administrator.',
						],
					'_serialize' => ['success', 'data']]);
					 return ;
				}
				$queryGoogle = $userData->find()->where(['googleplus_id' => $googleId,'status'=>STATUS_ACTIVE]);
				$googleRowCount = $queryGoogle->count();
				
			}
			if (!empty($this->request->data['email'])) {
				$email = trim($this->request->data['email']);
				$queryEmail = $userData->find()->where(['email' => $email,'status'=>STATUS_INACTIVE]);
				$emailRowCount = $queryEmail->count();
				if($emailRowCount > 0)
				{
					$this->response->statusCode(422);
					$this->set([
					'success' => false,
					'data' => [
						'code' => 422,
						'url' => h($this->request->here()),
						'message' => '1 validation errors occurred',
						'error' => '',
						'errorCount' => 1,
						'errors' => 'Your Account blocked by administrator.Please contact to administrator.',
						],
					'_serialize' => ['success', 'data']]);
					 return ;
				}
				
				
				$queryEmail = $userData->find()->where(['email' => $email,'status'=>STATUS_ACTIVE]);
				$emailRowCount = $queryEmail->count();
			}
			$firstName = (!empty($this->request->data['first_name']))? $this->request->data['first_name']:"";
			if($googleRowCount == 0 && $emailRowCount == 0){
				$userNewData = $userData->newEntity();
				$userNewData->username = $this->genreateRandomNickname($firstName);
				$userNewData->first_name = $firstName;
				$userNewData->googleplus_id = $googleId;
				$userNewData->email = $email;
				$userNewData->status = STATUS_ACTIVE;
				$userNewData->last_login = date('Y-m-d H:i:s');
				$userNewData->created = date('Y-m-d H:i:s');
				$userNewData->modified = date('Y-m-d H:i:s');
				$userData->save($userNewData);
				$id = $userNewData->id;
				$token = JWT::encode(
                    [
                        'sub' => $id,
                        'exp' =>  time() + 604800
                    ],
                    Security::salt()
                );
				$user_data = $this->Users->get($id); // Return user regarding id
				$user_data->token = $token;
				$user_data->last_login = date('Y-m-d H:i:s');
				$user_data->modified = date('Y-m-d H:i:s');
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'token' => $token,
						'userId' => base64_encode($id),
					],
					'_serialize' => ['success', 'data']
				]);
			}
			else if($googleRowCount == 1 && $emailRowCount == 1){
				$result = $queryGoogle->toArray()[0];
				$id = $result->id;
				$token = JWT::encode(
                    [
                        'sub' => $id,
                        'exp' =>  time() + 604800
                    ],
                    Security::salt()
                );
				$user_data = $this->Users->get($id); // Return user regarding id
				$user_data->token = $token;
				$user_data->last_login = date('Y-m-d H:i:s');
				$user_data->modified = date('Y-m-d H:i:s');
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'token' => $token,
						'userId' => base64_encode($id),
					],
					'_serialize' => ['success', 'data']
				]);
				
			}
			else if($googleRowCount == 1 && $emailRowCount == 0){
				$result = $queryGoogle->toArray()[0];
				$id = $result->id;
				$token = JWT::encode(
                    [
                        'sub' => $id,
                        'exp' =>  time() + 604800
                    ],
                    Security::salt()
                );
				$user_data = $this->Users->get($id); // Return user regarding id
				$user_data->token = $token;
				$user_data->last_login = date('Y-m-d H:i:s');
				$user_data->modified = date('Y-m-d H:i:s');
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'token' => $token,
						'userId' => base64_encode($id),
					],
					'_serialize' => ['success', 'data']
				]);
				
			}
			else if($googleRowCount == 0 && $emailRowCount == 1){
				$result = $queryEmail->toArray()[0];
				$id = $result->id;
				$token = JWT::encode(
                    [
                        'sub' => $id,
                        'exp' =>  time() + 604800
                    ],
                    Security::salt()
                );
				$user_data = $this->Users->get($id); // Return user regarding id
				$user_data->googleplus_id = $googleId;
				$user_data->last_login = date('Y-m-d H:i:s');
				$user_data->modified = date('Y-m-d H:i:s');
				$user_data->token = $token;
				$this->Users->save($user_data);
				$this->set([
					'success' => true,
					'data' => [
						'token' => $token,
						'userId' => base64_encode($id),
					],
					'_serialize' => ['success', 'data']
				]);
				
			}
		
		}
		else
		{
			throw new MethodNotAllowedException();
		}
	}
	
	private function check_user_authrization(){
		
		if(!empty($this->request->header('Authorization')) && !empty($this->request->header('userId'))){
		
			$token =  str_replace("Bearer ","",$this->request->header('Authorization'));
			$userID = base64_decode($this->request->header('userId'));
			
			$users = TableRegistry::get('Users');

			// Start a new query.
			$query = $users->find()
			->where(['id' => $userID, 'token'=>$token]);
			
			$row = $query->count();
			return $row;
		}
		else
		{
			return 0;
		}
			
		
		
	}
	
	
	
	
	
	
}