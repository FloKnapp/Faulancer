<?php
/**
 * Class Request
 *
 * @package Faulancer\Http
 * @author Florian Knapp <office@florianknapp.de>
 */
namespace Faulancer\Http;

/**
 * Class Request
 */
class Request extends AbstractHttp
{

    /**
     * The current path string
     * @var string
     */
    protected $uri = '';

    /**
     * The current method
     * @var string
     */
    protected $method = '';

    /**
     * The current query string
     * @var string
     */
    protected $query = '';

    /**
     * Set attributes automatically
     *
     * @return self
     */
    public function createFromHeaders()
    {
        $uri = $_SERVER['REQUEST_URI'];

        if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
            $uri = explode('?', $_SERVER['REQUEST_URI']);
            $this->setQuery($uri[1]);
            $uri = $uri[0];
        }

        $this->setUri($uri);
        $this->setMethod($_SERVER['REQUEST_METHOD']);

        return $this;
    }

    /**
     * Set uri path
     *
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get uri path
     *
     * @return string
     */
    public function getUri() :string
    {
        return $this->uri;
    }

    /**
     * Set method
     *
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod() :string
    {
        return empty($this->method) ? $_SERVER['REQUEST_METHOD'] : $this->method;
    }

    /**
     * Set query string
     *
     * @param string $query
     */
    public function setQuery(string $query)
    {
        $this->query = $query;
    }

    /**
     * Get query string
     *
     * @return string
     */
    public function getQuery() :string
    {
        return $this->query;
    }

    /**
     * Determine if it's a post request
     *
     * @return boolean
     */
    public function isPost() :bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Determine if it's a get request
     *
     * @return boolean
     */
    public function isGet() :bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Return the post data
     *
     * @return array
     */
    public function getPostData() :array
    {
        return empty($_POST) ? [] : $_POST;
    }

    public function getRequestContent()
    {
        return file_get_contents('php://input');
    }

}