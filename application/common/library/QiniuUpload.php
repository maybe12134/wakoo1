<?php

namespace app\common\library;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\File;
use Qiniu\Cdn\CdnManager;

trait QiniuUpload
{

    protected function upladFile(File  $file){
        $info = $file->getInfo();
        $domain = config("qiniu.domain");
        $bucket = config("qiniu.bucket");
        $auth = new Auth(config("qiniu.accessKey"), config("qiniu.secretKey"));
        $cdnManager = new CdnManager($auth);
        $key    = $info['name'];
        $filePath =$info['tmp_name'];
        // 生成上传Token
        $token = $auth->uploadToken($bucket,$key,3600);
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传，该方法会判断文件大小.
        list($ret, $err) = $uploadMgr->putFile($token, $info['name'], $filePath);
        if ($err !== null) {
            return ['code' => 0,  'msg' => '上传失败:'.$err->message()];
        } else {
            $urls=array("https://".$domain.$ret['key']);
            list($refreshResult, $refreshErr) = $cdnManager->refreshUrls($urls);
            if ($refreshErr != null) {
                return ['code' => 0,  'msg' => '刷新失败:'.$refreshErr];
            } else {
                //返回图片的完整URL
                return ['code' => 1, 'msg' => '上传完成', 'data' => ($domain . $ret['key']),'refresh' =>  $refreshResult];
            }
        }
    }
}