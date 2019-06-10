<?php


class LatteEngine implements ViewEngine
{
    /**
     * @var Latte\Engine
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
        $this->latte = new Latte\Engine;

        $this->latte->setTempDirectory(__DIR__ . DIRECTORY_SEPARATOR . 'build');

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
