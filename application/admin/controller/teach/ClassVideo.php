<?php

namespace app\admin\controller\teach;

use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Session;
use ReflectionFunction;
use app\admin\model\teach\ClassFtype;
use app\admin\model\teach\ClassType;
use JamesHeinrich\GetID3\GetID3;
/**
 *
 *
 * @icon fa fa-circle-o
 */
class ClassVideo extends Backend
{

    /**
     * ClassType模型对象
     * @var \app\admin\model\teach\ClassVideo
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\teach\ClassVideo;
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
             foreach($static['where'] as $val){
                if($val[0]=='type'){
                    $origin=strpos($val[2],'-');
                        if(empty($origin)){
                            $age=preg_replace('/[^0-9]/',"",$val[2]);
                            if($age){
                                $where['type_age']=['=',intval($age)];
                            }else{
                                $finish=strrpos($val[2],'%');
                                $str=substr($val[2],0,$origin) . substr($val[2],$finish,strlen($val[2]));
                                $where[$val[0]]=[$val[1],$str];
                            }
                        }else{
                            $age=preg_replace('/[^0-9]/',"",$val[2]);
                            $where['type_age']=['=',intval($age)];
                            $finish=strrpos($val[2],'%');
                            $str=substr($val[2],0,$origin) . substr($val[2],$finish,strlen($val[2]));
                            $where[$val[0]]=[$val[1],$str];
                        }
                }else{
                    $where[$val[0]]=[$val[1],$val[2]];
                }
            }
            $list=$this->model
                ->alias('v')
                ->field('v.id,class_type_id,long,title,video_image,which,p.type,p.type_age,f.type_name')
                ->join('fa_class_type p','p.id=v.class_type_id')
                ->join('fa_class_ftype f','f.id=p.class_ftype_id')
                ->where($where)
                ->order('v.class_type_id','asc')
                ->order('v.id', 'asc')
                ->paginate($limit);
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
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
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
            $result = $this->model->allowField(true)->save($params);
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
        $type=$this->model->with(['classtype'=>['classftype']])->select($row['id']); 
        foreach($type as $val){
            $row->ftype=$val->classtype->classftype->id; 
            $row->type=$val->classtype->id;
        }       
        $tid=$row['class_type_id'];
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('tid', $tid);
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
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
     * 回收站
     *
     * @return string|Json
     * @throws \think\Exception
     */
    public function recyclebin()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->onlyTrashed()
            ->field('p.id,title,video_image,p.delete_time,type,type_age')
            ->alias('p')
            ->join('fa_class_type f','p.class_type_id = f.id')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        foreach($list as &$row){
            $row->delete_time=date('Y-m-d H:i:s',$row->delete_time);
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }



    public function ftype(){
        $ftype=new ClassFtype();
         //当前页
        $page     = $this->request->request("pageNumber");
         //分页大小
        $pagesize = $this->request->request("pageSize");
        if($keyValue=$this->request->request("keyValue")){
            $arr=$ftype->field('type_name')
            ->where('id',$keyValue)
            ->find();
            return ['total'=>1, 'list'=>[
                ['id'=>$keyValue, 'type'=>$arr['type_name']]
            ]
          ];
        }
        if(empty($type_id)){
            $arr=$ftype->field('id,type_name')
                    ->page($page,$pagesize)
                    ->select();
            $total=$ftype->count();
            foreach($arr as &$value){
                $value['type']=$value['type_name'];
                unset($value['type_name']);
            }
            $data=array('list'=>$arr,'total'=>$total);
        }  
        return json($data);
    }

    public function ptype(){
        $ptype=new ClassType();
        $page     = $this->request->request("pageNumber");
         //分页大小
        $pagesize = $this->request->request("pageSize");
        $type_id  = $this->request->post('type');
        if(empty($type_id)){
            $type_id=Session::get('type_id');
        }else{
            Session::set('type_id',$type_id);
        }
        if($keyValue=$this->request->request("keyValue")){
            $arr=$ptype
            ->field('id,type,type_age')
            ->where([
                'id'             => $keyValue,
                'class_ftype_id' => $type_id
            ])->find();
            return ['total'=>1, 'list'=>[
                ['id'=>$arr['id'], 'type'=>$arr['type'].'-'.$arr['type_age']]
            ]
          ];
        }
        $arr=$ptype
            ->field('id,type,type_age')
            ->where('class_ftype_id',$type_id)
            ->page($page,$pagesize)
            ->select();
        $total=$ptype
            ->where('class_ftype_id',$type_id)
            ->count();
        $data=array();
        foreach($arr as $key=>$value){
            $data[$key]=['id'=>$value->id,'type'=>$value['type'].'-'.$value['type_age']];
        }
        $data=array('list'=>$data,'total'=>$total); 
       
        return json($data);
    }

    public function sky(){
        return json(array('list'=>[],'total'=>0));
    }

}
