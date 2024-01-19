<?php
namespace App\Model\Table;
use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;
/**
 * Users Model
 */

class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('users');
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
			->notEmpty('username', 'Username is required.')
			->add('username', [
				'length' => [
					'rule' => ['lengthBetween', 6, 25],
					'message' => 'username Between 6 to 25 characters.',
				]
			])
			->add('username', 'validFormat', [
              'rule' => 'alphanumeric',
              'message' => 'Only alphabets and numbers allowed.',
            ])
			//->requirePresence('password')
			->notEmpty('password', 'Password is required.')
			->add('password', 'required', [
				'rule' => 'notBlank',
				'required' => true
			])
			->add('password', 'size', [
				'rule' => ['lengthBetween', 4, 20],
				'message' => 'Password should Between 4 to 20'
			])
			//->requirePresence('email')
			->notEmpty('email', 'Email is required.')
			->add('email', 'validFormat', [
				'rule' => 'email',
				'message' => 'E-mail must be valid.'
			])
			//->requirePresence('first_name')
			->notEmpty('first_name', 'First name is required.')
			->add('first_name', 'validFormat', [
              'rule' => 'alphanumeric',
              'message' => 'Only alphabets and numbers allowed.',
            ])
			->add('first_name', [
				'length' => [
					'rule' => ['lengthBetween', 2, 30],
					'message' => 'first name Between 2 to 30 characters.',
				]
			])
			->notEmpty('last_name', 'Last name is required.')
			->add('last_name', 'validFormat', [
              'rule' => 'alphanumeric',
              'message' => 'Only alphabets and numbers allowed.',
            ])
			->add('last_name', [
				'length' => [
					'rule' => ['lengthBetween', 2, 30],
					'message' => 'last name Between 2 to 30 characters.',
				]
			])
			->add('image', [
                'mimeType' => [
                        'rule' => ['mimeType', ['image/gif', 'image/png', 'image/jpg', 'image/jpeg']],
                        'message' => 'Please only upload images (gif, png, jpg).',
                        'allowEmpty' => TRUE,
                ],
                'fileSize' => [
                        'rule' => ['fileSize', '<=', '1MB'],
                        'message' => 'Profile image must be less than 1MB.',
                        'allowEmpty' => TRUE,
                ]

            ])
			->notEmpty('zipcode', 'zipcode is required.')
			->add('zipcode', 'validFormat', [
              'rule' => 'numeric',
              'message' => 'Only numbers allowed.',
            ])
			->add('zipcode', [
				'length' => [
					'rule' => ['maxLength', 5],
					'message' => 'Zipcode must be no larger than 5 number long.',
				],
				'length1' => [
					'rule' => ['minLength', 5],
					'message' => 'Zipcode must be at least 5 number long.',
				]
			])
			
			;
			//->requirePresence('role_id')
			//->notEmpty('role_id', 'Role is required(1:Client,2:Worker,3:Company,4:ProExpert)')
			;
			//->requirePresence('username')
			//->notEmpty('username', 'You need to give a username.');
            //->requirePresence('username', 'create')
            //->notEmpty('username')
           // ->requirePresence('password', 'create')
           // ->notEmpty('password')
            //->add('active', 'valid', ['rule' => 'boolean'])
           // ->requirePresence('active', 'create')
            //->notEmpty('active')
			//->requirePresence('role_id', 'create')
            //->notEmpty('role_id');
			
        return $validator;
    }
	
	 /**
     * Custom validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationRegister($validator)
    {
		 
        $validator
            //->add('id', 'valid', ['rule' => 'numeric'])
           // ->allowEmpty('id', 'create')
			->requirePresence('username')
			->notEmpty('username', __('usernameRequired'))
			->add('username', [
				'length' => [
					'rule' => ['lengthBetween', 6, 25],
					'message' => __('usernameBetween6To25Character'),
				]
			])
			->add('username', 'validFormat', [
              'rule' => 'alphanumeric',
              'message' => __('onlyAlphabetsNumbersAllowed'),
            ])
			->requirePresence('password')
			->notEmpty('password', __('passwordRequired'))
			->add('password', 'required', [
				'rule' => 'notBlank',
				'required' => true
			])
			->add('password', 'size', [
				'rule' => array('lengthBetween', 4, 20),
				'message' => __('passwordShouldBetween4To20')
			])
			->requirePresence('email')
			->notEmpty('email', __('emailIsRequired'))
			->add('email', 'validFormat', [
				'rule' => 'email',
				'message' => __('emailMustValid')
			])
			
			->notEmpty('company_name', __('companyNameRequired'))
			->notEmpty('vat_id', __('vatIdRequired'))
			
			//->requirePresence('role_id')
			//->notEmpty('role_id', 'Role is required(1:Client,2:Worker,3:Company,4:ProExpert)')
			;
			//->requirePresence('username')
			//->notEmpty('username', 'You need to give a username.');
            //->requirePresence('username', 'create')
            //->notEmpty('username')
           // ->requirePresence('password', 'create')
           // ->notEmpty('password')
            //->add('active', 'valid', ['rule' => 'boolean'])
           // ->requirePresence('active', 'create')
            //->notEmpty('active')
			//->requirePresence('role_id', 'create')
            //->notEmpty('role_id');
        return $validator;
    }
	
	 
	
	/**
     * Custom validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationFpassword($validator)
    {
		 
        $validator
            //->add('id', 'valid', ['rule' => 'numeric'])
           // ->allowEmpty('id', 'create')
			->requirePresence('email')
			->notEmpty('email', __('emailIsRequired'))
			->add('email', 'validFormat', [
				'rule' => 'email',
				'message' => __('emailMustValid')
			])
			;
			//->requirePresence('username')
			//->notEmpty('username', 'You need to give a username.');
            //->requirePresence('username', 'create')
            //->notEmpty('username')
           // ->requirePresence('password', 'create')
           // ->notEmpty('password')
            //->add('active', 'valid', ['rule' => 'boolean'])
           // ->requirePresence('active', 'create')
            //->notEmpty('active')
			//->requirePresence('role_id', 'create')
            //->notEmpty('role_id');
        return $validator;
    }
	
	
	public function validationPassword(Validator $validator )
    {
		

        $validator
            ->add('password1', [
                'length' => [
                    'rule' => ['minLength', 6],
                    'message' => __('passwordAtLeast6Character'),
                ]
            ])
            ->add('password1',[
                'match'=>[
                    'rule'=> ['compareWith','password2'],
                    'message'=>__('passwordDoesNotMatch'),
                ]
            ])
            ->notEmpty('password1');
			
			
        $validator
            ->add('password2', [
                'length' => [
                    'rule' => ['minLength', 6],
                    'message' => __('passwordAtLeast6Character'),
                ]
            ])
            ->add('password2',[
                'match'=>[
                    'rule'=> ['compareWith','password1'],
                    'message'=>__('passwordDoesNotMatch'),
                ]
            ])
            ->notEmpty('password2');

        return $validator;
    }
		
	
    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['username'],__('userNameAlreadyExists')));
		$rules->add($rules->isUnique(['email'],__('emailAlreadyExists')));
        return $rules;
    }
	
	public function validationContact($validator){
		
		$validator
			->requirePresence('salution')
			->notEmpty('salution', __('salutionRequired'))
			->add('salution', 'required', [
				'rule' => 'notBlank',
				'required' => true
			])
			->requirePresence('first_name')
			->notEmpty('first_name', __('firstNameRequired'))
			->add('first_name', 'required', [
				'rule' => 'notBlank',
				'required' => true
			])
			->requirePresence('last_name')
			->notEmpty('last_name', __('lastNameRequired'))
			->add('last_name', 'required', [
				'rule' => 'notBlank',
				'required' => true
			])
			
			->requirePresence('email')
			->notEmpty('email', 'Email is required.')
			->add('email', 'required',[
				'rule' => 'notBlank',
				'required' => true
			])
			->requirePresence('message')
			->notEmpty('message', __('messageRequired'))
			->add('message', 'required', [
				'rule' => 'notBlank',
				'required' => true
			])
			;
		return $validator;
    }
	
	/* public function validationNewsletter($validator){
		
		$validator
			->requirePresence('email')
			->notEmpty('email', 'Email is required.')
			->add('email', 'validFormat', [
				'rule' => 'email',
				'message' => 'E-mail must be valid.'
			])
			 ;
		return $validator;
    } */
	
	
	public function validationChangePassword(Validator $validator )
    {
		
        $validator
            ->add('old_password','custom',[
                'rule'=>  function($value, $context){
					
                    $user = $this->get($context['data']['id']);
                    if ($user) {
                        if ((new DefaultPasswordHasher)->check($value, $user->password)) {
							
                            return true;
                        }
                    }
                    return false;
                },
                'message'=>__('oldPasswordNotMatch'),
            ])
            ->notEmpty('old_password');

        $validator
            ->add('password1', [
                'length' => [
                    'rule' => ['minLength', 6],
                    'message' => __('passwordAtLeast6Character'),
                ]
            ])
            ->add('password1',[
                'match'=>[
                    'rule'=> ['compareWith','password2'],
                    'message'=>__('passwordsDoesNotMatch'),
                ]
            ])
            ->notEmpty('password1');
			
			
        $validator
            ->add('password2', [
                'length' => [
                    'rule' => ['minLength', 6],
                    'message' => __('passwordAtLeast6Character'),
                ]
            ])
            ->add('password2',[
                'match'=>[
                    'rule'=> ['compareWith','password1'],
                    'message'=>__('passwordsDoesNotMatch'),
                ]
            ])
            ->notEmpty('password2');

        return $validator;
    }
}