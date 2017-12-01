<?php

class MyController extends \Phalcon\Mvc\Controller
{


    public function initialize()
    {

        echo '<h1>initialize!</h1>';
    }

    public function index()
    {

        $m = new BcHomeworkBases();
        $r = $m->find()->toArray();

        foreach ($r as $v) {
            $data['id']          = $v['id'];
            $data['merchant_id'] = '2311';
            $m->save($data);
        }
        dump($data);

    }

}

