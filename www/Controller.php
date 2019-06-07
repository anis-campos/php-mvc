<?php


abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
    }


    public function invoke($request)
    {
        $this->request = $request;
        call_user_func_array([$this, $this->request->getAction()], $this->request->getParams());
    }

    /**
     * Returns JSON data to the client
     * @param $model mixed
     */
    protected static function json($model)
    {
        header('Content-Type: application/json');
        echo json_encode($model);
    }

}