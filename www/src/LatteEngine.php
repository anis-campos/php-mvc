<?php

namespace myMVC;

use Latte\Engine;

class LatteEngine implements ViewEngine
{
    /**
     * @var Engine
     */
    private $latte;
    private $model;
    /**
     * @var string
     */
    private $fileName;


    /**
     * LatteViewEngine constructor.
     */
    public function __construct()
    {
        $this->latte = new Engine;

        $this->latte->setTempDirectory(__DIR__ . DIRECTORY_SEPARATOR . "../build");

    }

    public function setModel($model)
    {
        $this->model = $model ?? array();
    }

    public function setFile(string $fileName)
    {
        $this->fileName = "$fileName.latte";
    }

    public function render()
    {
        $this->latte->render($this->fileName, $this->model);
    }
}

