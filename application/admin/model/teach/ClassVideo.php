<?php

namespace app\admin\model\teach;

use think\Model;
use traits\model\SoftDelete;


class ClassVideo extends Model
{





    // 表名
    protected $name = 'class_video';

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

    public function classtype()
    {
        return $this->belongsTo('ClassType');
    }






}
