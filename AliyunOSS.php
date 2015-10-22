<?php

namespace Zhu\AliyunOSS;

require_once __DIR__.'/oss/aliyun.php';

use Aliyun\OSS\OSSClient;
use Aliyun\OSS\Models\OSSOptions;

/**
* \OssService
*/
class AliyunOSS {

    protected $ossClient;
    protected $bucket;

    public function __construct($serverName, $AccessKeyId, $AccessKeySecret)
    {
        $this->ossClient = OSSClient::factory([
            OSSOptions::ENDPOINT => $serverName,
            'AccessKeyId' => $AccessKeyId,
            'AccessKeySecret' => $AccessKeySecret
        ]);
    }

    public static function boot($serverName, $AccessKeyId, $AccessKeySecret)
    {
        return new AliyunOSS($serverName, $AccessKeyId, $AccessKeySecret);
    }

    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
        return $this;
    }

    public function uploadFile($key, $file)
    {
        $handle = fopen($file, 'r');
        $value = $this->ossClient->putObject(array(
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Content' => $handle,
                'ContentLength' => filesize($file)
        ));
        fclose($handle);
        return $value;
    }

    public function uploadContent($key, $content)
    {
        return $this->ossClient->putObject(array(
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Content' => $content,
                'ContentLength' => strlen($content)
        ));
    }

    public function getUrl($key, $expire_time)
    {
        return $this->ossClient->generatePresignedUrl([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Expires' => $expire_time
        ]);
    }

    public function createBucket($bucketName)
    {
        return $this->ossClient->createBucket(['Bucket' => $bucketName]);
    }

    public function getAllObjectKey($bucketName)
    {
        $objectListing = $this->ossClient->listObjects(array(
            'Bucket' => $bucketName,
        ));

        $objectKeys = [];
        foreach ($objectListing->getObjectSummarys() as $objectSummary) {
            $objectKeys[] = $objectSummary->getKey();
        }
        return $objectKeys;
    }
    
    /**
     * 删除阿里云中存储的文件
     *
     * @param string $bucketName 存储容器名称
     * @param string $key 存储key（文件的路径和文件名）
     * @return void
     */
    public function deleteObject($bucketName, $key)
    {
        return $this->ossClient->deleteObject([
            'Bucket'    => $bucketName,
            'Key'       => $key
        ]);
    }

    /**
     * 复制存储在阿里云OSS中的Object
     *
     * @param string $sourceBucke 复制的源Bucket
     * @param string $sourceKey - 复制的的源Object的Key
     * @param string $destBucket - 复制的目的Bucket
     * @param string $destKey - 复制的目的Object的Key
     * @return Models\CopyObjectResult
     */
    public function copyObject($sourceBucke, $sourceKey, $destBucket, $destKey)
    {
        return $this->ossClient->copyObject([
            'SourceBucket'  => $sourceBucke,
            'SourceKey'     => $sourceKey,
            'DestBucket'    => $destBucket,
            'DestKey'       => $destKey
        ]);
    }

    /**
     * 移动存储在阿里云OSS中的Object
     *
     * @param string $sourceBucke 复制的源Bucket
     * @param string $sourceKey - 复制的的源Object的Key
     * @param string $destBucket - 复制的目的Bucket
     * @param string $destKey - 复制的目的Object的Key
     * @return Models\CopyObjectResult
     */
    public function moveObject($sourceBucke, $sourceKey, $destBucket, $destKey)
    {
        $result = $this->ossClient->copyObject([
            'SourceBucket'  => $sourceBucke,
            'SourceKey'     => $sourceKey,
            'DestBucket'    => $destBucket,
            'DestKey'       => $destKey
        ]);

        if (is_object($result) && $result->getETag()) {
            $this->deleteObject($sourceBucke, $sourceKey);
        }
        
        return $result;
    }
}
