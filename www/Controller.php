<?php


abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ViewEngine
     */
    private $viewEngine;


    public function invoke($request, $viewEngine)
    {
        $this->request = $request;
        $this->viewEngine = $viewEngine;
        call_user_func_array([$this, $this->request->getAction()], $this->request->getParams());
    }

    /**
     * @param mixed $model
     * @param string $fileName
     */
    protected function view($model = null, $fileName = null)
    {
        $fileName = $fileName ?? "Views/{$this->request->getController()}/{$this->request->getAction()}";
        $this->viewEngine->setModel($model);
        $this->viewEngine->setFile($fileName);

        return $this->viewEngine->render();

    }

    /**
     * Returns JSON data to the client
     * @param $model mixed
     */
    protected function json($model)
    {
        header('Content-Type: application/json');
        echo json_encode($model);
    }

}