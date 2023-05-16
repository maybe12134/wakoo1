<?php

namespace app\admin\controller\teach;

use app\admin\model\teach\ClassType as TeachClassType;
use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use ReflectionFunction;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class ClassType extends Backend
{

    /**
     * ClassType模型对象
     * @var \app\admin\model\teach\ClassType
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\teach\ClassType;

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

            //类型表查询条件
            $type=array();
            //
            $where=array();
            foreach($static['where'] as $val){
                if($val[0]=='type_name'){
                    $val[0]=str_replace('type_name','f.type_name',$val[0]);
                    $type[$val[0]]=[$val[1],$val[2]];
                }else{
                   $val[0]='p.'.$val[0];
                   $where[$val[0]]=[$val[1],$val[2]];
                }
            }
            $list=$this->model
                ->field('p.id,type,type_age,inage_image,age_image,p.status,material,p.create_time,f.type_name')
                ->alias('p')
                ->join('fa_class_ftype f','p.class_ftype_id = f.id')
                ->where($type)
                ->where($where)
                ->order($sort,$order)
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
        if(empty($params['type'])||empty($params['type_age_one'])||empty($params['type_age_two'])){
            $this->error('数据不全');
        }
        if(empty($params['inage_image'])){
            $inage=array();
            $num=$params['type_age_two']-$params['type_age_one']+1;
            for($i=0;$i<$num;$i++){
                $inage[$i]='https://cdn.wakoohome.top/02F9A120E4CA297341BDE94850DFCBCF.jpg';
            }
        }else{
            $inage=explode(',',$params['inage_image']);
        }
        if(empty($params['age_image'])){
            $image=array();
            $num=$params['type_age_two']-$params['type_age_one']+1;
            for($i=0;$i<$num;$i++){
                $image[$i]='https://cdn.wakoohome.top/02F9A120E4CA297341BDE94850DFCBCF.jpg';
            }
        }else{
            $image=explode(',',$params['age_image']);
        }
        if(empty($params['type_image'])){
            $params['type_image']='https://cdn.wakoohome.top/10F6D37BFBF12D997E0E5A7CA2CFE300.jpg';
        }
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        if($params['type_age_one']>$params['type_age_two']){
            $this->error('始年龄大于至年龄');
        }
        $data=array();
        if($params['type_age_one']==$params['type_age_two']){
            $data['class_ftype_id'] =$params['class_ftype_id'];
            $data['type']=$params['type'];
            $data['type_age']=$params['type_age_two'];
            $data['inage_image']=$params['inage_image'];
            $data['age_image']=$params['age_image'];
            $data['type_image']=$params['type_image'];
            $data['material']=$params['material'];
            $data['status']  = $params['status'];
            $data['create_time']=$params['create_time'];
            $data['update_time']=$params['update_time'];
        }else{
            $one=intval($params['type_age_one']);
            $two=intval($params['type_age_two']);
            for($age=$one;$age<$two+1;$age++){
                $data[$age-$one]['class_ftype_id'] =$params['class_ftype_id'];
                $data[$age-$one]['type']=$params['type'];
                $data[$age-$one]['type_age']=$age;
                $data[$age-$one]['inage_image']=$inage[$age-$one];
                $data[$age-$one]['age_image']=$image[$age-$one];
                $data[$age-$one]['type_image']=$params['type_image'];
                $data[$age-$one]['status']=$params['status'];
                $data[$age-$one]['material']=$params['material'];
                $data[$age-$one]['create_time']=$params['create_time'];
                $data[$age-$one]['update_time']=$params['update_time'];
            }
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
            if(count($data)!=count($data,1)){
                $result = $this->model->allowField(true)->saveAll($data);
            }else{
                $result = $this->model->allowField(true)->save($data);
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
            ->field('p.id,type,type_age,inage_image,age_image,p.delete_time,type_name')
            ->alias('p')
            ->join('fa_class_ftype f','p.class_ftype_id = f.id')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        foreach($list as &$row){
            $row->delete_time=date('Y-m-d H:i:s',$row->delete_time);
        }
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 审核年龄
     * @return string
     */
    public function age(){
        $age=$_POST;
        if(!empty($age['one']) && !empty($age['two'])){
            if($age['two']<$age['one']){
                $this->error('至年龄不可小于始年龄');
            }
            if(isset($age['type'])){
                $data=\app\admin\model\teach\ClassType::field('type,type_age')->select();
                foreach($data as $value){
                    if($age['type']==$value->type){
                        foreach($data as $v){
                            if($v['type_age']==$age['two'] && $v['type_age']==$age['one']) {
                                $this->error('同类型年龄已存在');
                            }else if($v['type_age']==$age['one']){
                                $this->error($age['one'].'岁已存在');
                            }else if($v['type_age']==$age['two']){
                                $this->error($age['two'].'岁已存在');
                            }
                        }
                    }
                }

            }
        }else if(empty($age['one'])){
                if(isset($age['type'])){
                    $data=\app\admin\model\teach\ClassType::field('type,type_age')->select();
                    foreach($data as $value){
                        if($age['type']==$value->type){
                            foreach($data as $v){
                                if($v['type_age']===intval($age['two'])) {
                                    $this->error('同类型年龄已存在');
                                }
                            }
                        }
                    }

                }
        }

       $this->success();
    }



    /**
     * 审核年龄
     * @return string
     */
    public function image(){
        $image=$_POST;
       if(empty($image['one'])||empty($image['two'])){
           $this->error('请先填写年龄');
       }
       $im=explode(',',$image['age_image']);
       $i=count($im);
       $num=$image['two']-$image['one']+1;
      if($i<$num){
          $number=$num-$i;
          $this->success('请再上传'.$number.'张图片');
      }else if($i>$num){
          $this->error('上传图片已超出年龄范围');
      }else if($i==$num){
          $this->success('上传完成');
      }
    }

    /**
     * 审核类型图片
     * @return string
     */
    public function typeImage(){
        $typeImage=$_POST;
        $image=\app\admin\model\teach\ClassType::field('type_image')
            ->where($typeImage)
            ->find();
        if(empty($image)){
            $image['type_image']='';
        }
        return json(['data'=>$image['type_image']]);
    }


    /**
     * 审核类型图片
     * @return string
     */
    public function status(){
        $status=$this->request->post('status');
        if(empty($status)){
            $this->error('请求为空');
        }
        if(empty($this->field('id')->where('status',$status)->find())){
            $this->success('');
        }else{
            $this->error('二级顺序已存在,请检查清楚');
        }
    }

}
