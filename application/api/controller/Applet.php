<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Cache;
use think\Db;
use app\api\library\Cheader;
use think\Exception;
use app\admin\model\ClassCampus;
use app\admin\model\sundry\ClassAdvert;
use app\admin\model\sundry\ClassChart;

class Applet extends Api{
    protected $noNeedLogin = ['mobilelogin','register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';
    public $_user          = '';
    protected $row         = '';
    protected $father      = '';
    protected $item        = '';
    
    public function __construct(){
        parent::__construct();
        $header=get_all_header();
        $params=new Cheader($header);
        try{
            $this->_user  =$params->check();
        }catch(Exception $e){
            $this->error($e->getMessage(),'',403);
        }
    }

   

    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }

    

    //退出
    public function quit(){
        $row=$this->request->post();
        if($this->_user['iphone']!=$row['iphone']){
            $this->error('信息错误','',500);
        }
        if(Cache::get($this->_user['iphone'])){
          Cache::rm($this->_user['token']);
        }
        $this->success('退出成功','',200);
    }



    public function changepad(){
        $params = $this->request->post();
        $validate = $this->validate($params, [
            'password|原密码'     => 'require',
            'passwordone|新密码'     => 'require',
            'password_confirm|重复密码'     => 'require|confirm',
        ]);
        if(!$validate){
            $this->error('验证失败','',500);
        }else if($validate!=true){
            $this->error($validate,'',404);
        }
        if($params['password']!=$this->_user['password']){
            $this->error('原密码错误','',403);
        }
        Db::startTrans();
        try{
            Db::table("fa_class_user")
            ->where("iphone",$this->_user['iphone'])
            ->update(["password"=>$params['passwordone']]);
            Cache::rm($this->_user['token']);
            //提交事务
            Db::commit();
        }catch(Exception $e){
            //回滚事务
            Db::rollback();
        }
        $this->success('修改成功','',200);    
    }



    public function chart(){
        $data=ClassChart::field('chart')->where('status',1)->select()->toArray();
        $data=array_column($data,'chart');
        $this->success('请求成功',$data,200);
    }

    public function advert(){
        $data=ClassAdvert::field('video')->where('status',1)->find();
        $this->success('请求成功',$data,200);
    }

   

    public function address(){
        $data=ClassCampus::field("id,store_name,s_iphone,lat,lng,addres")
            ->select();
        $this->success('请求成功',$data,200);
    }

}
