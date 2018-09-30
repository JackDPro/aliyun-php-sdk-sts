# ReadMe

## Install

```
composer reuqire dongkaipo/aliyun-php-sdk-sts
```

## Usage

```
<?php
use Dongkaipo\Aliyun\Sts;
$sts = new Sts();


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

try {
	$role = 'acs:ram::1624292675336058:role/aliyunossadminrole';
	$token = $sts->requestToken('cn-beijing', '<accessKeyID>', '<accessKeySecret>', Sts::POLICY_ALL, $role, 'client_name');
} catch (Exception $exception) {
	var_dump($exception);
}
```

## Aliyun Document

[Aliyun STS Document](https://help.aliyun.com/document_detail/31935.html)
[roleArn](https://ram.console.aliyun.com/#/role/list)
