<?php

namespace app\admin\model\teach;

use think\model\Pivot;
use app\admin\model\teach\ClassOrder;
use app\admin\model\teach\ClassRate;
use traits\model\SoftDelete;
use think\Db;

class ClassBuy extends Pivot
{

    

    

    // 表名
    protected $name = 'class_buy';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 追加属性
    protected $append = [

    ];



    protected static function init()
    {   
        
        self::afterInsert(function(ClassBuy $buy) {
            $order =new ClassOrder;
            $rate =new ClassRate;
            Db::startTrans();
            try{
                $rate->class_buy_id=$buy->id;
                $rate->save();
                $user=$order->field('id,qty,rqty,create_time')->where(['class_campus_id'=>$buy->campus_id,'class_type_id'=>$buy->class_type_id,'expiry'=>1])->select();
                $maxtime=array();
                foreach($user as $value){
                    if(!empty($maxtime)){
                       if(strtotime($maxtime->create_time)>strtotime($value->create_time)){
                        $maxtime=$value;
                       }
                    }else{
                        $maxtime=$value;
                    }
                }
                if(!empty($maxtime)){
                    $order->save(['qty'=>$maxtime->qty+1,'rqty'=>$maxtime->rqty-1],['id'=>$maxtime->id,'class_campus_id'=>$buy->campus_id,'class_type_id'=>$buy->class_type_id]);
                }
                Db::commit();
            }catch (ValidateException|PDOException|Exception $e){
                Db::rollback();
                $this->error($e->getMessage());
            }
            
        });
    }
    
    public function getStateAttr($value)
    {
        $state = [0=>'待审核',1=>'审核成功',2=>'审核失败'];
        return $state[$value];
    }


    public function buyUser()
    {
        return $this->belongsTo('ClassUser','class_user_id')->field('id,classname,sex,age,iphone');
    }


    public function classtype()
    {
        return $this->belongsTo('ClassType','class_type_id');
    }

    

    public function classbelong(){
        return $this->belongsTo('ClassBelong','class_belong_id');
    }


}
