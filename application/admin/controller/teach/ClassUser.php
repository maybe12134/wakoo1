<?php

namespace app\admin\controller\teach;

use app\admin\model\teach\ClassType;
use app\admin\model\teach\ClassBuy;
use app\common\controller\Backend;
use app\admin\model\teach\ClassBelong;
use think\Db;
use think\Cache;
use app\admin\library\Auth;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Session;
use think\Hook;
use app\admin\model\teach\ClassUser as UserModel;
use app\admin\model\teach\ClassCampus;
use app\admin\model\teach\ClassFtype;
use app\admin\model\teach\ClassOrder;
use ReflectionFunction;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class ClassUser extends Backend
{

    /**
     * ClassUser模型对象
     * @var \app\admin\model\teach\ClassUser
     */
    protected $model = null;
    protected $campusId = null;

    public function _initialize()
    {
        parent::_initialize();
        $auth=new Auth;
        $this->campusId = $auth->getCampusId(Session::get('admin.'.'campus_id'));
        $this->model = new UserModel();
       
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            //获取闭包实例
            $func = new ReflectionFunction($where); 
            //获取STATIC属性
            $static = $func->getStaticVariables();
            
            $where=array();
            $storeName=array();
            //写入查询条件
            foreach($static['where'] as $val){
                if($val[0]!='store_name'){
                   $where[$val[0]]=[$val[1],$val[2]]; 
                }else{
                    $storeName[$val[0]]=[$val[1],$val[2]];
                }
            }

            //获取关联表信息
            $Cid=Db::table('fa_class_belong')
                     ->alias('l')
                     ->field('l.id,l.class_user_id,c.store_name')
                     ->join('fa_class_campus c','l.class_campus_id=c.id')
                     ->where('c.id','in',$this->campusId['campus_id'])
                     ->where($storeName)
                     ->select();
            $data=array();
            foreach($Cid as $val){
                $id[]=$val['class_user_id'];
                $str=str_replace('瓦酷机器人创客空间','',$val['store_name']);
                $str=preg_replace( '/[^\x{4E00}-\x{9FFF}]+/u', '', $str);
                if(array_key_exists($val['class_user_id'],$data)){
                   array_push($data[$val['class_user_id']],$str);
                }else{
                    $data[$val['class_user_id']][]=$str;
                }
               
            }
            // //取得classuser实例
            $list = $this->model
                        ->field('id,classname,password,sex,age,iphone,image,create_time')
                        ->where('id','in',$id)
                        ->where($where)
                        ->order($sort, $order)
                        ->paginate($limit);

            foreach($list as &$value){
                 $value->store_name=$data[$value->id];
            }
            $result = array("total" => $list->total(), "rows" => $list->items());
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('data', $this->campusId['store_name']);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        if(preg_match('/^0?(13|14|15|17|18)[0-9]{9}$/',$params['iphone']) == false){
            $this->error(__('用户手机号违规', ''));
        }
        if(is_array($this->campusId['campus_id'])){
            $store=$params['class_campus_id'];
            $user=$this->model->where('iphone',$params['iphone'])->find();
            if(!empty($user)){
                $campus=$user->campus()->where('fa_class_campus.id',$params['class_campus_id'])->find();
                if(!empty($campus)) {
                    $this->error(__('该账号已存在', ''));
                }
            } 
        }else{
            $store=$this->campusId['campus_id'];
            $user=$this->model->where('iphone',$params['iphone'])->find();
            if(!empty($user)){
                $campus=$user->campus()->where('id','IN',$this->campusId['campus_id'])->find();
                if(empty($campus)){
                    $this->error(__('该账号已存在', '')); 
                }
            }
        }
        unset($params['class_campus_id']);
        $params['password']=substr($params['iphone'],7);
        $params['image']='http://cdn.wakoohome.top/3wT2ULZwS3iWfdc2e2c47d09fde2e9a50db7af522fd4.jpg';
        $params = $this->preExcludeFields($params);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            if(empty($user)){
                $user = $this->model->create($params);
                $result    = $user->campus()->save($store);
            }else{
                $result    = $user->campus()->save($store);
            }

          Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        if(empty($params['password'])){
            unset($params['password']);
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    /**
     * 删除
     *
     * @param $ids
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $admin=Session::get('admin');
        if($admin['campus_id']!='*' && strlen($admin['campus_id'])==1){
            
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    /**
     * 购买
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function buy($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            //获得校区名称
            $campus=$row->classcampus($this->campusId['campus_id'])->field('fa_class_campus.id,store_name')->select();
            $store_name=array('清选择相应校区');
            foreach($campus as $value){
                $store_name[$value->id]=$value->store_name;
            }
            $row->store_name=$store_name;
            unset($store_name);
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        $type=explode(',',$params['type']);
        $ptype=explode('-',$type[1]);
        unset($type);
        $arr=array('type','type_age');
        $ptype=array_combine($arr,$ptype);
        $ptype_id=ClassType::field('id')->where($ptype)->find();
        $order=ClassOrder::where(['class_campus_id'=>$row->id,'expiry'=>1])->sum('rqty');
        if(!$order>1){
            $this->error('订单数已用完');
        }
        $belong=ClassBelong::field('id')
            ->where(['class_user_id' => $ids,'class_campus_id' =>$params['campus_id']])
            ->find();
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $params['create_time']=date('Y-m-d H:i:s',time());
        $data=array('class_user_id' => $row->id,
            'class_type_id' => $ptype_id->id,
            'class_belong_id'  => $belong['id']);
        $params=array_merge($params,$data);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $buy=new ClassBuy;
            $result=$buy->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }else{
            $data=$params;
            Hook::listen('indent',$data);
        }
        $this->success();
    }

    /**
     * 详情
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function info($ids = null)
    {
        $row = $this->model->get($ids);
        //获得校区名称
        $campus=$row->classcampus($this->campusId['campus_id'])->field('fa_class_campus.id,store_name')->select();
        $store_name=array('清选择相应校区');
        foreach($campus as $value){
            $store_name[$value->id]=$value->store_name;
        }
        $row->store_name=$store_name;
        unset($store_name);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
    }


     /**
     * 父课程种类
     *
     */
    public function ftype(){
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        $campus_id = $this->request->request('campus_id');
        if(is_string($campus_id)){
            Session::set('campus_id',$campus_id);
            return json(['list'=>array(),'total'=>0]);
        }else{
            $campus_id=Session::get('campus_id');
            if(!is_string($campus_id)){
               return json(['list'=>array(),'total'=>0]); 
            }
        }
        $order=ClassOrder::field('class_type_id')
                ->where('class_campus_id',$campus_id)
                ->select();
        $order=array_column($order,'class_type_id');
        $order=array_unique($order);
        $type=Classtype::field('id,class_ftype_id')
                ->page($page,$pagesize)
                ->where('id','in',$order)
                ->select();
        $ftype=ClassFtype::field('id,type_name')->select()->toArray();
        foreach($ftype as $key=>$val){
            $code=0;
            foreach($type as $v){
               if($v->class_ftype_id==$val['id']){
                   $code=1;
                   Cache::store('redis')->set($val['type_name'],$val['id'],1800);
               }
            }
            if($code==0){
                unset($ftype[$key]);
            }
        }
        $ftype=array_values($ftype);
        $total=0;
        foreach($ftype as &$val){
            $val['name']=$val['type_name'];
            $total++;
        }
        return json(['list'=>$ftype,'total'=>$total]);
    }


    /**
     * 子课程种类
     *
     */
    public function campus(){
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        $campus_id = $this->request->request('campus_id');
        $ftype=$this->request->post('ftype');
        $iphone=$this->request->post('iphone');
        if(empty($ftype)){
            $iphone=Session::get('iphone');
            $ftype=Session::get('ftype');
            $campus_id=Session::get('campus_id');
            $fid  =Cache::store('redis')->get($ftype);
            if(empty($fid)){
                return json(['list'=>array(),'total'=>0]);
            }
        }else{
            Session::set('iphone',$iphone);
            Session::set('ftype',$ftype);
            Session::set('campus_id',$campus_id);
            return json(['list'=>array(),'total'=>0]);;
        }
        $order=ClassOrder::field('id,class_type_id')->where('class_campus_id',$campus_id)->select();
        $order=array_column($order,'class_type_id');
        $order=array_unique($order);
        $user=$this->model->field('id')
            ->where('iphone',$iphone)
            ->find();
        $f_user=$user->classtype()->where('class_ftype_id',$fid)->select();
        $pid=array();
        if(empty($f_user)){
            $pid=null;
        }else{
            foreach($f_user as $val){
                $pid[]=$val->id;
            } 
        }
        $type=ClassType::field('type,type_age')
                ->where('id',['NOT IN',$pid],['IN',$order])
                ->where('class_ftype_id',$fid)
                ->page($page,$pagesize)
                ->select();
        $total=ClassType::field('type,type_age')
        ->where(['id' =>['not in',$pid],
                    'class_ftype_id' => $fid
        ])->count();
        foreach($type as $key=>$value){
            $list[$key]['name']=$value->type.'-'.$value->type_age;
        }   
        if(empty($list)){
            return json(['list'=>[],'total'=>0]);
        }else{
            return json(['list'=>$list,'total'=>$total]);
        }    
    }

    /**
     * 判断用户是否存在
     *
     */
    public function type(){
        $iphone=$this->request->post('iphone');
        if(count($this->campusId['campus_id'])>1){
            $user=$this->model->where('iphone',$iphone)->find();
            if(empty($user)){
                $this->success();
            }else{
                $this->success();
            } 
        }else{
            $user=$this->model->where('iphone',$iphone)->find();
            if(empty($user)){
                $this->success();
            }
            $campus=$user->campus()->where('id','IN',$this->campusId['campus_id'])->find();
            if(empty($campus)){
                $this->error(__('该账号已存在', '')); 
            }else{
                $this->success();
            }
        }
    }

     /**
     * 用户信息异步
     *
     */
    public function iphone(){
        $iphone=$this->request->post('iphone');
        $user=$this->model
            ->field('id,classname,age,sex')
            ->where('iphone',$iphone)
            ->find();
        if(empty($user)){
            $this->success();
        }
        $campus=ClassCampus::field('id,store_name')
            ->select();
        $belong=ClassBelong::field('class_campus_id')
            ->where('class_user_id',$user['id'])
            ->select();
        foreach($campus as $k=>$value){
            foreach($belong as $val){
                if($val['class_campus_id']==$value['id']){
                   unset($campus[$k]);
                }
            }
        }
        $user->store_name=$campus;
        return json(['data'=>$user]);
    }

    /**
     * 已属校区
     *
     */
    public function store(){
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        $iphone=$this->request->post('iphone');
        $store=$this->request->post('store');
        if(empty($iphone)){
            $iphone=Session::get('iphone');
            $store=Session::get('store');
        }else{
            Session::set('iphone',$iphone);
            Session::set('store',$store);
            return json(['list'=>array(),'total'=>0]);
        }
        $user=$this->model->field('id')
            ->where('iphone',$iphone)
            ->find();
        $campus=$user->classcampus($store)->page($page,$pagesize)->select();
        foreach($campus as $val){
            $belong=$val->pivot->id;
        }
        $ptype=$user->ptype($belong)->select();
        if(empty($ptype)){
            return json(['list'=>array(),'total'=>0]);
        }else{
            foreach($ptype as $v){
                $list[]['name']=$v->type.'-'.$v->type_age;
            }
        }
        return json(['list'=>$list,'total'=>count($list)]);
    }

     /**
     * 用户审核状态
     *
     */
    public function state(){
        $iphone=$this->request->post('iphone');
        $type=$this->request->post('type');
        $type=explode('-',$type);
        $arr=array('type','type_age');
        $type=array_combine($arr,$type);
        $user=$this->model->field('id')->where('iphone',$iphone)->find();
        $ptype=$user->classtype()->where($type)->select();
        foreach($ptype as $val){
            $state=$val->pivot->state;
        }
        return json($state);
    }

    /**
     * 用户数量统计
     *
     */
    public function unumber(){
        $arr=array('日','一','二','三','四','五','六');
        for($number=-7;$number<0;$number++){
            $time[$number+7]=date('Y-m-d',strtotime("$number days"));
            $week[]='星期'.$arr[date('w',strtotime($time[$number+7]))];
        }
        $admin=Session::get('admin');
        if($admin['campus_id']=='*' || strlen($admin['campus_id'])>1){
            for($i=0;$i<7;$i++){
            $user[]=Db::table('fa_class_user')->field('id')->where('create_time','LIKE',"$time[$i]%")->select();
            }
            foreach($user as $value){
                $data[]=count($value);
            }
            return json(['code'=>200,'msg'=>'请求成功','data'=>['week'=>$week,'num'=>$data]]);
        }else{
            $campus=ClassCampus::get($admin['campus_id']);
            for($i=0;$i<7;$i++){
               $data[]=$campus->classuser()->where('create_time','LIKE',"$time[$i]%")->count();
            }
            return json(['code'=>200,'msg'=>'请求成功','data'=>['week'=>$week,'num'=>$data]]);
        }
    }
}
