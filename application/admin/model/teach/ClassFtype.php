<?php

namespace app\admin\model\teach;

use think\Model;
use traits\model\SoftDelete;
use app\admin\model\teach\ClassType;

class ClassFtype extends Model
{

    

    

    // 表名
    protected $name = 'class_ftype';


    protected $resultSetType = 'collection';
    
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
        self::beforeDelete( function (ClassFtype $Ftype) {
            foreach($Ftype->classtype()->select() as $value){
                $value->delete();
            }
        });

        self::beforeRestore(function(ClassFtype $Ftype){
            foreach(ClassType::onlyTrashed()->where('class_ftype_id',$Ftype->id)->select() as $value){
                $value->restore();
            }
        });
    }


    public function classtype()
    {
        return $this->hasMany('ClassType');
    }


}
