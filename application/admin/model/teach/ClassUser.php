<?php

namespace app\admin\model\teach;

use think\Model;
use traits\model\SoftDelete;

class ClassUser extends Model
{

    

    

    // 表名
    protected $name = 'class_user';
    

    
    protected $resultSetType = 'collection';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // protected $deleteTime = false;


    use SoftDelete;
    protected $deleteTime = 'delete_time'; 


    // 追加属性
    protected $append = [

    ];



    public function classcampus($campus_id=null)
    {
        return $this->belongsToMany('ClassCampus','\\app\\admin\\model\\teach\\ClassBelong')->wherePivot('class_campus_id','IN',$campus_id);
    }


    public function ptype($belong_id=null)
    {
        return $this->belongsToMany('ClassType','\\app\\admin\\model\\teach\\ClassBuy')->wherePivot('class_belong_id','IN',$belong_id);
    }


    public function classtype()
    {
        return $this->belongsToMany('ClassType','\\app\\admin\\model\\teach\\ClassBuy');
    }
 

    public function campus()
    {
        return $this->belongsToMany('ClassCampus','\\app\\admin\\model\\teach\\ClassBelong');
    }
}
