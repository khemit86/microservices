<?php
namespace App\Model\Table;
use App\Model\Entity\Job;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Rule\IsUnique;
use Cake\ORM\Table;
use Cake\Validation\Validator;
/**
 * Roles Model
 */
class JobsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('jobs');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }
	
	public function validationApi($validator)
    {
		
		$validator
			->requirePresence('title')
			->notEmpty('title', 'Title is required.')
			->add('title', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('title', [
				'length' => [
					'rule' => ['lengthBetween', 4, 100],
					'message' => 'Title Between 4 to 100 characters.',
				]
			])
			->requirePresence('description')
			->notEmpty('description', 'Description is required.')
			->add('description', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			/* ->requirePresence('project_id')
			->notEmpty('project_id', 'Project Id is required.')
			->add('project_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) */
			->requirePresence('category_id')
			->notEmpty('category_id', 'Category Id is required.')
			->add('category_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			/* ->requirePresence('worker_id')
			->notEmpty('worker_id', 'Worker Id is required.')
			->add('worker_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) */
			->requirePresence('execution_time_id')
			->notEmpty('execution_time_id', 'Execution time Id is required.')
			->add('execution_time_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) 
			/* ->requirePresence('budget_id')
			->notEmpty('budget_id', 'Budget Id is required.')
			->add('budget_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) */
			->requirePresence('area_range_id')
			->notEmpty('area_range_id', 'Area Range Id is required.')
			->add('area_range_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('end_date')
			->notEmpty('end_date', __('EnddateRequired'))
			->add('end_date', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('zipcode')
			->notEmpty('zipcode','Zipcode is required.')
			->add('zipcode', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('zipcode', [
				'length' => [
					'rule' => ['lengthBetween', 5, 5 ],
					'message' => 'Zipcode length should be 5 characters.',
				]
			])
			->requirePresence('location')
			->notEmpty('location', __('locationRequired'))
			->add('location', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('location', [
				'length' => [
					'rule' => ['lengthBetween', 5, 100],
					'message' => __('locationBetween5To50'),
				]
			])
			
           ;
		return $validator;
		
		
    }
	
	public function validationFront($validator)
    {
		
		$validator
			->requirePresence('title')
			->notEmpty('title', __('titleRequired'))
			->add('title', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('title', [
				'length' => [
					'rule' => ['lengthBetween', 4, 100],
					'message' => __('titleBetween4To100'),
				]
			])
			->requirePresence('description')
			->notEmpty('description', __('DescriptionRequired'))
			->add('description', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
		/* 	->requirePresence('project_id')
			->notEmpty('project_id', __('projectRequired'))
			->add('project_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) */
			->requirePresence('category_id')
			->notEmpty('category_id', __('categoryRequired'))
			->add('category_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('end_date')
			->notEmpty('end_date', __('EnddateRequired'))
			->add('end_date', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('zipcode')
			->notEmpty('zipcode', __('postalcodeRequired'))
			->add('zipcode', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('zipcode', [
				'length' => [
					'rule' => ['lengthBetween', 5, 5 ],
					'message' => __('postalCodelength5'),
				]
			])
			->requirePresence('location')
			->notEmpty('location', __('locationRequired'))
			->add('location', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->add('location', [
				'length' => [
					'rule' => ['lengthBetween', 5, 100],
					'message' => __('locationBetween5To50'),
				]
			])
			->requirePresence('area_range_id')
			->notEmpty('area_range_id', __('areaRangeRequired'))
			->add('area_range_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			/* ->requirePresence('budget_id')
			->notEmpty('budget_id', 'Budget is required.')
			->add('budget_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) */
			/* ->requirePresence('execution_time_id')
			->notEmpty('execution_time_id', __('executionTimeRequired'))
			->add('execution_time_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) */
			/* ->requirePresence('worker_id')
			->notEmpty('worker_id', 'Worker Id is required.')
			->add('worker_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('execution_time_id')
			->notEmpty('execution_time_id', 'Execution time Id is required.')
			->add('execution_time_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('budget_id')
			->notEmpty('budget_id', 'Budget Id is required.')
			->add('budget_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			))
			->requirePresence('area_range_id')
			->notEmpty('area_range_id', 'Area Range Id is required.')
			->add('area_range_id', 'required', array(
				'rule' => 'notBlank',
				'required' => true
			)) */
			
           ;
		return $validator;
    }
    
}