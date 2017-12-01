<?php
require __DIR__ . '/autoload.php';
use JPush\Client;

class JPush{


    /**
     * 描述：初始參数
     *
     * @access public
     * ----------------------------------------------------------
     * @param
     * ----------------------------------------------------------
     * return string
     * ----------------------------------------------------------
     * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
     * ----------------------------------------------------------
     * @date ${date}
     * ----------------------------------------------------------
     */

    public function __construct($config) {
        $app_key = $config['app_key'];
        $master_secret = $config['master_secret'];

        if ($app_key) $this->app_key = $app_key;
        if ($master_secret) $this->master_secret = $master_secret;
    }

    /**
     * 向所有设备推送消息
     *
     * @access public
     * ----------------------------------------------------------
     * @param string $message
     * ----------------------------------------------------------
     * @return array
     * ----------------------------------------------------------
     * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
     * ----------------------------------------------------------
     * @date 2017-08-11 20:17
     * ----------------------------------------------------------
     */
    public function sendNotifyAll($message)
    {
        $client = new \JPush\Client($this->app_key, $this->master_secret);
        $push = $client->push();
        $response = $push->setPlatform('all')
                         ->addAllAudience()
                         ->setNotificationAlert($message);

        try {
            $response = $response->send();
        }catch (\JPush\Exceptions\APIConnectionException $e) {
            return $data = array('http_code'=>'error');
        } catch (\JPush\Exceptions\APIRequestException $e) {
            return $data = array('http_code'=>'error');
        }
        return $response;

    }

    /**
     * 特定设备推送相同消息
     *
     * @access public
     * ----------------------------------------------------------
     * @param  array $regid 特定设备的设备标识
     * ----------------------------------------------------------
     * @param  string $message 需要推送的消息
     * ----------------------------------------------------------
     * @return array
     * ----------------------------------------------------------
     * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
     * ----------------------------------------------------------
     * @date 2017-08-11
     * ----------------------------------------------------------
     */
    function sendNotifySpecial($regid,$message)
    {
        $client = new \JPush\Client($this->app_key, $this->master_secret);
        $push = $client->push();
        $response = $push->setPlatform('all')
                       ->addRegistrationId($regid)
                       ->setNotificationAlert($message);

        try {
            $response = $response->send();
        }catch (\JPush\Exceptions\APIConnectionException $e) {
            return $data = array('http_code'=>'error');
        } catch (\JPush\Exceptions\APIRequestException $e) {
            return $data = array('http_code'=>'error');
        }
        return $response;
    }

    /**
     * 所有设备推送，多条信息
     *
     * @access public
     * ----------------------------------------------------------
     * @param  array $data
     * ----------------------------------------------------------
     * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
     * ----------------------------------------------------------
     * @date 2017-08-11
     * ----------------------------------------------------------
     */

    public function sendAllDevice($data)
    {
        $base64=base64_encode("$this->app_key:$this->master_secret");
        $header=array("Authorization:Basic $base64","Content-Type:application/json");

        $device  = getArrVal($data, 'device','all');//设备 ios Android WinPhone
        $title   = getArrVal($data, 'title','');//推送标题
        $content = getArrVal($data, 'content','');//推送内容
        $alert   = getArrVal($data, 'alert','');//表示通知内容
        $m_time  = getArrVal($data, 'm_time','86400');
        $alias   = getArrVal($data, 'alias',array());//数组多个用户UID
        $extras  = getArrVal($data, 'extras',array());//内容数组形式


        $client = new \JPush\Client($this->app_key, $this->master_secret);
        $push = $client->push();
        $ios_notification = array(
            'sound'             => '',//表示通知提示声音，默认填充为空字符串
            'badge'             => '+1',//应用角标 为 0 表示清除，支持 '+1','-1'
            'content-available' => true,//表示推送唤醒
            'mutable-content'   => true,
            'category'          => '',
            'extras'            => $extras,
        );

        $android_notification = array(
            'title'     => $title,//表示通知标题
            'build_id'  => 2,//表示通知栏样式 ID
            //'priority'  => 0,//表示通知栏展示优先级，默认为 0
            //'alert_type' => 0,//表示通知栏展示优先级，默认为 0
            'style'     => 1,
            'extras' => $extras,
        );

        $message = array(
            'title' => $title,
            'content_type' => 'text',
            'extras' => $extras,
        );
        $options = array(
            //'sendno' => time(),//表示推送序号
            'time_to_live' => $m_time,//表示离线消息保留时长(秒)
            //'override_msg_id' => 100,//表示要覆盖的消息ID
            //'big_push_duration' => 10,
            'apns_production' =>false  //true 表示推送生产环境，False 表示要推送开发环境
        );
        $alert_ios     = ['body'=>$alert,'title'=>$title];//ios消息体
        $alert_android = $alert;
        $response = $push
            ->setPlatform($device)
            ->addAlias($alias)
            ->iosNotification($alert_ios, $ios_notification)
            ->androidNotification($alert_android, $android_notification)
            ->message($content, $message)
            ->options($options);
        try {
            $response = $response->send();
        }catch (\JPush\Exceptions\APIConnectionException $e) {
            return $data = array('http_code'=>'error');
        } catch (\JPush\Exceptions\APIRequestException $e) {
            //print_r($response);die;
            return $data = array('http_code'=>'error');
        }
        return $response;
    }

    /**
     * 各类统计数据
     *
     * @access public
     * ----------------------------------------------------------
     * @param  array $msgIds 推送消息返回的msg_id列表
     * ----------------------------------------------------------
     * @return JSON
     * ----------------------------------------------------------
     * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
     * ----------------------------------------------------------
     * @date 2017-08-11
     * ----------------------------------------------------------
     */
    function reportNotify($msgIds){
        $client = new \JPush\Client($this->app_key, $this->master_secret);
        $response = $client->report()->getReceived($msgIds);
        return $response;
    }

}