<?php
namespace Xety\Cake3CookieAuth\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component\CookieComponent;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;

class CookieAuthenticate extends BaseAuthenticate
{

    /**
     * Constructor.
     *
     * @param \Cake\Controller\ComponentRegistry $registry The Component registry used on this request.
     * @param array $config Array of config to use.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $this->_registry = $registry;
        $this->config([
            'cookie' => [
                'name' => 'CookieAuth'
            ]
        ]);
        $this->config($config);
    }

    /**
     * Authenticate a user based on the cookies information.
     *
     * @param \Cake\Network\Request  $request  The request instance.
     * @param \Cake\Network\Response $response The response instance.
     *
     * @return mixed
     *
     * @throws \RuntimeException When the CookieComponent is not loaded.
     */
    public function authenticate(Request $request, Response $response)
    {
        if (!isset($this->_registry->Cookie) || !$this->_registry->Cookie instanceof CookieComponent) {
            throw new \RuntimeException('You need to load the CookieComponent.');
        }

        $cookies = $this->_registry->Cookie->read($this->_config['cookie']['name']);
        if (empty($cookies)) {
            return false;
        }

        extract($this->_config['fields']);
        if (empty($cookies[$username]) || empty($cookies[$password])) {
            return false;
        }

        $user = $this->_findUser($cookies[$username], $cookies[$password]);
        if ($user) {
            return $user;
        }

        return false;
    }

    /**
     * Returns a list of all events that this authenticate class will listen to.
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Auth.logout' => 'logout'
        ];
    }

    /**
     * Delete cookies when an user logout.
     *
     * @param \Cake\Event\Event  $event The logout Event.
     * @param array $user The user about to be logged out.
     *
     * @return void
     */
    public function logout(Event $event, array $user)
    {
        $this->_registry->Cookie->delete($this->_config['cookie']['name']);
    }
}
