<?php

namespace app\admin\model\test;

use think\Model;


class ClassWqc extends Model
{

    

    

    // 表名
    protected $name = 'asd';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function wqc()
    {
        return $this->belongsTo('app\admin\model\Wqc', 'wqc_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
