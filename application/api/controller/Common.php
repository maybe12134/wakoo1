<?php

namespace app\api\controller;
Vendor('qiniu.php-sdk.autoload');
use app\common\controller\Api;
use app\common\exception\UploadException;
use app\common\library\QiniuUpload;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use app\common\model\Area;
use app\common\model\Version;
use fast\Random;
use think\Config;
use think\Db;
use think\Hook;
use app\common\controller\Qiniu;
use app\common\library\Upload;

/**
 * 公共接口
 */
class Common extends Api
{
    use QiniuUpload;
    protected $noNeedLogin = ['init','upload'];
    protected $noNeedRight = '*';

    /**
     * 加载初始化
     *
     * @param string $version 版本号
     * @param string $lng     经度
     * @param string $lat     纬度
     */
    public function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');

            //配置信息
            $upload = Config::get('upload');
            //如果非服务端中转模式需要修改为中转
            if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
                //临时修改上传模式为服务端中转
                set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

                $upload = \app\common\model\Config::upload();
                // 上传信息配置后
                Hook::listen("upload_config_init", $upload);

                $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
            }

            $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
            $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => $upload,
                'coverdata'   => Config::get("cover"),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }







    public function upload()
    {
        $header=get_all_header();
        //验证token
        if(!isset($header["token"]) || ($header["token"]=="")){
            return json(["code"=>500,"msg"=>"token不存在1"]);
        }

        Config::set('default_return_type', 'json');
        //必须设定cdnurl为空,否则cdnurl函数计算错误
        Config::set('upload.cdnurl', '');
        $chunkid = $this->request->post("chunkid");
        $file = $this->request->file('file');
        if ($chunkid) {
            if (!Config::get('upload.chunking')) {
                $this->error(__('Chunk file disabled'));
            }
            $action = $this->request->post("action");
            $chunkindex = $this->request->post("chunkindex/d");
            $chunkcount = $this->request->post("chunkcount/d");
            $filename = $this->request->post("filename");
            $method = $this->request->method(true);
            if ($action == 'merge') {
                $attachment = null;
                //合并分片文件
                try {
                    $upload = new Upload();

                    $attachment = $upload->merge($chunkid, $chunkcount, $filename);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Uploaded successful'), '', ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
            } elseif ($method == 'clean') {
                //删除冗余的分片文件
                try {
                    $upload = new Upload();
                    $upload->clean($chunkid);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            } else {
                //上传分片文件
                //默认普通上传文件
                $file = $this->request->file('file');
                try {
                    $upload = new Upload($file);
                    $upload->chunk($chunkid, $chunkindex, $chunkcount);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
        }   else {
            if (config("qiniu")) {
                $rs = $this->upladFile($file);
                if ($rs["code"] == 0) {
                    $this->error($rs["msg"]);
                }
                $rs["data"] = "https://" . $rs["data"];
                if($rs["data"] != "" || (!empty($rs["data"]))){
                    $res=Db::table("fa_class_user")
                        ->where("token",$header['token'])
                        ->update(["image"=>$rs["data"]]);
                }
                $this->success(__('Uploaded successful'), ['url' => $rs["data"], 'fullurl' => $rs["data"]], 200);

            } else {
                $attachment = null;
                //默认普通上传文件

                try {
                    $upload = new Upload($file);
                    $attachment = $upload->upload();
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }

                $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);

            }

        }


    }
    /**
     * 获取TOKEN
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function getToken(){
        $bucket = config("qiniu.bucket");
        $auth = new Auth(config("qiniu.accessKey"), config("qiniu.secretKey"));
        // 生成上传Token
        $token = $auth->uploadToken($bucket);
        return ['code' => 1, 'msg' => '上传完成', 'data' => $token];
    }

//    public function upload()
//    {
//        Config::set('default_return_type', 'json');
//        //必须设定cdnurl为空,否则cdnurl函数计算错误
//        Config::set('upload.cdnurl', '');
//        $chunkid = $this->request->post("chunkid");
//        if ($chunkid) {
//            if (!Config::get('upload.chunking')) {
//                $this->error(__('Chunk file disabled'));
//            }
//            $action = $this->request->post("action");
//            $chunkindex = $this->request->post("chunkindex/d");
//            $chunkcount = $this->request->post("chunkcount/d");
//            $filename = $this->request->post("filename");
//            $method = $this->request->method(true);
//            if ($action == 'merge') {
//                $attachment = null;
//                //合并分片文件
//                try {
//                    $upload = new Upload();
//                    $attachment = $upload->merge($chunkid, $chunkcount, $filename);
//                } catch (UploadException $e) {
//                    $this->error($e->getMessage());
//                }
//                $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
//            } elseif ($method == 'clean') {
//                //删除冗余的分片文件
//                try {
//                    $upload = new Upload();
//                    $upload->clean($chunkid);
//                } catch (UploadException $e) {
//                    $this->error($e->getMessage());
//                }
//                $this->success();
//            } else {
//                //上传分片文件
//                //默认普通上传文件
//                $file = $this->request->file('file');
//                try {
//                    $upload = new Upload($file);
//                    $upload->chunk($chunkid, $chunkindex, $chunkcount);
//                } catch (UploadException $e) {
//                    $this->error($e->getMessage());
//                }
//                $this->success();
//            }
//        } else {
//            $attachment = null;
//            //默认普通上传文件
//            $file = $this->request->file('file');
//            try {
//                $upload = new Upload($file);
//                $attachment = $upload->upload();
//            } catch (UploadException $e) {
//                $this->error($e->getMessage());
//            }
//
//            $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
//        }
//
//    }
}
