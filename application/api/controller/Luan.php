<?php

namespace app\api\controller;

use app\api\controller\Applet;
use think\Db;

class Luan extends Applet{




    public function videolist(){
        
        $id = $this->request->get("id");//用户
        $type_id=$this->request->get('type_id');//类型
        $page=$this->request->get('page');
        // file_put_contents('text.txt',"\n".$type_id.'-'.$id, FILE_APPEND | LOCK_EX);
        $buy=Db::table('fa_class_buy')
            ->field('id,state')
            ->where([
                'class_user_id'=>$id,
                'class_type_id'=>$type_id
            ])->find();
        //分页
        $data=Db::table("fa_class_video")
            ->field("id,video,title,video_image,long,which,video_introduction")
            ->where(["class_type_id"=>$type_id,'delete_time'=>null])
            ->order("which")
            ->paginate(5)
            ->toArray();
        
        //分页结束
        if(isset($buy)&&$buy['state']==1){
            foreach($data["data"] as $key=>$value){
                $data['data'][$key]['buy'] ='已买';
            }
            
            //rate表查讯
            $rate=Db::table("fa_class_rate")
                ->field("id,video_look,status")
                ->where("class_buy_id",$buy['id'])
                ->find();
            
            //watch表查询
             foreach($data['data'] as $k=>$z){
                 $watch[$k]=Db::table('fa_class_watch')
                    ->where(['user_id'=>$id,'video_id'=>$z['id']])
                    ->find();
                if(empty($watch[$k])){
                    Db::table('fa_class_watch')->insert(['user_id'=>$id,'video_id'=>$z['id']]);
                    $watch[$k]=Db::table('fa_class_watch')
                    ->where(['user_id'=>$id,'video_id'=>$z['id']])
                    ->find();
                }
             }
            foreach($data['data'] as &$key) {

                foreach($watch as $value){
                    if($value['attach']==1 && $value['video_id']==$key['id']){
                        $key['watch']      = '重看';
                        if($key['which']===$rate['status']){
                            $key['watch_look']=$rate['video_look'];
                        }else{
                            $key['watch_look'] = $value['look'];
                        }
                        break;
                    }else{
                        $key['watch']      = '未重看';
                        if($rate['status'] == $key['which']){
                            $key['watch_look'] = $rate['video_look'];
                        }else{
                            $key['watch_look'] = 0;
                        }
                        
                       
                    }
                }
            } 
            unset($key);
            unset($value);
            //判断观看进度
            foreach($data["data"] as &$key){
                    if($key['which']<$rate['status']){
                        $key['status']='已看完';
                        $key['video_look']  = $key['long'];
                        $key['father_status']['status']='已看完';
                    }else {
                        if ($key['which'] === $rate['status']) {
                            if ($rate['video_look'] < $key['long']) {
                                $key['status'] = '正在观看';
                                $key['video_look'] = $rate['video_look'];
                                $key['father_status']['status'] = '已看完';
                            }
                            if ($rate['video_look'] === $key['long']) {
                                if($key['long']==0){
                                     $key['status'] = '正在观看';
                                }else{
                                     $key['status'] = '已看完';
                                }
                                $key['video_look'] = $rate['video_look'];
                                $key['father_status']['status'] = '已看完';
                            }

                        } else {
                            if ($key['which'] - $rate['status'] === 1) {
                                $key['status'] = '未看';
                                $key['video_look'] = '0';
                                $key['father_status']['status'] = '正在观看';
                            } else {
                                $key['status'] = '未看';
                                $key['video_look'] = '0';
                                $key['father_status']['status'] = '未看';
                            }

                        }
                    }
            }
        }else{
            foreach($data["data"] as $key=>$value){
                $data['data'][$key]['buy'] = '未买';
            }
        }
        unset($value);
        unset($key);
        if(!$data){
            return  json(["code"=>403,"msg"=>"返回失败"]);
        }else{
            foreach($data["data"] as $key=>&$value){
                if(($key===0 && is_null($page))||($page==1&&$key===0)){
                    $value['experience'] = '体验课';
                    unset($value['status']);
                    unset($value['father_status']);
                    unset($value['long']);
                    unset($value['video_look']);
                    unset($value['watch_look']);
                    unset($value['watch']);
                    unset($value['buy']);
                }
                if(($key===1 && is_null($page))||($page==1&&$key===1)){
                   $value['father_status']=(object)array('status'=>'已看完');
                   $value['status']='正在观看';
                }
            }
        }
        
        return  json(["code"=>200,"msg"=>"返回成功", "data"=>$data]);
    }



    //视频进度
    //视频进度
    public function videoplan(){
        $id = $this->request->get("id");
        $video_id=$this->request->get("video_id");
        $type_id=$this->request->get('type_id');//类型
        $plan = intval($this->request->get("plan")); //进度
        if($plan==0){
            $this->success();
        }
        $buy=Db::table('fa_class_buy')
            ->field('id,class_type_id,state')
            ->where([
                'class_user_id'=>$id,
                'class_type_id'=>$type_id
            ])->find();
        if(isset($buy)){
            //进度表查询
            $rate=Db::table("fa_class_rate")
                ->where("class_buy_id",$buy['id'])
                ->find();
            //视频表查询
            $video=Db::table("fa_class_video")
                ->where('id',$video_id)
                ->where("class_type_id",$buy['class_type_id'])
                ->find();
            if($video['which']<=$rate['status']){
                //判断进度更新
                if($rate['status']==$video['which']){
                    if($plan < $video['long'] && $rate['video_look'] < $plan){
                        Db::table("fa_class_rate")
                            ->where("class_buy_id",$buy['id'])
                            ->update(["video_look"=>$plan]);    
                        return json(["code"=>200,"msg"=>"返回成功","data"=>""]);
                    }
                    if($plan===$video['long']){
                        $update=[
                            'status'  =>  $rate['status']+1,
                            'video_look'    =>  0
                        ];
                        Db::table("fa_class_rate")
                            ->where('class_buy_id',$buy['id'])
                            ->update($update);

                        Db::table('fa_class_watch')
                            ->where('user_id',$id)
                            ->where('video_id',$video_id)
                            ->update(['look'=> 0]);
                        return json(["code"=>200,"msg"=>"返回成功","data"=>""]);
                    }
                    return json(["code"=>506,"msg"=>"传输数据违规","data"=>""]);
                }else{
                    if($plan < $video['long']){
                        Db::table('fa_class_watch')
                            ->where('user_id',$id)
                            ->where('video_id',$video_id)
                            ->update(['look'=>$plan,'attach'=>1]);
                    }
                    if($plan===$video['long']){
                        Db::table('fa_class_watch')
                            ->where('user_id',$id)
                            ->where('video_id',$video_id)
                            ->update(['look'=> 0,'attach'=>1]);
                    }
                    return json(["code"=>200,"msg"=>"返回成功","data"=>""]);
                }
            }else{
                return json(["code"=>506,"msg"=>"传输数据违规","data"=>""]);
            }
             
        }else{
            return json(["code"=>506,"msg"=>"传输数据违规","data"=>""]);
        }
    }
}