<?php

namespace app\admin\controller\teach;

use app\admin\model\teach\ClassBuy as BuyModel;
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
class ClassBuy extends Backend
{

    /**
     * ClassBuy模型对象
     * @var \app\admin\model\teach\BuyModel
     */
    protected $model = null;

    
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new BuyModel;
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
           
            //主表查询条件
            $where     = array();
            foreach($static['where'] as $val){
                switch($val[0]){
                    case 'store_name':
                        $val[0]='c.'.$val[0];
                        $where[$val[0]]=[$val[1],$val[2]];
                        break;
                    case 'type':
                        $origin=strpos($val[2],'-');
                        $val[0]='p.'.$val[0];
                        if(empty($origin)){
                            $age=preg_replace('/[^0-9]/',"",$val[2]);
                            if($age){
                                $where['p.type_age']=['=',intval($age)];
                            }else{
                                $finish=strrpos($val[2],'%');
                                $str=substr($val[2],0,$origin) . substr($val[2],$finish,strlen($val[2]));
                                $where[$val[0]]=[$val[1],$str];
                            }
                        }else{
                            $age=preg_replace('/[^0-9]/',"",$val[2]);
                            $where['p.type_age']=['=',intval($age)];
                            $finish=strrpos($val[2],'%');
                            $str=substr($val[2],0,$origin) . substr($val[2],$finish,strlen($val[2]));
                            $where[$val[0]]=[$val[1],$str];
                        }
                        break;
                    case 'state':
                        $val[0]='b.'.$val[0];
                        $where[$val[0]]=[$val[1],$val[2]];
                        break;
                    case 'create_time':
                        $val[0]='b.'.$val[0];
                        $where[$val[0]]=[$val[1],$val[2]];
                        break;
                    default:
                        $val[0]='u.'.$val[0];
                        $where[$val[0]]=[$val[1],$val[2]]; 
                }
            }
            $list=$this->model
                ->field('b.id,state,b.create_time,u.classname,u.iphone,u.sex,u.age,p.type,p.type_age,c.store_name')
                ->alias('b')
                ->join('fa_class_user u','b.class_user_id = u.id')
                ->join('fa_class_type p','b.class_type_id = p.id')
                ->join('fa_class_belong l','b.class_belong_id = l.id')
                ->join('fa_class_campus c','l.class_campus_id = c.id')
                ->where($where)
                ->order($sort,$order)
                ->paginate($limit);
            return json(['rows'=>$list->items(),'total'=>$list->total()]);
        }
        return $this->view->fetch();
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
        $row = $this->model
            ->with('buy_user,classtype')
            ->field('id,state,class_user_id,class_type_id,class_belong_id,create_time,update_time')
            ->where('id',$ids)->find();
        $arr=array('待审核','审核成功','审核失败');
        $key=array('待审核','审核成功','审核失败');
        $arr=array_combine($key,$arr);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('arr', $arr);
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        switch($params['state']){
            case '待审核':
                $params['state']=0;
                break;
            case '审核成功':
                $params['state']=1;
                break;
            case '审核失败':
                $params['state']=2;
                break;
        }
        $rate=model('app\admin\model\teach\ClassRate');
        $watch=model('app\admin\model\teach\ClassWatch');
        $video=model('app\admin\model\teach\ClassVideo');
        $vision=$video->field('id')
                      ->where('class_type_id',$row->class_type_id)
                      ->select();
        if(empty($vision)){
            $this->error('所买课程视频为空');
        }
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
            if($params['state']===1){
                $rate->create([
                    'class_buy_id' => $row->id
                ]);
                foreach($vision as $value){
                    $list[]= array('user_id'=>$row->class_user_id,'video_id'=>$value->id);
                }
               $watch->saveAll($list,false);
            }
            if($params['state']===2||$params['state']===0){
                $rate->destroy(['class_buy_id'=>$row->id]);
                $watch->destroy(['user_id'=>$row->class_user_id]);
            }
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
     * 真实删除
     *
     * @param $ids
     * @return void
     */
    public function destroy($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $this->model->where($pk, 'in', $ids);
        
        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->onlyTrashed()->select();
            foreach ($list as $item) {
                $count += $item->delete(true);
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

}
