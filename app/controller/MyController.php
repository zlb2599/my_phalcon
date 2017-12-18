<?php

class MyController extends \Phalcon\Mvc\Controller
{

    public function index()
    {
    $m=new McParentMarkets();
    $data=[
        'phone'=>'17777777777'
    ];
    $r=$m->updateRecord(['id'=>'c482fcf0e9684ff381c2491f09540ae3'],$data,'phone');
    dump($r);




    }


    public function index1()
    {


        $id  = getUuid();
        $now = date('Y-m-d H:i:s');

        $emp_id      = '0197c4994dace843e569f8b3e04b9049';
        $merchant_id = 'a7e431fbeb2b805b38180cd2ca8d1d27';
        $platform_id = '47426eb3c0054cfb9556db29a3da4883';

        $m                    = new McParents();
        $m->id                = $id;
        $m->merchant_id       = $merchant_id;
        $m->platform_id       = $platform_id;
        $m->type              = '5';
        $m->channel_id        = '08c239fe42234af190d410feee25a75a';
        $m->classification_id = '0ce0e6eca6dc4663a173ace303dcc91b';
        $m->intention_id      = '127902c31cd245f9a448cd87013f087b';
        $m->is_use            = '2';
        $m->employee_id       = $emp_id;
        $m->is_student        = '1';
        $m->is_usable         = '1';
        $m->is_delete         = '2';
        $m->creator_id        = $emp_id;
        $m->modifier_id       = $emp_id;
        $m->created           = $now;
        $m->modified          = $now;
        $m->data_enter_type   = 1;

        dump($m->create());

        $m    = new McParentMarketInfos();
        $data = [
            'id'           => getUuid(),
            'clue_id'      => $id,
            'student_name' => 'student_9529',
            'sex'          => 1,
            'birthday'     => '2017-12-13 00:00:00',
            'is_usable'    => 1,
            'is_delete'    => 2,
            'creator_id'   => $emp_id,
            'modifier_id'  => $emp_id,
            'created'      => $now,
            'modified'     => $now,
        ];
        dump($m->add($data));


        $m = new McParentMarkets();

        $data = [
            'id'          => getUuid(),
            'clue_id'     => $id,
            'parent_name' => 'parent_9529',
            'relation'    => '2',
            'is_main'     => '2',
            'is_usable'   => '1',
            'listorder'   => '1',
            'is_delete'   => '2',
            'creator_id'  => $emp_id,
            'modifier_id' => $emp_id,
            'created'     => $now,
            'modified'    => $now,
        ];

        dump($m->add($data));

        $m    = new McHistoryReceives();
        $data = [
            'id'          => getUuid(),
            'merchant_id' => $merchant_id,
            'platform_id' => $platform_id,
            'clue_id'     => $id,
            'employee_id' => $emp_id,
            'type'        => 2,
            'is_usable'   => 1,
            'is_delete'   => 1,
            'creator_id'  => $emp_id,
            'modifier_id' => $emp_id,
            'created'     => $now,
            'modified'    => $now,
        ];
        var_dump($m->add($data));

        $m       = new McCommunications();
        $comm_id = getUuid();
        $sure_id = getUuid();
        $data    = [
            'id'          => $comm_id,
            'merchant_id' => $merchant_id,
            'platform_id' => $platform_id,
            'clue_id'     => $id,
            'employee_id' => $emp_id,
            'type'        => 2,
            'content'     => '沟通内容',
            'sure_id'     => $sure_id,
            'is_usable'   => 1,
            'is_delete'   => 1,
            'creator_id'  => $emp_id,
            'modifier_id' => $emp_id,
            'created'     => $now,
            'modified'    => $now,

        ];
        var_dump($m->add($data));

        $m = new McHistoryHears();

        $data = [
            'id'               => $sure_id,
            'merchant_id'      => $merchant_id,
            'platform_id'      => $platform_id,
            'communication_id' => $comm_id,
            'clue_id'          => $id,
            'employee_id'      => $emp_id,
            'is_to_hear'       => 1,
            'is_usable'        => 1,
            'is_delete'        => 1,
            'creator_id'       => $emp_id,
            'modifier_id'      => $emp_id,
            'created'          => $now,
            'modified'         => $now,
        ];
        var_dump($m->add($data));


    }

}

