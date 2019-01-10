<?php

namespace Dongkaipo\Aliyun;

use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\Regions\Endpoint;
use Aliyun\Core\Regions\EndpointProvider;
use Aliyun\Core\Regions\ProductDomain;
use Sts\Request\V20150401\AssumeRoleRequest;
use Exception;

class Sts
{

    private $regions = [
        "cn-hangzhou", "cn-beijing", "cn-qingdao", "cn-hongkong", "cn-shanghai", "us-west-1", "cn-shenzhen", "ap-southeast-1"
    ];

    const POLICY_ALL = '{"Statement":[{"Action":["oss:*"],"Effect": "Allow","Resource": ["acs:oss:*"]}],"Version": "1"}';
    const POLICY_READ = '{"Statement":[{"Action":["oss:Get*","oss:List*"],"Effect":"Allow","Resource":"*"}],"Version":"1"}';

    public function __construct($region = null)
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
        $endpoint = new Endpoint($region ? $region : "cn-beijing", $this->regions, $productDomains);
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
     * @param $roleArn acs:ram::1624292675336058:role/aliyunossadminrole' find at https://ram.console.aliyun.com/#/role/list
     * @param $clientName
     * @param int $tokenExpire
     * @return array
     * @throws Exception
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
        try {
            $response = $client->getAcsResponse($request);
        } catch (Exception $exception) {
            throw $exception;
        }

        $rows = [];
        $rows['AccessKeyId'] = $response->Credentials->AccessKeyId;
        $rows['AccessKeySecret'] = $response->Credentials->AccessKeySecret;
        $rows['Expiration'] = $response->Credentials->Expiration;
        $rows['SecurityToken'] = $response->Credentials->SecurityToken;
        return $rows;
    }
}
