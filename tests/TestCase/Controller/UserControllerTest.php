<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\UserController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\UserController Test Case
 *
 * @uses \App\Controller\UserController
 */
class UserControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.User',
        'app.Address',
        'app.Feature',
        'app.FeatureUser',

    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\UserController::index()
     */
    public function testIndex(): void
    {
        $this->session([
            'Auth' => [
                    'id' => 1,
                    'DNI_CIF' => '22175395Z',
                    'name' => 'Prueba',
                    'lastname' => 'Prueba Prueba',
                    'username' => 'Pruebatesting',
                    'password' => 'prueba',
                    'email' => 'prueba@gmail.com',
                    'phone' => '639087621',
                    'birth_date' => '1999-12-14',
                    'role' => 'admin',
                    'addres_id' => 1
                
            ]
        ]);
        $this->get('/user');
        $this->assertResponseOk();
    }


    public function testSearch(): void
    {
        $this->get('/user?key=&keyRole=admin');
        $this->assertResponseOk();
    }

    // /**
    //  * Test view method
    //  *
    //  * @return void
    //  * @uses \App\Controller\UserController::view()
    //  */
    public function testView(): void
    {
        $this->session([
            'Auth' => [
                    'id' => 1,
                    'DNI_CIF' => '22175395Z',
                    'name' => 'Prueba',
                    'lastname' => 'Prueba Prueba',
                    'username' => 'Pruebatesting',
                    'password' => 'prueba',
                    'email' => 'prueba@gmail.com',
                    'phone' => '639087621',
                    'birth_date' => '1999-12-14',
                    'role' => 'admin',
                    'addres_id' => 1
                
            ]
        ]);
        $this->get('user/view/1');
        $this->assertResponseOk();
        $this->assertResponseContains('22175395Z');
    }

    public function testViewUnauthenticatedFail(): void
    {
        $this->enableCsrfToken();
        $this->get('user/view/1');
        $this->assertRedirectContains('/user/login');

    }
    // /**
    //  * Test add method
    //  *
    //  * @return void
    //  * @uses \App\Controller\UserController::add()
    //  */
    public function testAdd(): void
    {
        $this->session([
            'Auth' => [
                    'id' => 1,
                    'DNI_CIF' => '22175395Z',
                    'name' => 'Prueba',
                    'lastname' => 'Prueba Prueba',
                    'username' => 'Pruebatesting',
                    'password' => 'prueba',
                    'email' => 'prueba@gmail.com',
                    'phone' => '639087621',
                    'birth_date' => '1999-12-14',
                    'role' => 'admin',
                    'addres_id' => 1
            ]
        ]);
        $this->get('user/add');

        $this->assertResponseOk();

        $data=[
            'DNI_CIF' => '35728482Y',
            'name' => 'Prueba',
            'lastname' => 'Prueba Prueba',
            'username' => 'Nuevouser',
            'password' => 'prueba',
            'email' => 'nuevouser@gmail.com',
            'phone' => '639087691',
            'birth_date' => '1999-12-14',
            'role' => 'standar',
            'addres' => [
                'province' => 'Ourense',
                'postal_code' => 35004,
                'country' => 'Loremadd',
                'city' => 'Loremo',
                'street' => 'Lorem ipsum dolor sit amet'
            ],
            'feature_user' => [
                0 => ['value' => 'enfermera',
                     'feature_id' =>1
                    ],
                1 => ['value' => 'enfermeria',
                    'feature_id' =>2
                    ],
                2 => ['value' => 'single',
                   'feature_id' =>3
                    ],
                3 => ['value' => 2,
                  'feature_id' =>4
                    ],
                4 => ['value' => 'flat',
                    'feature_id' =>5
                    ],
                5 => ['value' => 'dog',
                    'feature_id' =>6
                    ],
                6 => ['value' => 2,
                    'feature_id' =>7
                    ],
                7 => ['value' => 'female',
                    'feature_id' =>8
                    ],
                
            ]
        ];
        $this->enableCsrfToken();
        $this->post('user/add', $data);
        $this->assertRedirect(['controller' => 'User', 'action' => 'login']);

        $user = TableRegistry::get('User');
        $query = $user->find()->where(['DNI_CIF' => $data['DNI_CIF']]);
        $this->assertEquals(1,$query->count());

        $addres = TableRegistry::get('Address');
        $query = $addres->find()->where(['country' => $data['addres']['country']]);
        $this->assertEquals(1,$query->count());
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\UserController::edit()
     */

    public function testEdit(): void
    {
        $this->session([
            'Auth' => [
                    'id' => 1,
                    'DNI_CIF' => '22175395Z',
                    'name' => 'Prueba',
                    'lastname' => 'Prueba Prueba',
                    'username' => 'Pruebatesting',
                    'password' => 'prueba',
                    'email' => 'prueba@gmail.com',
                    'birth_date' => '1999-12-14',
                    'phone' => '639087621',
                    'role' => 'admin',
                    'addres_id' => 1
                
            ]
        ]);
        $this->get('user/edit/1');

        $this->assertResponseOk();
        $data=[
            'DNI_CIF' => '35728482Y',
            'name' => 'Prueba',
            'lastname' => 'Prueba Prueba',
            'username' => 'Nuevouser',
            'password' => 'prueba',
            'email' => 'nuevouseredit@gmail.com',
            'phone' => '639087691',
            'birth_date' => '1999-12-14',
            'role' => 'standar',
            'addres' => [
                'province' => 'Ourense',
                'postal_code' => 35004,
                'country' => 'Loremmmedit',
                'city' => 'Loremo',
                'street' => 'Lorem ipsum dolor sit amet'
            ],
            'feature_user' => [
                0 => ['value' => 'enfermera',
                     'feature_id' =>1
                    ],
                1 => ['value' => 'enfermeria',
                    'feature_id' =>2
                    ],
                2 => ['value' => 'single',
                   'feature_id' =>3
                    ],
                3 => ['value' => 2,
                  'feature_id' =>4
                    ],
                4 => ['value' => 'flat',
                    'feature_id' =>5
                    ],
                5 => ['value' => 'snake',
                    'feature_id' =>6
                    ],
                6 => ['value' => 2,
                    'feature_id' =>7
                    ],
                7 => ['value' => 'female',
                    'feature_id' =>8
                    ],
                
            ]
        ];
        $this->enableCsrfToken();
        $this->post('user/edit/1',$data);
        $this->assertRedirect(['controller' => 'User', 'action' => 'index']);

        $user = TableRegistry::get('User');
        $query = $user->find()->where(['email' => $data['email']]);
        $this->assertEquals(1,$query->count());

        $addres = TableRegistry::get('Address');
        $query = $addres->find()->where(['country' => $data['addres']['country']]);
        $this->assertEquals(1,$query->count());

    }

    public function testEditUnauthenticatedFail(): void
    {
        $this->enableCsrfToken();
        $this->get('user/edit/1');
        $this->assertRedirectContains('/user/login');

    }
    // /**
    //  * Test login method
    //  *
    //  * @return void
    //  * @uses \App\Controller\UserController::login()
    //  */
    public function testLogin(): void
    {
        //Añado un usuario 
        $data=[
            'DNI_CIF' => '94802477L',
            'name' => 'Prueba',
            'lastname' => 'Prueba Prueba',
            'username' => 'Nuevouser',
            'password' => 'prueba',
            'email' => 'nuevouserlog@gmail.com',
            'phone' => '639087771',
            'birth_date' => '1999-12-14',
            'role' => 'standar',
            'addres' => [
                'province' => 'Ourense',
                'postal_code' => 35004,
                'country' => 'Loremadd',
                'city' => 'Loremo',
                'street' => 'Lorem ipsum dolor sit amet'
            ],
            'feature_user' => [
                0 => ['value' => 'enfermera',
                     'feature_id' =>1
                    ],
                1 => ['value' => 'enfermeria',
                    'feature_id' =>2
                    ],
                2 => ['value' => 'single',
                   'feature_id' =>3
                    ],
                3 => ['value' => 2,
                  'feature_id' =>4
                    ],
                4 => ['value' => 'flat',
                    'feature_id' =>5
                    ],
                5 => ['value' => 'snake',
                    'feature_id' =>6
                    ],
                6 => ['value' => 2,
                    'feature_id' =>7
                    ],
                7 => ['value' => 'female',
                    'feature_id' =>8
                    ],
                
            ]
        ];
        $this->enableCsrfToken();
        $this->post('user/add', $data);
        $this->assertRedirect(['controller' => 'User', 'action' => 'login']);

        $user = TableRegistry::get('User'); //Compruebo que efectivamente ese usuario existe
        $query = $user->find()->where(['DNI_CIF' => '94802477L']);
        $this->assertEquals(1,$query->count());

        //Logeo
        $this->enableSecurityToken();
        $this->enableCsrfToken();

        $this->get('/user/login');
        $this->assertResponseOk();

        $this->post('/user/login', [
            'DNI_CIF' => '94802477L',
            'password' => 'prueba' ]
        );
        $this->assertRedirect(['controller' => 'Pages', 'action' => 'index']);
        $this->assertSession(3,'Auth.id');
    }
    // /**
    //  * Test logout method
    //  *
    //  * @return void
    //  * @uses \App\Controller\UserController::logout()
    //  */
    public function testLogout(): void
    {

        $this->session([
            'Auth' => [
                    'id' => 1,
                    'DNI_CIF' => '22175395Z',
                    'name' => 'Prueba',
                    'lastname' => 'Prueba Prueba',
                    'username' => 'Pruebatesting',
                    'password' => 'prueba',
                    'email' => 'prueba@gmail.com',
                    'phone' => '639087621',
                    'birth_date' => '1999-12-14',
                    'role' => 'admin',
                    'addres_id' => 1
                
            ]
        ]);   

        $this->enableCsrfToken();
        $this->post('/user/logout');
        $this->assertSession(null, 'Auth.id');
        $this->assertRedirect(['controller' => 'User', 'action' => 'login']);

    }

    // /**
    //  * Test delete method
    //  *
    //  * @return void
    //  * @uses \App\Controller\UserController::delete()
    //  */
    public function testDelete(): void
    {
        $user = TableRegistry::get('User');

        $this->session([
            'Auth' => [
                    'id' => 1,
                    'DNI_CIF' => '22175395Z',
                    'name' => 'Prueba',
                    'lastname' => 'Prueba Prueba',
                    'username' => 'Pruebatesting',
                    'password' => 'prueba',
                    'email' => 'prueba@gmail.com',
                    'phone' => '639087621',
                    'birth_date' => '1999-12-14',
                    'role' => 'admin',
                    'addres_id' => 1
                
            ]
        ]);
        $this->enableCsrfToken();
        $this->post('/user/delete/1');
        $user = TableRegistry::get('User');
        $data = $user->find()->where(['or'=>[['username'=>'Anonimo1'],['username'=>'Anonymous1']]]);
        $this->assertEquals(1,$data->count());

    }


    public function testDeleteUnauthenticatedFail(): void
    {
        $this->enableCsrfToken();
        $this->delete('/user/delete/1');
        $this->assertRedirectContains('/user/login');

    }

}
