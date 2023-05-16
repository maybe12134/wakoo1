<?php

namespace app\api\controller;

use app\admin\model\teach\ClassFtype;
use app\admin\model\teach\ClassUser;
use app\api\controller\Luan;
use app\admin\model\teach\ClassType;
use think\Db;
class Mainlogic extends Luan{

    protected $father   = null;

    public function homepage(){
        $list=ClassFtype::field('id,type_name,ftype_image')->select()->toArray();
        $arr=array('id','decide','ftype_id','title','mg');
        foreach($list as $key=>$value){
            if($value['type_name']=='编程' || $value['type_name']=='机器人'){
                array_unshift($list[$key],1);
            }else{
                array_unshift($list[$key],0);
            }
            array_unshift($list[$key],$key);
            $list[$key]=array_combine($arr,$list[$key]);
        }
        $data=array('image'=>'https://cdn.wakoohome.top/wakoojia.png','list'=>$list);
        $this->success('请求成功',$data,200);
    }


    public function execution(){
        //模型查询
        $user=ClassUser::where('id',$this->_user['id'])->find();
        $data=$user->classtype()->field('fa_class_type.id,class_ftype_id,type,type_age')->select();
        $ftype=ClassFtype::field('id,type_name,ftype_image')->where('status',1)->select()->toArray();
        //阴藏处理相关字段
        foreach($data as &$val){
            $val->hidden(['pivot']);
            $val->list=$val->type.'-'.$val->type_age.'岁';
            unset($val->type);
            unset($val->type_age);
        }
        unset($val);
        foreach($ftype as &$value){
            $value['state']='未购买';
            $value['list'] =array();
            foreach($data as $val){
                if($val->class_ftype_id==$value['id']){
                    $value['state']='已购买';
                    array_push($value['list'],$val);
                } 
            }
            array_multisort($value['list']);
        }
        $this->success('',$ftype,200);
    }





    //父级分类
    public function ftype()
    {
        $list=Db::table("fa_class_ftype")
            ->field("id,type_name")
            ->select();
        foreach($list as &$val){
            $i=0;
            $ftype=ClassFtype::get($val['id']);
            if(!empty($ftype)){
                $data =$ftype->classtype;
                foreach($data as $value){
                    $type=ClassType::get($value->id);
                    if(!empty($type->classvideo)){
                      $i++;
                    }
                }
                if($i>0){
                    $val['decide'] = 1;
                }else{
                    $val['decide'] = 0;
                }
            }
        }
        if(empty($list)){
            $this->error('一级类目请求失败','',404);
        }else{
            $this->father=$list;
        }
    }
    
    //二级分类
    public function ptype(){
        $navi_cate=ClassType::field('class_ftype_id,type,status')
                ->order('status')
                ->select();
        $item=array();
        foreach($this->father as $k=>$value){
            $data=array();
            foreach($navi_cate as $val){
                if($val['class_ftype_id']==$value['id']){
                    array_push($data,$val['type']);
                }
            }
            $data=array_unique($data);
            $data=array_values($data);
            $item[$k]['id']=$value['id'];
            $item[$k]['list']=$data;  
        }
        if(empty($item)){
            $this->error('二级类目请求失败','',404);
        }else{
            $this->item=$item;
        }

    }
 
    //分类组合数据
    public function end(){
        $this->success('请求成功',['navi_cate'=>$this->father,'item'=>$this->item],200);
    }


    //学习乐园分类开始
    public function reads(){
        $this->ftype();
        $this->ptype();
        $this->end();
       
    }

    //年龄请求分析
    public function open($row){
        if(empty($row['ftype_id'])||empty($row['type'])){
            $this->error('参数未按规范传入','',404);
        }
        $list=ClassType::field('id,type_age,inage_image,age_image')
           ->where([
            'class_ftype_id'  => $row['ftype_id'],
            'type'            => $row['type'],
           ])->select();
        $this->success('请求成功',$list,200);
    }

    //年龄请求开始
    public function typeage(){
        $this->open($this->request->post());
    }
}


