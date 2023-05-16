<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Random;
use think\Config;
use think\Cache;
use think\Db;
use app\admin\model\teach\ClassUser;
use app\admin\model\ClassCampus;
use think\Exception;

class Iinitial extends Api{
    protected $noNeedLogin = ['mobilelogin','register','logon','resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';
    protected $model   = '';


    public function __construct(){
        parent::__construct();
        $this->model=new ClassUser();
    }


    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }

    /**
     * 登录
     * @ApiMethod (POST)
     */
    public function logon(){
        return $this->Iinitial($this->request->post());
    }



    /**
     * 验证码
     */
    public function code()
    {
        $ran_num=mt_rand(1,10000);
        //生成验证码地址
        $src = captcha_src($ran_num);
        //返回数据
        $res = [
            'src' => 'https://'."wakoojia.top".$src,
            'ran_num' => $ran_num,
        ];
        $this->success('请求成功',$res,200);
    }

   
    public function Iinitial($params){
        $validate = $this->validate($params, [
            'iphone|用户名'     => 'require',
            'password|密码'     => 'require',
            'code|验证码'       => 'require',
            'ran_num|验证规则'  => 'require',
        ]);
        if(!$validate){
            $this->error('验证失败','',500);
        }else if($validate!=true){
            $this->error($validate,'',404);
        }
        //file_put_contents('text.txt',$_SERVER["REMOTE_ADDR"]);
        //验证验证码
        $check = captcha_check($params['code'],$params['ran_num']);
        if(!$check)
        {
            $this->error('验证码错误','',403);
        }
        //获取账号缓存
        $cache=Cache::store('redis')->get($params['iphone']);
        //判断失败次数,锁定账号登录
        if($cache){
          if(array_key_exists('prohibit',$cache)){
             $time=date('i:s',$cache['inhibit']-time());
             $this->error('您已被锁定','您当前仍被锁定,请'.$time.'后尝试',501);
          }
          if(time()>$cache['lastime']||$cache['rate']>=5){
              Cache::store('redis')->rm($params['iphone']);
              $inhibit=strtotime('+30 minute');
              Cache::store('redis')->set($params['iphone'],['prohibit'=>true,'inhibit'=>$inhibit],1800);
              $this->error('登录锁定',$inhibit,500);
          }
        }
        $user=$this->model->where([
            "iphone"    =>  $params["iphone"],
            "password"  =>  $params['password']
            ])->find();
        if(!empty($user)){
            //开启事务
            Db::startTrans();
                $token = Random::uuid();
                try{
                    $this->model->save([
                        'token'  => $token
                    ],['id' => $user->id]);
                    //提交事务
                    Db::commit();
                }catch(Exception $e){
                    //回滚事务
                    Db::rollback();
                }
                $user=$this->model->where('id',$user->id)->find();
                //写入登录时效
                Cache::store('redis')->set($user->token,$user,604800);
                Cache::store('redis')->set($user->id,$user->token);
                Cache::store('redis')->rm($user->iphone);
                $data=array("code"=>200,"msg"=>"登录成功","data"=>$user);
        }else{
            //写入规定时间内的失败次数
            $iphone=Cache::store('redis')->get($params['iphone']);
            if(empty($iphone)){
                $rate=array('lastime'=>strtotime('+10 minute'),'rate'=>1,1800);
                Cache::store('redis')->set($params['iphone'],$rate);
            }else{
                Cache::store('redis')->rm($params['iphone']);
                $iphone['rate']=$iphone['rate']+1;
                $expires=$iphone['lastime']-time();
                Cache::store('redis')->set($params['iphone'],$iphone,$expires);
            }
            $data = array("code"=>403,"msg"=>"账号或密码输入错误,请仔细查看");
        }
        return json($data);
    }

     /**
     * 校区联系方式
     * @ApiMethod (POST)
     */
    public function iphone(){
        $data=ClassCampus::field('store_name,s_iphone')->select();
        $this->success('请求成功',$data,200);
    }
}