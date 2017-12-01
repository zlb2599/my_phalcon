<?php
/**
 * 推送配置文件
 * 机构给家长端退送用MERCHANT_PUSH
 * 家长端给机构端推送用PUSH
 *  @copyright Copyright 2012-2016, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link http://api.baonahao.com api(tm) Project
 * @date 2017/10/20 9:53
 * @author xuxiongzi <xuxiongzi@xiaohe.com
 */
return [

    //极光 项目ID 家长端给机构端推送用
    'PUSH'=>[
        //佳一
        '4ecb50de52b44be8a77991d3baa442a9'=>[
            'app_key'           =>'e6713e58455bb725beb80da8',
            'master_secret'     =>'5c48feaf85d30dd7048b1814',

        ],
        //杰睿
        '387c84e4e765db6e43651888abfb9c7a'=>[
            'app_key'           =>'3c911c9ce62d3e2363038fb6',
            'master_secret'     =>'a2e2512445defe69f87e8744',
        ],
        //爱校
        'aixiao'=>[
            'app_key'       =>'174343e32362bc2d26a9a7d7',
            'master_secret' =>'83e8c3b4d78dc641957ee947'
        ],
    ],

    //极光 商家ID 机构给家长端推送用
    'MERCHANT_PUSH'=>[
        //佳一测试
        '9f331628edd941009439f6fc25f8ef0f'=>[
            'app_key'           =>'e6713e58455bb725beb80da8',
            'master_secret'     =>'5c48feaf85d30dd7048b1814',
        ],
        //佳一正式
        '0647e5574fca4a4dbb15603ba190d9a7'=>[
            'app_key'           =>'e6713e58455bb725beb80da8',
            'master_secret'     =>'5c48feaf85d30dd7048b1814',
        ],
        //杰睿测试
        '97ff3f092ee3983194fd7e7924280f24'=>[
            'app_key'           =>'3c911c9ce62d3e2363038fb6',
            'master_secret'     =>'a2e2512445defe69f87e8744',
        ],
        //杰睿测试
        'dd6474f78cf35b7dda442c7fc95a3057'=>[
            'app_key'           =>'3c911c9ce62d3e2363038fb6',
            'master_secret'     =>'a2e2512445defe69f87e8744',
        ],
        //杰睿正式
        '06f3ee4037d0376447cd4fc3893cb5d1'=>[
            'app_key'           =>'3c911c9ce62d3e2363038fb6',
            'master_secret'     =>'a2e2512445defe69f87e8744',
        ],
        'a7e431fbeb2b805b38180cd2ca8d1d27'=>[
            'app_key'           =>'e6713e58455bb725beb80da8',
            'master_secret'     =>'5c48feaf85d30dd7048b1814',
        ],
    ]
];