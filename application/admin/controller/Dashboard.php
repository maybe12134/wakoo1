<?php

namespace app\admin\controller;

use app\admin\controller\teach\ClassUser;
use app\admin\model\Admin;
use app\admin\model\teach\ClassCampus;
use app\admin\model\teach\ClassType;
use app\admin\model\teach\ClassUser as AppClassUser;
use app\admin\model\teach\ClassVideo;
use app\admin\model\teach\ClassVisitor;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;
use think\Session;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

//    /**
//     * 查看
//     */
//    public function index()
//    {
//        try {
//            \think\Db::execute("SET @@sql_mode='';");
//        } catch (\Exception $e) {
//
//        }
//        $column = [];
//        $starttime = Date::unixtime('day', -6);
//        $endtime = Date::unixtime('day', 0, 'end');
//        $joinlist = Db("user")->where('jointime', 'between time', [$starttime, $endtime])
//            ->field('jointime, status, COUNT(*) AS nums, DATE_FORMAT(FROM_UNIXTIME(jointime), "%Y-%m-%d") AS join_date')
//            ->group('join_date')
//            ->select();
//        for ($time = $starttime; $time <= $endtime;) {
//            $column[] = date("Y-m-d", $time);
//            $time += 86400;
//        }
//        $userlist = array_fill_keys($column, 0);
//        foreach ($joinlist as $k => $v) {
//            $userlist[$v['join_date']] = $v['nums'];
//        }
//
//        $dbTableList = Db::query("SHOW TABLE STATUS");
//        $addonList = get_addon_list();
//        $totalworkingaddon = 0;
//        $totaladdon = count($addonList);
//        foreach ($addonList as $index => $item) {
//            if ($item['state']) {
//                $totalworkingaddon += 1;
//            }
//        }
//        $this->view->assign([
//            'totaluser'         => User::count(),
//            'totaladdon'        => $totaladdon,
//            'totaladmin'        => Admin::count(),
//            'totalcategory'     => \app\common\model\Category::count(),
//            'todayusersignup'   => User::whereTime('jointime', 'today')->count(),
//            'todayuserlogin'    => User::whereTime('logintime', 'today')->count(),
//            'sevendau'          => User::whereTime('jointime|logintime|prevtime', '-7 days')->count(),
//            'thirtydau'         => User::whereTime('jointime|logintime|prevtime', '-30 days')->count(),
//            'threednu'          => User::whereTime('jointime', '-3 days')->count(),
//            'sevendnu'          => User::whereTime('jointime', '-7 days')->count(),
//            'dbtablenums'       => count($dbTableList),
//            'dbsize'            => array_sum(array_map(function ($item) {
//                return $item['Data_length'] + $item['Index_length'];
//            }, $dbTableList)),
//            'totalworkingaddon' => $totalworkingaddon,
//            'attachmentnums'    => Attachment::count(),
//            'attachmentsize'    => Attachment::sum('filesize'),
//            'picturenums'       => Attachment::where('mimetype', 'like', 'image/%')->count(),
//            'picturesize'       => Attachment::where('mimetype', 'like', 'image/%')->sum('filesize'),
//        ]);
//
//        $this->assignconfig('column', array_keys($userlist));
//        $this->assignconfig('userdata', array_values($userlist));
//
//        return $this->view->fetch();
//    }




    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('AdminLog');
    }

    /**
     * 查看
     */
    public function index()
    {
        $day=date('Y-m-d',time());
        $yesday=date('Y-m-d',strtotime('-1 day'));

        $usernum=AppClassUser::where('create_time','LIKE',"$day%")->count();
        $visitor=ClassVisitor::where('create_time','LIKE',"$day%")->count();
        $unum=AppClassUser::count();
        $typenum=ClassType::count();
        $videonum=ClassVideo::count();
        $adminum=Admin::count();

        $number=0;
        $yesnumber=0;
        $admin=Session::get('admin');
        $today=Db::table('fa_class_buy')
            ->where([
                'state' => 1,
                'create_time' =>['LIKE',"$day%"]
                ])
            ->count();
        $yes=Db::table('fa_class_buy')
            ->where([
                'state' => 1,
                'create_time' =>['LIKE',"$yesday%"]
                ])
            ->count();

        
        //判断所属权限
        if($admin['campus_id']=='*' || strlen($admin['campus_id'])>1){
            if($today==0){
               $growth=0;
            }else{
                 $growth=round(($today-$yes)/$today*100,2);
            }
           
        }else{
           
            $campus=ClassCampus::get($admin['campus_id']);
            $number=$campus->classuser()->where('create_time','LIKE',"$day%")->count();
            $number=$campus->classuser()->where('create_time','LIKE',"$yesday%")->count();
            if($number==0){
                $growth=0;
            }else{
                $growth=round(($number-$yesnumber)/$number*100,2);
            }
            
        }

        $this->view->assign('unum',$unum);
        $this->view->assign('typenum',$typenum);
        $this->view->assign('videonum',$videonum);
        $this->view->assign('visitor',$visitor);
        $this->view->assign('adminum',$adminum);
        $this->view->assign('usernum',$usernum);
        $this->view->assign('growth',$growth);
        $this->view->assign('number',$number);
        return $this->view->fetch();
    }


    public function visitor(){
        $arr=array('日','一','二','三','四','五','六');
        for($number=-7;$number<0;$number++){
            $time[$number+7]=date('Y-m-d',strtotime("$number days"));
            $week[]='星期'.$arr[date('w',strtotime($time[$number+7]))];
        }


        $admin=Session::get('admin');
        if($admin['campus_id']!='*' || strlen($admin['campus_id'])!=1){
            $campus=ClassCampus::get($admin['campus_id']);
            foreach($time as $key=>$val){
                $data[$key]=$campus->classuser()->where('create_time','LIKE',"$val%")->count();
            }
        }else{
            $visitor=model('ClassVisitor');
            foreach($time as $key=>$val){
                $data[$key]=$visitor->where('create_time','LIKE',"$val%")->count();
            }
        }
        
        return json(['code'=>200,'msg'=>'请求成功','data'=>['week' => $week,'num' => $data]]);
    }




    public function indent(){
        $arr=array('日','一','二','三','四','五','六');
        for($number=-7;$number<0;$number++){
            $time[$number+7]=date('Y-m-d',strtotime("$number days"));
            $week[]='星期'.$arr[date('w',strtotime($time[$number+7]))];
            $num=$time[$number+7];
            $data[]=Db::table('fa_class_indent')->where('create_time','LIKE',"$num%")->count();
        }
        return json(['week' => $week,'data' => $data]);
    }
 
}
