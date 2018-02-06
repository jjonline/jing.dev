<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-12 17:05
 * @file DeviceSearch.php
 */

namespace app\manage\model\search;

use app\manage\service\UserService;
use think\Db;
use think\facade\Session;
use think\Request;

class DeviceSearch extends BaseSearch
{
    /**
     * @var UserService
     */
    public $UserService;

    public function __construct(UserService $UserService)
    {
        $this->UserService = $UserService;
    }

    /**
     * 执行datatable插件的后端数据源动作
     * @param Request $request
     * @throws
     */
    public function search(Request $request)
    {
        // 初始化Param
        $this->initData($request);

        // 用户信息
        $isSuper = $this->UserService->isSupper();
        $user_id = Session::get('user_id');
        $default_dept1 = Session::get('default_dept1');
        $default_dept2 = Session::get('default_dept2');

        // Query对象
        /*
         * ---------------
         * 1、公司级别管理员部门表中没有dept_id2，无需理会用户
         * 2、业务员级别dept_id1、dept_id2均存在且必须是其所管控的device，亦既device表dept_id1、dept_id2与用户所属部门一致且用户要对应
         * ---------------
         */
        $query = Db::name('device d')
            ->join('user_department user_dept','(user_dept.dept_id1 = d.dept_id1 AND user_dept.dept_id2 IS NULL) OR (user_dept.dept_id1 = d.dept_id1 AND user_dept.dept_id2 = d.dept_id2 AND user_dept.user_id = d.user_id AND user_dept.dept_id2 IS NOT NULL)')
            ->leftJoin('user u','d.user_id = u.id')
            ->leftJoin('device_group dg','dg.id = d.device_group_id');
        // 业态筛选条件处理
        if(empty($default_dept2))
        {
            // 公司管理员
            $query->where('(((user_dept.user_id =:user1 AND user_dept.dept_id2 IS NULL) OR (d.user_id=:user2 AND user_dept.dept_id2 IS NOT NULL)) AND d.delete_time IS NULL) AND d.dept_id1 = :default_dept1',['user1' => $user_id,'user2'=>$user_id,'default_dept1' => $default_dept1['dept_id']]);
        }else {
            // 业务员
            $query->where('(((user_dept.user_id =:user1 AND user_dept.dept_id2 IS NULL) OR (d.user_id=:user2 AND user_dept.dept_id2 IS NOT NULL)) AND d.delete_time IS NULL) AND d.dept_id1 = :default_dept1 AND d.dept_id2 = :default_dept2',['user1' => $user_id,'user2'=>$user_id,'default_dept1' => $default_dept1['dept_id'],'default_dept2' => $default_dept2['dept_id']]);
        }
        $query->where(['d.delete_time' => null]) //仅显示未删除的数据
              ->field(['d.*','u.real_name','u.username','dg.name as device_group_name'])
              ->group('d.id');

        // 排序
        $this->orderBy($query, 'd');
        if ($query->getOptions('order') === null) {
            $query->order(['d.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword/s',null,'trim');
        if (!empty($keyword)) {
            $query->where('(d.network like :keyword1 OR d.device_no like :keyword2 OR d.device_imei like :keyword3 OR u.username like :keyword4 OR u.real_name like :keyword5 OR d.remark like :keyword6 OR dg.name like :keyword7)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
                'keyword3' => '%'.$keyword.'%',
                'keyword4' => '%'.$keyword.'%',
                'keyword5' => '%'.$keyword.'%',
                'keyword6' => '%'.$keyword.'%',
                'keyword7' => '%'.$keyword.'%',
            ]);//一个where条件，启用绑定特性，防止整句sql被OR打乱了先前的严格限定条件
        }

        // 按钮条件
        if($request->has('allocation'))
        {
            $allocation = $request->param('allocation/i');
            if($allocation)
            {
                $query->where('d.user_id','not null');
            }else {
                $query->where('d.user_id','null');
            }
        }
        // 检索时间范围
        $begin_date = $request->param('begin_date');
        $end_date   = $request->param('end_date');
        $begin_date = $begin_date ? date('Y-m-d H:i:s',strtotime($begin_date)) : null;
        $end_date   = $end_date ? date('Y-m-d H:i:s',strtotime($end_date)) : null;
        if(!empty($begin_date) && empty($end_date))
        {
            $query->where('d.create_time','>=',$begin_date);
        }
        if(empty($begin_date) && !empty($end_date))
        {
            $query->where('d.create_time','<=',$end_date);
        }
        if(!empty($begin_date) && !empty($end_date))
        {
            $query->where('d.create_time','>=',$begin_date);
            $query->where('d.create_time','<=',$end_date);
        }



        // 总数
        $countQuery = clone $query;
        $this->totalCount = $countQuery->count();

        // 数据
        $this->results = $query->limit($this->start, $this->length)->select()->toArray();

        //dump($query->getLastSql());exit;

        // 结果集
        return $this->queryData();
    }
}
