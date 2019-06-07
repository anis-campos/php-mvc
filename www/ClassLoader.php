<?php

class ClassLoader
{


    /**
     * @var string[]
     */
    private $locations;

    /**
     * ClassLoader constructor.
     * @param string[] $paths
     */
    public function __construct(array $paths = ["Model/", "View/"])
    {
        $this->locations = $paths;
    }

    private function load($class)
    {
        foreach ($this->locations as $location) {
            if (file_exists($path = "{$location}/{$class}.php")) {
                require_once $path;
                return true;
            }
        }
    }

    public function register()
    {
        spl_autoload_register([$this, 'load']);
    }

    /**
     * @param $location string
     */
    public function addLocation($location)
    {
        array_push($this->locations, $location);
    }
}