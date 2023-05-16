<?php

namespace app\api\controller;


use app\common\controller\Api;
use think\Db;
use app\common\library\VideostreamClass;

class Afile extends Api
{
    protected $noNeedLogin =['index'];


    public function index(){
        //跨域发送请求
        // header('Access-Control-Allow-Origin:*');
        // header('Access-Control-Allow-Methods:OPTIONS, GET, POST');//允许option，get，post请求
        // header('Access-Control-Allow-Headers:X-Requested-With,id,token');//允许x-requested-with请求头,需要添加header头里的参数可以继续添加
        // $file="https://cdn.wakoohome.top/bf8b9699168723dd3d4d16fdc8d12310.mp4";
        // $stream= new VideostreamClass();
        // $stream->bofang($file);



        $stream= new VideostreamClass();
        $stream->open();

    }

}