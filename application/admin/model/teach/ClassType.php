<?php

namespace app\admin\model\teach;

use think\Model;
use traits\model\SoftDelete;
use app\admin\model\teach\ClassVideo;

class ClassType extends Model
{

    

    

    // 表名
    protected $name = 'class_type';
    

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
        self::beforeDelete( function (Classtype $type) {
            foreach($type->classvideo()->select() as $value){
                $value->delete();
            }
        });

        self::beforeRestore(function(Classtype $type){
            foreach(ClassVideo::onlyTrashed()->where('class_type_id',$type->id)->select() as $value){
                $value->restore();
            }
        });
    }


    public function classvideo()
    {
        return $this->hasMany('ClassVideo');
    }

    
    public function classftype()
    {
        return $this->belongsTo('ClassFtype');
    }



    public function setTypeAttr($value)
    {
        return strtolower($value);
    }




}
