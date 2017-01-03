<?php

namespace Faulancer\Security;

use Faulancer\Session\SessionManager;

/**
 * Class Csrf
 *
 * @package Faulancer\Security
 * @author Florian Knapp <office@florianknapp.de>
 */
class Csrf
{

    /**
     * Generates a token and save it to session
     *
     * @return string
     */
    public static function getToken()
    {
        $token = bin2hex(random_bytes(32));
        self::saveToSession($token);
        return $token;
    }

    /**
     * @return boolean
     */
    public static function isValid()
    {
        return $_REQUEST['csrf'] === SessionManager::instance()->getFlashbag('csrf');
    }

    private static function saveToSession($token)
    {
        SessionManager::instance()->setFlashbag('csrf', $token);
    }

}