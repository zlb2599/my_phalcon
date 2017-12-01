<?php
/**
 * 公共方法
 *
 * @copyright Copyright 2012-2017, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link http://api.baonahao.com api(tm) Project
 * @author gaoxiang <gaoxiang@xiaohe.com>
 */
function dataReturn($status = true, $msg = 'API_COMM_001', $result = null)
{
    $tip = require ROOT.'conf/tip.php';

    $result = array(
        'status'        => $status, //接口操作状态
        'type'          => 'json', //数据交互类型
        'code'          => $msg, //接口错误码
        'code_msg'      => $tip[$msg][0], //操作错误信息
        'code_user_msg' => $tip[$msg][1], //用户提示信息
        'result'        => empty($result)?'':$result, //接口返回结果集
    );

    // 返回结果放到redis,防止重复请求
    $redis = connRedis();
    $redis->select(5);

    $cache_key           = md5(json_encode($_REQUEST));
    $cache_data          = $result;
    $cache_data['cache'] = 'redis';
    $redis->setex($cache_key, 1, json_encode($cache_data));

    DLOG('return:'.json_encode($result));
    exit(json_encode($result));
}

/*
 * 获取配置文件
 *
 * @param string $keys 获取配置key
 * @param string $file_name 获取配置文件名称 列：conf.php 传递conf 不带后缀名
 *
 * @return 返回获取内容
 * */
function getConfig($keys = '', $file_name = '')
{
    if ($keys == '') {
        return '';
    }

    if ($file_name == '') {
        $path = ROOT.'conf/conf.php';
    } else {
        $path = ROOT.'conf/'.$file_name.'.php';
    }

    $conf = new Phalcon\Config\Adapter\Php($path);


    $key_arr = explode(".", $keys);
    if (count($key_arr) > 1) {
        $value = $conf;
        for ($i = 0; $i < count($key_arr); $i++) {
            $tmp = getArrVal($value, $key_arr[$i], '');
            if ($tmp == '') {
                break;
            } else {
                $value = $tmp;
            }
        }

        $result = $value;
    } else {
        $result = getArrVal($conf, $key_arr[0], '');
    }

    if (is_object($result)) {
        return (array)$result;
    } else {
        return $result;
    }
}

/*
 * 与新版支付宝sdk方法重名
 * */
function C($key = '', $file_name = '')
{
    return getConfig($key, $file_name);
}

/**
 * 获取数组里的值
 *
 * @param  array $arr 数组
 * @param  mixed $key 键名
 * @param  mixed $default 默认值
 * @return mixed
 */
function getArrVal($arr, $key, $default = '')
{
    if (!isset($arr[$key])) {
        return $default;
    }
    $data = $arr[$key];
    switch (strtolower(getType($data))) {
        case 'boolean':
        case 'null':
        case 'object':
        case 'resource':
            return $data;
            break;
        case 'array':
            return (empty($data)?$default:$data);
            break;
        default:
            $data = trim($data);

            return (strlen($data)?addslashes($data):$default);
            break;
    }

    return $default;
}

/**
 * 记录日志
 *
 * @param mixed $log_content 要调试的数据
 * @param string $log_level 日志级别(ERROR:执行错误日志 WARN:警告日志 INFO:交互信息日志 DEBUG:调试日志)
 * @param string $file_name 记录日志文件
 *
 * @return array
 */
function DLOG($log_content = '', $log_level = 'INFO', $file_name = 'debug.log')
{
    if (is_array($log_content) || is_object($log_content)) {
        $log_content = json_encode($log_content);
    }

    $log_level_arr = ['ERROR', 'WARN', 'INFO', 'DEBUG'];
    if ($log_content == '') {
        return;
    }
    if (!in_array($log_level, $log_level_arr)) {
        return;
    }

    $log_path       = ROOT.'tmp'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR;
    $log_path       .= date('m').DIRECTORY_SEPARATOR.date('d').DIRECTORY_SEPARATOR;
    $time           = sprintf("%8s.%03d", date('H:i:s'), floor(microtime() * 1000)); //请求时间精确到毫秒
    $ip             = sprintf("%15s", get_client_ip(0, true)); //获取客户端IP地址
    $request_uri    = $_SERVER['REQUEST_URI']; //请求uri
    $content_prefix = "[ ".$time." ".trim($ip)." ".$log_level." ".$request_uri." ] "; //日志前缀
    $content_suffix = "[ ".getmypid()." ]"; //日志后缀
    $file_path      = sprintf('%s%s', $log_path, $file_name); //日志写入地址

    if (!file_exists(dirname($file_path))) {
        mkdir(dirname($file_path), 0755, true);
    }

    $fp = fopen($file_path, 'a+');
    fwrite($fp, $content_prefix.$log_content.$content_suffix."\n");
    fclose($fp);

    return;
}

/**
 * 获取客户端IP地址
 *
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 *
 * @return string
 */
function get_client_ip($type = 0, $adv = false)
{
    $type = $type?1:0;
    static $ip = NULL;

    if ($ip !== NULL) {
        return $ip[$type];
    }

    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);

            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long?array($ip, $long):array('0.0.0.0', 0);

    return $ip[$type];
}

/**
 * 返回UUID
 *
 * @param void
 *
 * @return string
 */
function getUuid()
{
    $random = new Phalcon\Security\Random();

    return str_replace('-', '', $random->uuid());
}

