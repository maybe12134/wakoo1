<?php

namespace app\admin\controller\teach;

use app\common\controller\Backend;
use app\admin\library\Auth;
use think\Session;
use app\admin\model\teach\ClassType;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class ClassOrder extends Backend
{

    /**
     * ClassOrder模型对象
     * @var \app\admin\model\teach\ClassOrder
     */
    protected $model = null;
    protected $campusId=NULL;


    public function _initialize()
    {
        parent::_initialize();
        $auth=new Auth;
        $this->campusId = $auth->getCampusId(Session::get('admin.'.'campus_id'));
        $this->model = new \app\admin\model\teach\ClassOrder;

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
           
            if(is_array($this->campusId)){
               $campus=$this->campusId['campus_id'];
            }else{
                $campus=array($this->campusId);
            }
            $list = $this->model
                    ->alias('o')
                    ->field('o.id,class_campus_id,class_type_id,money,number,qty,rqty,expiry,o.create_time,o.update_time,c.store_name,t.type,t.type_age')
                    ->join('fa_class_campus c','c.id=o.class_campus_id')
                    ->join('fa_class_type t','t.id=o.class_type_id')
                    ->where($where)
                    ->where('o.class_campus_id','in',$campus)
                    ->order($sort, $order)
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
        $params['rqty']=$params['number'];
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
        $type=ClassType::field('type,type_age')->where('id',$row->class_type_id)->find();
        $row->type=$type->type.'-'.$type->type_age;
        $row->aexpiry=array('过期'=>'过期','生效'=>'生效');
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
        if($params['expiry']=='过期'){
            $params['expiry']=0;
        }else{
            $params['expiry']=1;
        }
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

    
}
