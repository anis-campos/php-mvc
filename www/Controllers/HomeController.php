<?php

class HomeController extends Controller
{

    function index()
    {
        self::json([ "test"=>"Hello World"]);
    }
}