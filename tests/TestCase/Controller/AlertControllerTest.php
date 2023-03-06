<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AlertController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\AlertController Test Case
 *
 * @uses \App\Controller\AlertController
 */
class AlertControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Alert',
        'app.User',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\AlertController::index()
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

        $this->get('/alert');
        $this->assertResponseOk();    
    }


    public function testIndexUnauthenticatedFail(): void
    {
        $this->enableCsrfToken();
        $this->get('/alert');
        $this->assertRedirectContains('/user/login');

    }
    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\AlertController::view()
     */
    // public function testView(): void
    // {
    //     $this->markTestIncomplete('Not implemented yet.');
    // }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\AlertController::add()
     */
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
        $this->get('alert/add');

        $this->assertResponseOk();

        $data=[
            'user_id' => 1,
            'country' => 'España',
            'province' => 'Madrid',
            'specie' => 'dog',
            'race' => 'Caniche',
            'creation_date' => '2023-03-03 23:17:48',
            'active' => 'yes',
            'title' => 'Alerta caniche',
        ];
        $this->enableCsrfToken();
        $this->post('alert/add',$data);
        $this->assertRedirect(['controller' => 'Alert', 'action' => 'index']);

        $alerta = TableRegistry::get('Alert');

        $query = $alerta->find()->where(['creation_date' => $data['creation_date']]);

        $this->assertEquals(1,$query->count());
    }

    public function testAddUnauthenticatedFail(): void
    {
        $this->enableCsrfToken();
        $this->get('alert/add');
        $this->assertRedirectContains('/user/login');

    }
    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\AlertController::edit()
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
                    'phone' => '639087621',
                    'birth_date' => '1999-12-14',
                    'role' => 'admin',
                    'addres_id' => 1
                
            ]
        ]);
        $this->get('alert/edit/1');

        $this->assertResponseOk();

        $data=[
            'user_id' => 1,
            'country' => 'España',
            'province' => 'Madrid',
            'specie' => 'cat',
            'race' => 'Siames edit',
            'creation_date' => '2023-03-03 23:17:48',
            'active' => 'no',
            'title' => 'Alerta',
        ];
        $this->enableCsrfToken();
        $this->post('alert/edit/1',$data);
        $this->assertRedirect(['controller' => 'Alert', 'action' => 'index']);

        $alerta = TableRegistry::get('Alert');
        $query = $alerta->find()->where(['race' => $data['race']]);
        $this->assertEquals(1,$query->count());
    }

    public function testEditUnauthenticatedFail(): void
    {
        $this->enableCsrfToken();
        $this->get('alert/edit/1');
        $this->assertRedirectContains('/user/login');

    }

 
    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\AlertController::delete()
     */
    public function testDelete(): void
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
        $this->post('/alert/delete/1');

        $this->assertRedirect(['controller' => 'Alert', 'action' => 'index']);

        $alerta = TableRegistry::get('Alert');
        $data = $alerta->find()->where(['id' => 1]);
        $this->assertEquals(0,$data->count());

    }

    public function testDeleteUnauthenticatedFail(): void
    {
        $this->enableCsrfToken();
        $this->delete('/alert/delete/1');
        $this->assertRedirectContains('/user/login');

    }
}
