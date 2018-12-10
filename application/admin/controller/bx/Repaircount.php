<?php

namespace app\admin\controller\bx;
use think\Db;
use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Repaircount extends Backend
{
    
    /**
     * RepairList模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('RepairList');
        $this -> control_id = Db::view('auth_group') 
                    -> view('auth_group_access','uid,group_id','auth_group.id = auth_group_access.group_id')
                    -> where('name','报修管理员') 
                    -> find()['uid'];
        $this->view->assign("statusList", $this->model->getStatusList());
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
        //获取当前管理员id的方法
        $now_admin_id = $this->auth->id;
        //设置过滤方法
        $status_params= $this->request->param();
        $this->view->assign('status_params',$status_params['status']);
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $status = $this->request->param()['status'];
            if($status == 'all'){
                
                    $total = $this->model
                            ->with('getname,getaddress,getcompany,gettypename')
                            ->where("status",$status)
                            ->where('distributed_id',$now_admin_id)
                            ->where($where)
                            ->order($sort, $order)
                            ->count();

                    $list = $this->model
                            ->with('getname,getaddress,getcompany,gettypename')
                            ->where("status",$status)
                            ->where('distributed_id',$now_admin_id)
                            ->where($where)
                            ->order($sort, $order)
                            ->limit($offset, $limit)
                            ->select();
            }            
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);           
            return json($result);
        }
         return $this->view->fetch(); 
    }
    
    //受理方法
    public function accept($ids ){
        $admin_id = $this->auth->id;
        $res = $this->model->accept($ids, $admin_id);
        if($res){
            return $this->success("受理成功，请尽快指派单位");
        } else {
            return $this->error("受理失败，请确认数据");
        }
    }

    public function finish($ids){
        $res = $this->model->finish($ids);
        if ($res) {
            $this->success("该任务已经完成");
        }
    }
    //驳回
    public function refuse($ids ){
        if ($this->request->isPost()){
            $content = $this->request->post()['refuse_content'];
            $res = $this->model->refuse($ids, $content);
            return $res;
        }else{
            $this->error('请求失败');
        }
    }
    //分配单位
    public function distribute($ids)
    {
        if ($this->request->isPost()){
            $company_id = $this->request->post()['company_id'];
            $res = $this->model->distribute($ids, $company_id);
            return $res;
        } else {
            $this -> error('请求错误');
        }
    }
    //重新指派单位
    public function redistribute($ids){
        $time = time();
        $now_admin_id = $this->auth->id;
        if ($now_admin_id == $this -> control_id || $now_admin_id == 1) {
            $res = $this->model->where('id ='.$ids)->update([
                    'status' => 'accepted', 
                    'distributed_id'=> '',
                    'dispatched_id' => '',
                    'dispatched_time' => '',
                ]);
            $this->model->where('id = '.$ids)->update(['accepted_time' => null]);
            return $res;
        } else {
            $res = $this->model->where('id ='.$ids)->update([
                    'status' => 'distributed', 
                    'dispatched_id' => '',
                    'dispatched_time' => '',
                ]);
            return $res;
        }
    }
    //分配人员
    public function dispatch($ids){  
        if ($this->request->isPost()){
            $worker_id = $this->request->post()['worker_id'];
            $res = $this->model->dispatch($ids, $worker_id);
            return $res;
        }else{
           $this -> error('请求错误');
        }         
    }


    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isAjax())
        {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
        //处理数据

        $row['new_time'] = $this -> getLastOperationTime($row);
        $data = $this->model->redata($row);
        //根据不同的状态查找不同的所需值
        switch ($data['status']) {
            case 'waited':
                $data['image'] = json_decode($data['image']);
                $this->view->assign("row", $data->toArray());
                return $this->view->fetch();
                break;
            case 'accepted':
                $data['image'] = json_decode($data['image']);
                //获取受理人
                $admin_name = Db::name('admin')->where('id',$data['admin_id'])->find();
                $data['admin_name'] = $admin_name['nickname'];
                $com_id = Db::name('auth_group') -> where('name','报修单位') -> field('id') -> find()['id'];
                //获取公司名称
                $company = Db::view('auth_group_access') 
                            -> view('admin','nickname,id','auth_group_access.uid = admin.id')
                            -> where("group_id = $com_id") 
                            -> select();

                $data['company'] = $company;
                $this->view->assign("row", $data->toArray());
                return $this->view->fetch();
                break;
            case 'distributed':
                $admin_name = Db::name('admin')->where('id',$data['admin_id'])->find();
                $company_name = Db::name('admin')->where('id',$data['distributed_id'])->find();
                $workerModel = model('RepairWorker');
                $worker = $workerModel->where('distributed_id',$this->auth->id)->select();
                $data['admin_name'] = $admin_name['nickname'];
                $data['image'] = json_decode($data['image']);
                $data['worker'] = $worker;
                $data['company_name'] = $company_name['nickname'];
                //此处返回当前登录的管理员id，如果与数据中分配的id一致才能进行分配工人
                $data['now_admin_id'] = $this->auth->id;
                $this->view->assign("row", $data->toArray());
                return $this->view->fetch();
                break;
            case 'dispatched':
                $admin_name = Db::name('admin')->where('id',$data['admin_id'])->find();
                $company_name = Db::name('admin')->where('id',$data['distributed_id'])->find();
                $worker_info = Db::name('repair_worker')->where('id',$data['dispatched_id'])->find();
                $data['admin_name'] = $admin_name['nickname'];
                $data['worker_info'] = $worker_info; 
                $data['image'] = json_decode($data['image']);
                $data['company_name'] = $company_name['nickname'];
                $this->view->assign("row", $data->toArray());
                return $this->view->fetch();
                break;
            case 'finished':
                $admin_name = Db::name('admin')->where('id',$data['admin_id'])->find();
                $company_name = Db::name('admin')->where('id',$data['distributed_id'])->find();
                $worker_info = Db::name('repair_worker')->where('id',$data['dispatched_id'])->find();
                $data['admin_name'] = $admin_name['nickname'];
                $data['worker_info'] = $worker_info; 
                $data['image'] = json_decode($data['image']);
                $data['company_name'] = $company_name['nickname'];
                $this->view->assign("row", $data->toArray());
                return $this->view->fetch();
                break;
            default:
                $admin_name = Db::name('admin')->where('id',$data['admin_id'])->find();             
                $data['admin_name'] = $admin_name['nickname'];
                $data['image'] = json_decode($data['image']);
                $this->view->assign("row", $data->toArray());
                return $this->view->fetch();
                break;
        }
    }
    /**
     * 获取传入工单的最新操作时间
     */
    public function getLastOperationTime($listArray)
    {
        switch ($listArray['status']) {
            case 'waited':
                return $listArray['submit_time'];
                break;
            case 'accepted':
                return $listArray['accepted_time'];
                break;
            case 'distributed':
                return $listArray['distributed_time'];
                break;
            case 'dispatched':
                return $listArray['dispatched_time'];
                break;
            case 'finished':
                return $listArray['finished_time'];
                break;
            
            default:
                return $listArray['refused_time'];
                break;
        }
    }


}
