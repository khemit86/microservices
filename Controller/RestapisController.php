<?php
namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\View\Helper\TextHelper;
use Cake\I18n\I18n;

class RestapisController extends AppController
{
	// public $helpers = ['MetaTag'];	
	public function initialize()
    {
        parent::initialize(); 
		// $this->Auth->allow(['index']);  
        $this->loadComponent('Flash'); // Include the FlashComponent
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
	public function index($id = null)
    {	
		
		$this->viewBuilder()->layout('lay_restapi');
		$apiQuery = $this->Restapis->find('all',['conditions'=>['status'=>STATUS_ACTIVE],'order'=>['id'=>'ASC']]);
		$this->set('left_nav',$apiQuery);
		
		if(isset($id) && !empty($id)){
			$details = $this->Restapis->get($id);
			$this->set('details',$details);			
		}		
		
    }
}