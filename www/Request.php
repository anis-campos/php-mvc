<?php

class Request
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $controller;
    /**
     * @var string
     */
    private $action;
    /**
     * @var object[]
     */
    private $params;


    /**
     * @param $url
     * @param $controller
     * @param $action
     * @param $params
     */
    public function __construct($url, $controller, $action, $params)
    {
        $this->url = $url;
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return object[]
     */
    public function getParams()
    {
        return $this->params;
    }


}

