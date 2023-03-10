<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;
/**
 * Message Model
 *
 * @property \App\Model\Table\UserTable&\Cake\ORM\Association\BelongsTo $User
 * @property \App\Model\Table\UserTable&\Cake\ORM\Association\BelongsTo $User
 *
 * @method \App\Model\Entity\Message newEmptyEntity()
 * @method \App\Model\Entity\Message newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Message[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Message get($primaryKey, $options = [])
 * @method \App\Model\Entity\Message findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Message patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Message[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Message|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Message saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Message[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Message[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Message[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Message[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class MessageTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('message');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Transmitter', [
            'className' => 'User',
            'PropertyName' => 'Transmitter',
            'foreignKey' => 'transmitter_user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Receiver', [
            'className' => 'User',
            'PropertyName' => 'Receiver',
            'foreignKey' => 'receiver_user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('User', [
            'className' => 'User',
            'PropertyName' => 'User',
            'joinType' => 'INNER',
        ]);
    }

    //Capitalize
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
        {
        if (isset($data['title'])) {
            $data['title'] = ucfirst($data["title"]);
        }
        if (isset($data['content'])) {
            $data['content'] = ucfirst($data["content"]);
        }
    }

        
    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('message_date', 'create',__('La fecha es requerida.'))
            ->notEmptyDateTime('message_date',__('La fecha no puede ser vac??a.'))
            ->add('message_date', [
                'custom' => [
                'rule' => [$this,'validoDateBetween'],
                'message' => 'La fecha debe ser entre 2022 y 2050.'
                , 'provider' => 'table'
                ]])
            ->add('message_date', [
                'dateTime' => [
                'rule' => ['dateTime'],
                'message' => 'La fecha introducida debe seguir un formato de fecha y hora correcto.',
                ],
                ]);

        $validator
            ->requirePresence('title', 'create', __('El campo t??tulo es requerido.'))
            ->notEmptyString('title',__('El t??tulo no puede ser vac??o.'))
            ->add('title',
            ['regex'=>[
                'rule' =>['custom','/^[\w??????-????-??\s.??,-]*$/i'],
                'message' => __('El t??tulo debe contener solo caracteres alfab??ticos, espacios y algunos s??mbolos [. , ?? ].')
            ]])
            ->add('title',
            ['minLength'=>[
                'rule' =>['minLength',3],
                'message' => __('El t??tulo debe tener m??nimo 3 caracteres.')
            ]])
            ->add('title',
                ['maxLength'=>[
                    'rule' =>['maxLength',100],
                    'message' => __('El t??tulo debe tener m??ximo 100 caracteres.')
                ]]);

        $validator
            ->requirePresence('content', 'create', __('El campo contenido es requerido.'))
            ->notEmptyString('content', __('El contenido no puede ser vac??o.'))
            ->add('content',
            ['regex'=>[
                'rule' =>['custom','/^[\w??????-????-??\s.??,-]*$/i'],
                'message' => __('El contenido debe contener solo caracteres alfab??ticos, espacios y algunos s??mbolos [. , ?? ].')
            ]])
            ->add('content',
            ['minLength'=>[
                'rule' =>['minLength',3],
                'message' => __('El contenido debe tener m??nimo 3 caracteres.')
            ]])
            ->add('content',
                ['maxLength'=>[
                    'rule' =>['maxLength',700],
                    'message' => __('El contenido debe tener m??ximo 700 caracteres.')
                ]]);

        $validator
            ->integer('transmitter_user_id', __('El id del emisor debe ser un n??mero entero.'))
            ->notEmptyString('transmitter_user_id',__('El identificador de emisor no puede ser vac??o.'));

        $validator
            ->integer('receiver_user_id', __('El id del receptor debe ser un n??mero entero.'))
            ->notEmptyString('receiver_user_id',__('El identificador de receptor no puede ser vac??o.'));

        $validator
            ->notEmptyString('readed',__('Le??do no puede ser vac??o.'))
            ->requirePresence('readed', 'create', __('El campo le??do es requerido.'))

            ->add('readed',
                ['inList'=>[
                    'rule' =>['inList',['yes','no']],
                    'message' => __('Le??do debe ser s?? o no.')
                ]]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('transmitter_user_id', 'User'), ['errorField' => 'transmitter_user_id']);
        $rules->add($rules->existsIn('receiver_user_id', 'User'), ['errorField' => 'receiver_user_id']);

        return $rules;
    }

    public function validoDateBetween($date){
        if('2022-01-01 00:00:00' > $date || '2050-01-01 00:00:00' < $date){
            return false;
        }else{
            return true;
        }
    }
}
