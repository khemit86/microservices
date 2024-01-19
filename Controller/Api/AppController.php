<?php
namespace App\Controller\Api;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Network\Email\Email;
/**
 * AppController specific to API resources
 */
class AppController extends Controller
{
    use \Crud\Controller\ControllerTrait;
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.View',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete'
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'Crud.ApiQueryLog'
            ]
        ]);
        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate' => [
                'Form' => [
                    'scope' => ['Users.status' => STATUS_ACTIVE],
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'userModel' => 'Users',
                    'scope' => ['Users.status' => STATUS_ACTIVE],
                    'fields' => [
                        'username' => 'id'
                    ],
                    'queryDatasource' => true,
                ]
            ],
            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize'
        ]);
    }
	
	
	
	public function beforeFilter(Event $event){
		
		define('APIPageLimit', 3);
		$settings = TableRegistry::get('Settings');
		$query = $settings->find();
		$settings = $query->toArray();
		foreach($settings as $setting)
		{
			define(strtoupper($setting->name), $setting->value);
		}
	}
	
	public function sendEmail($from = NULL,$to = NULL,$subject = NULL,$message = NULL)
	{
		
		$email = new Email();
        try {
            $email->from($from)
                ->profile('Sendgrid')
                ->to($to)
                ->subject($subject)
                ->emailFormat("both")
                ->template('default')
                ->send($message);
            //$this->Flash->success("Message sent.");
        } catch (Exception $ex) {
            echo 'Exception : ', $ex->getMessage(), "\n";
        }                              
      //  return $this->redirect(['action' => 'index']);
		
	}
	
	
	function getUserDetail($userId = null){
		$users = TableRegistry::get('Users');
		$userArray = $users
				->find()
				->where(['id' => $userId])
				->first()->toArray();
		return $userArray;
	}
		
		
}
