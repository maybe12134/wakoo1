<?php

namespace app\api\controller;


use app\common\controller\Api;
use think\Config;


/**
 * 用户接口
 */
class Users extends Api
{
    protected $noNeedLogin = ['login',"yzm",'mobilelogin', 'denglu','register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';
    protected $main        = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->main=new Mainlogic();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }

    /**
     * 瓦酷主页
     * @ApiMethod (POST)
     */
    public function index(){
        $this->main->homepage();
    }


    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        $this->main->quit();
    }
    

    /**
     * 首页广告
     * @ApiMethod (POST)
     */
    public function advert(){
        $this->main->advert();
    }


    /**
     * 学习乐园
     * @ApiMethod (POST)
     */
    public function reads()
    {
        return $this->main->reads();
    }


    /**
     * 年龄
     * @ApiMethod (POST)
     */
    public function typeage(){
        $this->main->typeage();
    }



    /**
     * 轮播图
     * @ApiMethod (POST)
     */
    public function chart(){
        $this->main->chart();
    }


    /**
     * 校区
     * @ApiMethod (POST)
     */
    public function course(){
        $this->main->execution();
    }
    
    /**
     * 视频列表
     * @ApiMethod (POST)
     */
    public function videolist(){
        return $this->main->videolist();
    }

    /**
     * 视频进度
     * @ApiMethod (POST)
     */
    public function videoplan(){
        return $this->main->videoplan();
    }

     /**
     * 店铺地址
     * @ApiMethod (POST)
     */
    public function address(){
        $this->main->address();
    }

    /**
     * 修改密码
     * @ApiMethod (POST)
     */
    public function alter(){
        $this->main->changepad();
    }
    
}
