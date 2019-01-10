<?php

namespace Dongkaipo\Aliyun;

use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\Regions\Endpoint;
use Aliyun\Core\Regions\EndpointProvider;
use Aliyun\Core\Regions\ProductDomain;
use Sts\Request\V20150401\AssumeRoleRequest;
use Exception;
use \Datetime;

class Client
{
    private $accessKeyId = '';
    private $accessKeySecret = '';

    public function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }

    function __construct($accessKeyId, $accessKeySecret)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }

    public function sign($region, $bucket, $dir, $expire = 30, $callbackUrl = null, $limitSize = 1048576000)
    {
        # 拼 oss 上传 url
        $host = 'https://' . $bucket . '.oss-' . $region . '.aliyuncs.com';

        #  如果有回调地址配置回调
        if ($callbackUrl) {
            $callback_param = array('callbackUrl' => $callbackUrl,
                'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
                'callbackBodyType' => "application/x-www-form-urlencoded");
            $callback_string = json_encode($callback_param);

            $base64_callback_body = base64_encode($callback_string);
        }

        # 设置有效时间
        $now = time();
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);

        # 设置文件大小限制
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition;

        # 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;

        # 进行签名
        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->accessKeySecret, true));

        # 拼凑结果
        $response = array();
        $response['accessid'] = $this->accessKeyId;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        if ($callbackUrl) {
            $response['callback'] = $base64_callback_body;
        }
        $response['dir'] = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
        return $response;
    }


}
