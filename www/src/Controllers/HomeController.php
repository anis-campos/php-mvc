<?php
namespace myMVC;

class HomeController extends Controller
{

    function index()
    {
        return parent::view();
    }


    function test()
    {
        parent::json(["test" => "Hello World"]);
    }
}