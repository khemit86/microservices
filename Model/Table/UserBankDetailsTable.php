<?php
namespace App\Model\Table;
use App\Model\Entity\UserBankDetail;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
/**
 * Users Model
 */
class UserBankDetailsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('user_bank_details');
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
			->allowEmpty('paypal_email')
			->add('paypal_email', 'validFormat', [
				'rule' => 'email',
				'message' => __('paypalEmailMustValid')
			])
			->requirePresence('name')
			->notEmpty('name', __('banknameRequired'))
			->add('name', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('iban')
			->notEmpty('iban', __('ibanRequired'))
			->add('iban', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			
			;
        return $validator;
    }
	
	
}