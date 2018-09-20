<?php

namespace Dongkaipo\Aliyun;

use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\Regions\Endpoint;
use Aliyun\Core\Regions\EndpointProvider;
use Aliyun\Core\Regions\ProductDomain;
use Sts\Request\V20150401\AssumeRoleRequest;

class Sts
{

    private $regions = [
        "cn-hangzhou", "cn-beijing", "cn-qingdao", "cn-hongkong", "cn-shanghai", "us-west-1", "cn-shenzhen", "ap-southeast-1"
    ];

    const POLICY_ALL = '{"Statement":[{"Action":["oss:*"],"Effect": "Allow","Resource": ["acs:oss:*"]}],"Version": "1"}';
    const POLICY_READ = '{"Statement":[{"Action":["oss:GetObject","oss:ListObjects"],"Effect":"Allow","Resource":["acs:oss:*:*:$BUCKET_NAME/*","acs:oss:*:*:$BUCKET_NAME"]}],"Version":"1"}';
    const POLICY_WRITE = '{"Statement":[{"Action":["oss:GetObject","oss:PutObject","oss:DeleteObject","oss:ListParts","oss:AbortMultipartUpload","oss:ListObjects"],"Effect":"Allow","Resource":["acs:oss:*:*:$BUCKET_NAME/*","acs:oss:*:*:$BUCKET_NAME"]}],"Version":"1"}';

    public function __construct()
    {
        $productDomains = array(
            new ProductDomain("Ecs", "ecs.aliyuncs.com"),
            new ProductDomain("Rds", "rds.aliyuncs.com"),
            new ProductDomain("BatchCompute", "batchCompute.aliyuncs.com"),
            new ProductDomain("Bss", "bss.aliyuncs.com"),
            new ProductDomain("Oms", "oms.aliyuncs.com"),
            new ProductDomain("Slb", "slb.aliyuncs.com"),
            new ProductDomain("Oss", "oss-cn-hangzhou.aliyuncs.com"),
            new ProductDomain("OssAdmin", "oss-admin.aliyuncs.com"),
            new ProductDomain("Sts", "sts.aliyuncs.com"),
            new ProductDomain("Yundun", "yundun-cn-hangzhou.aliyuncs.com"),
            new ProductDomain("Risk", "risk-cn-hangzhou.aliyuncs.com"),
            new ProductDomain("Drds", "drds.aliyuncs.com"),
            new ProductDomain("M-kvstore", "m-kvstore.aliyuncs.com"),
            new ProductDomain("Ram", "ram.aliyuncs.com"),
            new ProductDomain("Cms", "metrics.aliyuncs.com"),
            new ProductDomain("Crm", "crm-cn-hangzhou.aliyuncs.com"),
            new ProductDomain("Ocs", "pop-ocs.aliyuncs.com"),
            new ProductDomain("Ots", "ots-pop.aliyuncs.com"),
            new ProductDomain("Dqs", "dqs.aliyuncs.com"),
            new ProductDomain("Location", "location.aliyuncs.com"),
            new ProductDomain("Ubsms", "ubsms.aliyuncs.com"),
            new ProductDomain("Ubsms-inner", "ubsms-inner.aliyuncs.com")
        );
        $endpoint = new Endpoint("cn-beijing", $this->regions, $productDomains);
        $endpoints = array($endpoint);
        EndpointProvider::setEndpoints($endpoints);

        define('ENABLE_HTTP_PROXY', false);
        define('HTTP_PROXY_IP', '127.0.0.1');
        define('HTTP_PROXY_PORT', '8888');
    }

    /**
     * @param $regionId
     * @param $accessKeyID
     * @param $accessKeySecret
     * @param $policy
     * @param $roleArn
     * @param $clientName
     * @param int $tokenExpire
     * @return false|string
     */
    public function requestToken($regionId, $accessKeyID, $accessKeySecret, $policy, $roleArn, $clientName, $tokenExpire = 3600)
    {
        $iClientProfile = DefaultProfile::getProfile($regionId, $accessKeyID, $accessKeySecret);
        $client = new DefaultAcsClient($iClientProfile);

        $request = new AssumeRoleRequest();
        $request->setRoleSessionName($clientName);
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds($tokenExpire);
        $response = $client->doAction($request);

        $rows = array();
        $body = $response->getBody();
        $content = json_decode($body);
        $rows['status'] = $response->getStatus();
        if ($response->getStatus() == 200) {
            $rows['AccessKeyId'] = $content->Credentials->AccessKeyId;
            $rows['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
            $rows['Expiration'] = $content->Credentials->Expiration;
            $rows['SecurityToken'] = $content->Credentials->SecurityToken;
        } else {
            $rows['AccessKeyId'] = "";
            $rows['AccessKeySecret'] = "";
            $rows['Expiration'] = "";
            $rows['SecurityToken'] = "";
        }
        return json_encode($rows);
    }
}