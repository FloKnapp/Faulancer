<?php
/**
 * Class Exception | Exception.php
 * @package Faulancer\Exception
 * @author  Florian Knapp <office@florianknapp.de>
 */
namespace Faulancer\Exception;

/**
 * Class Exception
 */
class ClientException extends \Exception {

    protected $httpStatus = 400;

}