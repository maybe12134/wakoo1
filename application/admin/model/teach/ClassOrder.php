<?php

namespace app\admin\model\teach;

use think\Model;
use traits\model\SoftDelete;

class ClassOrder extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'class_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    
    public function getExpiryAttr($value)
    {
        $state = [0=>'已过期',1=>'生效'];
        return $state[$value];
    }







}
