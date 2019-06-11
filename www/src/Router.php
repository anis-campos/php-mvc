<?php
namespace myMVC;

class Router
{
    /**
     * @var string
     */
    private $url;

    /**
     * Router constructor.
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = trim($url);;
    }

    function redirect($permanent = false)
    {
        if (headers_sent() === false) {
            header('Location: ' . $this->url, true, ($permanent === true) ? 301 : 302);
        }

        exit;
    }

    function error()
    {
        http_response_code(404);
        #include 'error/404.php';
        exit;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if ($this->url == "/") {
            #Route par defaut du
            return new Request($this->url, "Home", "index", []);
        } else if (preg_match("@^\.\./public/@", $this->url)) {
            # Fix relative url coming from view items
            $this->url = preg_replace("@^(\.\./)+public/@", "/public/", $this->url);
            $this->redirect();
        } else {
            $explode_url = explode('/', $this->url);
            $explode_url = array_slice($explode_url, 2);
            if (empty($explode_url))
                $this->error();
            $controller = $explode_url[0];
            $action = $explode_url[1];
            $params = array_slice($explode_url, 2);


            return new Request($this->url, $controller, $action, $params);
        }
    }
}