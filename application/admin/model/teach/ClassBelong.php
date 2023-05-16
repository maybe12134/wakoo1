<?php

namespace app\admin\model\teach;

use think\model\Pivot;

class classBelong extends Pivot
{
    protected $autoWriteTimestamp = false;


    public function classcampus()
    {
        return $this->belongsTo('ClassCampus','class_campus_id')->setEagerlyType(0);
    }
    
    
}