<?php
namespace App\Model\Table;
use App\Model\Entity\UserDetail;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
/**
 * Users Model
 */
class UserDetailsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('user_details');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }
    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
		$validator = new Validator();
		$validator
			
			->allowEmpty('hourly_rate')
			 ->add('hourly_rate', 'validValue', [
				'rule' => ['range', 1, 1000]
			])
			->notEmpty('trade_license_type', __('typeRequired'))
			->add('trade_license_type', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			;
        return $validator;
    }
	
	
}