/*
 * 数组转字符串(支持一维、二维 数组)
 *
 * @param array $data 转换数组
 * @param string $key 转换的key
 *
 * @return string $str 转换结果
 * */
function arrayToStr($data, $key = '')
{
    if ($key == '') {
        $str = "'".implode("','", $data)."'";
    } else {
        $str         = '';
        $columns_arr = array_column($data, $key);
        $str         = "'".implode("','", $columns_arr)."'";
    }

    return $str;
}

/*
 * 添加缓存
 *
 * @param string $key 缓存key
 * @param string $value 缓存value
 *
 * @return void
 * */
function addCache($key = '', $value = '')
{
    if ($key == '' || $value == '') {
        return;
    }

    $cacheKey = $key.'.cache';

    $frontCache = new Phalcon\Cache\Frontend\Data([
        "lifetime" => 3600
    ]);
    $cache      = new Phalcon\Cache\Backend\File(
        $frontCache,
        [
            "cacheDir" => ROOT."tmp/cache/"
        ]
    );

    // Store it in the cache
    $cache->save($cacheKey, $value);
}

/*
 * 获取缓存
 *
 * @param string $key 缓存key
 *
 * @return cache obj
 * */
function getCache($key = '')
{
    if ($key == '') {
        return;
    }

    $cacheKey = $key.'.cache';

    $frontCache = new Phalcon\Cache\Frontend\Data([
        "lifetime" => 3600
    ]);
    $cache      = new Phalcon\Cache\Backend\File(
        $frontCache,
        [
            "cacheDir" => ROOT."tmp/cache/"
        ]
    );

    $value = $cache->get($cacheKey);

    if ($value === null) {
        return '';
    }

    return $value;
}

/*
 * 删除缓存
 *
 * @param string $key 缓存key
 *
 * @return bool
 * */
function delCache($key = '')
{
    if ($key == '') {
        return false;
    }

    $cacheKey = $key.'.cache';

    $frontCache = new Phalcon\Cache\Frontend\Data([
        "lifetime" => 3600
    ]);
    $cache      = new Phalcon\Cache\Backend\File(
        $frontCache,
        [
            "cacheDir" => ROOT."tmp/cache/"
        ]
    );

    if ($cache->exists($cacheKey)) {
        $cache->delete($cacheKey);
    }

    return true;
}

/*
 * 连接redis
 *
 * @param void
 *
 * @return conn
 * */
function connRedis()
{
    $config     = new Phalcon\Config\Adapter\Ini(ROOT.'conf'.DIRECTORY_SEPARATOR.'db.ini');
    $redis_conf = $config['redis'];
    $redis      = new Redis();

    $redis->connect($redis_conf['host'], $redis_conf['port'], $redis_conf['timeout']);
    $redis->auth($redis_conf['password']);

    return $redis;
}

/**
 * 名称首字母排序
 *
 * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
 * @date 2017-09-25 14:29
 */
function getFirstCharter($str)
{
    if (empty($str)) {
        return '';
    }
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) {
        return strtoupper($str{0});
    }
    $s1  = iconv('UTF-8', 'gb2312', $str);
    $s2  = iconv('gb2312', 'UTF-8', $s1);
    $s   = $s2 == $str?$s1:$str;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848 || $asc == -6704) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';

    return "#";
}


/*
 * 数据变更为字符串
 *
 * @param array $data 转换数据
 * @param string $append_symbol 追加符类型
 *
 * @return bool
 *
 * */
function array2string($data, $append_symbol = '1')
{
    //追加符类型
    $append_symbol_type = array(1 => "'");
    //分割符
    $separator     = getArrVal($data, 'separator', "','");
    $append_symbol = !empty($append_symbol_type[$append_symbol])?$append_symbol_type[$append_symbol]:$append_symbol;

    if (empty($data)) {
        return '';
    }
    $data = array_unique((array)$data);

    return $append_symbol.implode($separator, $data).$append_symbol;
}

/**
 * 描述：三元运算获取值适用于获取数组中的字符串
 * @param
 * @return array
 * @author xuxiongzi <xuxiongzi@xiaohe.com>
 */
function getValue($arr, $key, $default = "")
{
    return empty($arr[$key])?$default:$arr[$key];
}

/**
 * 拼接图片路径
 * @param $data
 * @param $field
 * @return mixed
 * @author zhanglibo <zhanglibo@xiaohe.com>
 */
function imgLink(&$data, $field)
{
    if (empty($data) || empty($field)) {
        return $data;
    }
    //域名
    $domain = getConfig('IMG');

    foreach ($field as $conf_key => $conf_val) {
        foreach ($data as $data_key => $data_val) {
            if (is_array($data_val)) {
                if (!empty($data[$data_key][$conf_val])) {
                    $data[$data_key][$conf_val] = $domain.$data[$data_key][$conf_val];
                } else {
                    $data[$data_key][$conf_val] = '';
                }
            } else {
                $value           = !empty($data[$conf_key])?$data[$conf_key]:'';
                $data[$conf_val] = $domain.$value;
            }
        }
    }
}

function replace_unicode_escape_sequence($match)
{
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

/**
 * 将Unicode编码转换成可以浏览的utf-8编码
 * @param $str
 * @return mixed
 * @author zhanglibo <zhanglibo@xiaohe.com>
 */
function unicode_decode($str)
{
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
}

function is_android()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

    return strpos($agent, 'android')?true:false;
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}
