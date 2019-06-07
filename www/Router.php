<?php

class Router
{
    /**
     * @param $url string
     * @return Request
     */
    public static function parse($url)
    {
        $url = trim($url);
        if ($url == "/") {
            #Route par defaut du
            return new Request($url, "Home", "index", []);
        } else {
            $explode_url = explode('/', $url);
            $explode_url = array_slice($explode_url, 2);
            $controller = $explode_url[0];
            $action = $explode_url[1];
            $params = array_slice($explode_url, 2);

            return new Request($url, $controller, $action, $params);
        }
    }
}