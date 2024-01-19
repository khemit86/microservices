<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class OffersTable extends Table
{
    public function initialize(array $config)
    {
		 $this->table('offers');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
		
    }
	
	
	public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmpty('amount')
			->add('amount', 'validFormat', [
				'rule' => ['custom','/^[1-9][0-9]*$/'],
				'required' => true,
                'message' => __('positiveNumbersOnly')
			]);

        return $validator;
    }
	
	public function validationApi($validator){
		
		$validator
			->requirePresence('description')
			->notEmpty('description', 'Description is required.')
			->add('description', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('job_id')
			->notEmpty('job_id', 'Job id is required.')
			->add('job_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('amount')
			->notEmpty('amount', 'Amount is required.')
			->add('amount', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('amount', 'required', [
				 'rule' => array('money', 'left'),
				'message' => 'Please supply a valid monetary amount.'
			])
			 ;
		return $validator;
    }
	
	public function validationFront($validator){
		
		$validator
			->requirePresence('description')
			->notEmpty('description', __('DescriptionRequired'))
			->add('description', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('price')
			->notEmpty('price', __('priceRequired'))
			->add('price', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('price', 'required', [
				 'rule' => array('money', 'left'),
				'message' => __('supplyMonetaryPrice')
			])
			 ;
		return $validator;
		
		
    }
    
	
}
?>