<?php
/**
 * Class Authenticator | Authenticator.php
 * @package Faulancer\Auth
 * @author  Florian Knapp <office@florianknapp.de>
 */
namespace Faulancer\Service;

use Faulancer\Controller\Controller;
use Faulancer\ORM\User\Entity as UserEntity;
use Faulancer\Session\SessionManager;

/**
 * Class Authenticator
 */
class Authenticator
{

    /** @var Controller */
    protected $controller;

    /** @var string */
    protected $redirectAfterAuth;

    /**
     * Authenticator constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param UserEntity $userData
     */
    public function registerUser(UserEntity $userData)
    {

    }

    /**
     * @param UserEntity $user
     * @return bool
     */
    public function loginUser(UserEntity $user)
    {
        /** @var ORM $orm */
        $orm = $this->controller->getServiceLocator()->get(ORM::class);

        /** @var UserEntity $userData */
        $userData = $orm
            ->fetch(get_class($user))
            ->where('login', '=', $user->login)
            ->andWhere('password', '=', $user->password)
            ->one();

        if ($userData instanceof UserEntity) {
            $this->saveUserInSession($userData);
            return $this->controller->redirect($this->redirectAfterAuth);
        }

        SessionManager::instance()->setFlashbag('loginError', 'No valid username/password combination found.');
        return $this->redirectToAuthentication();
    }

    /**
     * @return bool
     */
    public function redirectToAuthentication()
    {
        /** @var Config $config */
        $config  = $this->controller->getServiceLocator()->get(Config::class);
        $authUrl = $config->get('auth:authUrl');

        return $this->controller->redirect($authUrl);
    }

    /**
     * @param string $uri
     */
    public function redirectAfterAuthentication(string $uri)
    {
        $this->redirectAfterAuth = $uri;
    }

    /**
     * @param array $roles
     * @return bool
     */
    public function isAuthenticated(array $roles)
    {
        /** @var UserEntity $user */
        $user = $this->getUserFromSession();

        if (!$user instanceof UserEntity) {
            return false;
        }

        foreach ($user->roles as $userRole) {

            if (in_array($userRole->roleName, $roles, true)) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param UserEntity $user
     */
    public function saveUserInSession(UserEntity $user)
    {
        $this->controller->getSessionManager()->set('user', $user->id);
    }

    /**
     * @return UserEntity
     */
    public function getUserFromSession()
    {
        $id = $this->controller->getSessionManager()->get('user');

        /** @var UserEntity $user */
        $user = $this->controller->getDb()->fetch(UserEntity::class, $id);
        return $user;
    }

}