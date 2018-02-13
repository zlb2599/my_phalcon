<?php

class MyController extends \Phalcon\Mvc\Controller
{
    public function index()
    {
        $a='a';
        $b='b';
        echo $a=$a^$b;
        echo $b=$a^$b;
        echo $a=$a^$b;
    }

    public function sort($data)
    {
        $length = count($data);
        for ($i = 0; $i < $length; $i++) {
            for ($j = $i + 1; $j < $length; $j++) {
                if ($data[$i] > $data[$j]) {
                    $temp     = $data[$i];
                    $data[$i] = $data[$j];
                    $data[$j] = $temp;
                }
            }
        }

        return $data;
    }

}

