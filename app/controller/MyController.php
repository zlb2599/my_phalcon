<?php

class MyController extends \Phalcon\Mvc\Controller
{


    public function onConstruct()
    {

        echo '<h1>initialize!</h1>';
    }

    public function index()
    {
        $m = new Parts();

    }

}

