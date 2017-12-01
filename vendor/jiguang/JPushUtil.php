<?php

/**
 * 描述：极光推送工具类
 *
 * @copyright Copyright 2012-2016, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link http://api.baonahao.com api(tm) Project
 * @date 2017/11/3 11:13
 * @author xuxiongzi <xuxiongzi@xiaohe.com>
 */
class JPushUtil
{
    /**
     * 描述：推送
     * @param
     * @return array
     * @author xuxiongzi <xuxiongzi@xiaohe.com>
     */
    public function push($data) {
        //参数
        $title       = getArrVal($data , 'title');
        $content     = getArrVal($data , 'content');
        $alert       = getArrVal($data , 'alert');
        $merchant_id = $all_powerful_id = getArrVal($data , 'merchant_id');
        $push_type   = getArrVal($data , 'push_type'); // 推送类型(1:课程 2:考勤 3:课次评价)
        $app_type    = getArrVal($data , 'app_type');  // 应用类型(1:家长端 2:机构端)
        $alias       = getArrVal($data , 'alias');     // 准备发送的人员member_id 或parent_id
        $extras_jg   = getArrVal($data , 'extras_jiguang' ,'');
        $extras      = getArrVal($data , 'extras' ,'');
        $created     = date('Y-m-d H:i:s', time());
        $creator_id  = getArrVal($data , 'creator_id'); // 会员或家长ID
        $push        =  $data['push'];

        //商家ID极光消息配置
        $config = [];
        $config = getConfig("MERCHANT_PUSH","pushConfig");
        $config = getArrVal($config,$merchant_id);
        $config = json_decode(json_encode($config),true);
        if (empty($config)) return;

        $data = [
            'title'  => $title,
            'content'=> $content,
            'alert'  => $alert,
            'alias'  => $alias,//$alias,//准备发送的人员
            'extras' => $extras_jg,
        ];
        $sendall = $push->sendAllDevice($data);


        //记录消息内容
        $push_msg_log_model = new PushMessageLogModel();
        $status = $sendall['http_code']==200 ? 1 : 2;

        $apml['app_type']        = $app_type;
        $apml['push_type']       = $push_type;
        $apml['push_title']      = $title;
        $apml['push_content']    = $content;
        $apml['push_alert']      = $alert;
        $apml['push_extras']     = $extras;
        $apml['user_id']         = $alias[0];
        $apml['status']          = $status;
        $apml['created']         = $created;
        $apml['creator_id']      = $creator_id;
        $apml['app_key']         = $config['app_key'];
        $apml['master_secret']   = $config['master_secret'];
        $apml['creator_id']      = $creator_id;
        $apml['all_powerful_id'] = $all_powerful_id;

        $push_msg_log_model->add($apml);
    }

}