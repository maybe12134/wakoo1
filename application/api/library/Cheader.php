<?php

namespace app\api\library;

use DateTime;
use think\Cache;
use think\Exception;
use think\Hook;

class Cheader{
    protected $data = '';

    public function __construct($header)
    {
      $this->data=$header;
    }
  
   //小程序token验证
   public function check(){
        
    if(empty($this->data)){
        throw new Exception('请求信息不存在');
    }
    if(empty($this->data['token'])){
        throw new Exception('token不存在');
    }
    if(Cache::store('redis')->has($this->data['token'])){
        $user_d=Cache::store('redis')->get($this->data['token']);
    }else{
        throw new Exception('登录信息已过期,请重新登录');
    }
    if($this->data['token']!=$user_d['token']){
        throw new Exception('token传输错误');
    }
    Cache::store('redis')->set($user_d['token'],$user_d,604800);
    $token =Cache::store('redis')->get($user_d['id']);
    if(empty($token)){
        throw new Exception('登录信息错误');
    }
    if($user_d['token']!=$token){
        Cache::store('redis')->rm($user_d['token']);
        throw new Exception('您已在其他设备登录');
    }
    Hook::listen('wakoohome',$user_d);
    return $user_d;
}
  
}