<?php
namespace Xety\Cake3CookieAuth\Test\TestCase\Auth;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Network\Request;
use Cake\Network\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use Xety\Cake3CookieAuth\Auth\CookieAuthenticate;

class CookieAuthenticateTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = ['plugin.Xety\Cake3CookieAuth.users'];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->request = new Request('posts/index');
        Router::setRequestInfo($this->request);
        $this->response = $this->getMock('Cake\Network\Response');

        Security::salt('Xety-Cake3CookieAuth_Xety-Cake3CookieAuth');
        $this->registry = new ComponentRegistry(new Controller($this->request, $this->response));
        $this->registry->load('Cookie');
        $this->registry->load('Auth');
        $this->auth = new CookieAuthenticate($this->registry);

        $password = password_hash('password', PASSWORD_DEFAULT);
        $Users = TableRegistry::get('Users');
        $Users->updateAll(['password' => $password], []);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->registry->Cookie->delete('CookieAuth');
    }

    /**
     * test authenticate
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $expected = [
            'id' => 1,
            'username' => 'Mariano'
        ];

        $result = $this->auth->authenticate($this->request, $this->response);
        $this->assertFalse($result);

        $this->registry->Cookie->write(
            'CookieAuth',
            ['username' => 'Mariano', 'password' => 'password']
        );
        $result = $this->auth->authenticate($this->request, $this->response);
        $this->assertEquals($expected, $result);
    }

    /**
     * test authenticateNoUsername
     *
     * @return void
     */
    public function testAuthenticateNoUsername()
    {
        $this->registry->Cookie->write(
            'CookieAuth',
            ['username' => '', 'password' => 'password']
        );

        $result = $this->auth->authenticate($this->request, $this->response);
        $this->assertFalse($result);
    }

    /**
     * test authenticateFail
     *
     * @return void
     */
    public function testAuthenticateFail()
    {
        $this->registry->Cookie->write(
            'CookieAuth',
            ['username' => 'Mariano', 'password' => 'passwordfail']
        );
        $result = $this->auth->authenticate($this->request, $this->response);
        $this->assertFalse($result);
    }

    /**
     * test authenticateNoCookieComponent
     *
     * @return void
     */
    public function testAuthenticateNoCookieComponent()
    {
        $this->_registry = new ComponentRegistry(new Controller($this->request, $this->response));
        $this->_registry->load('Cookie');
        $this->_auth = new CookieAuthenticate($this->_registry);

        $this->assertTrue($this->_registry->has('Cookie'));

        $this->_registry->unload('Cookie');

        $this->setExpectedException('RuntimeException');
        $this->_auth->authenticate($this->request, $this->response);
    }

    /**
     * test logout
     *
     * @return void
     */
    public function testLogout()
    {
        $this->registry->Cookie->write(
            'CookieAuth',
            ['username' => 'Mariano', 'password' => 'password']
        );
        $event = new Event('Auth.logout');
        $user = $this->auth->authenticate($this->request, $this->response);

        $resultTrue = $this->registry->Cookie->check('CookieAuth');
        $this->assertTrue($resultTrue);

        $this->auth->logout($event, $user);
        $resultFalse = $this->registry->Cookie->check('CookieAuth');
        $this->assertFalse($resultFalse);
    }
}
