<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-12 20:35
 * @file UserSearch.php
 */

namespace app\manage\model\search;

use app\common\helpers\ArrayHelper;
use think\Db;
use think\Request;

class UserSearch extends BaseSearch
{
    /**
     * 执行datatable插件的后端数据源动作
     * @param Request $request
     * @throws
     */
    public function search(Request $request)
    {
        // 初始化Param
        $this->initData($request);

        // Query对象
        $query = Db::name('user u')
            ->leftJoin('user_department user_dept','u.id = user_dept.user_id')
            ->leftJoin('department dept', 'dept.id = user_dept.dept_id1 OR dept.id = user_dept.dept_id2')
            ->leftJoin('user_role u_role','u_role.user_id = u.id')
            ->leftJoin('role role','u_role.role_name = role.name')
            ->field(['u.id','u.real_name', 'u.username','u.phone','u.enabled','u.create_time','dept.name as dept_name','role.name as role_name,u.device_quota']);
            //->group('u.id');

        // 排序
        $this->orderBy($query, 'u');
        if ($query->getOptions('order') === null) {
            $query->order(['u.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword',null,'trim');
        if (!empty($keyword)) {
            $query->where('(u.username like :keyword1 OR u.phone like :keyword2 OR u.real_name like :keyword3 OR dept.name like :keyword4)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
                'keyword3' => '%'.$keyword.'%',
                'keyword4' => '%'.$keyword.'%',
            ]);
        }
        if($request->has('enabled'))
        {
            $enabled = $request->param('enabled/i');
            $query->where(['enabled' => $enabled]);
        }

        // 总数
        $countQuery = clone $query;
        $this->totalCount = $countQuery->group('u.id')->count();

        // 数据
        $this->results = $query->limit($this->start, $this->length)->select()->toArray();
        // 多角色、多部门数据合并处理
        if(!empty($this->results))
        {
            $_result  = ArrayHelper::group($this->results,'id');
            $result   = [];
            foreach($_result as $key => $value)
            {
                $_role = [];
                $_dept = [];
                foreach ($value as $item) {
                    $_role[] = $item['role_name'];
                    $_dept[] = $item['dept_name'];
                }
                $item['role_name'] = implode('、',array_unique($_role));
                $item['dept_name'] = implode('、',array_unique($_dept));
                $result[]      = $item;
            }
            $this->results = $result;
        }


        // 结果集
        return $this->queryData();
    }

    /**
     * 公司管理员检索业务员列表
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchMineUser(Request $request)
    {
        // 初始化Param
        $this->initData($request);

        $default_dept1 = session('default_dept1');
        //$default_dept2 = Session::get('default_dept2');

        // Query对象
        $query = Db::name('user u')
            ->leftJoin('user_department ud','ud.user_id = u.id AND ud.dept_id2 IS NOT NULL')
            ->join('department dp','dp.id = ud.dept_id2')
            ->field('u.device_quota,u.username,u.id,u.remark,u.create_time,u.real_name,dp.name as dept_name')
            ->where('ud.dept_id1',$default_dept1['dept_id'])
            ->group('u.id');

        // 排序
        $this->orderBy($query,'u');
        if ($query->getOptions('order') === null) {
            $query->order(['u.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword',null,'trim');
        if(!empty($keyword))
        {
            $query->where('(u.username like :keyword1 OR u.remark like :keyword2 OR u.real_name like :keyword3)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
                'keyword3' => '%'.$keyword.'%',
            ]);
        }

        // 检索时间范围
        $begin_date = $request->param('begin_date');
        $end_date   = $request->param('end_date');
        $begin_date = $begin_date ? date('Y-m-d H:i:s',strtotime($begin_date)) : null;
        $end_date   = $end_date ? date('Y-m-d H:i:s',strtotime($end_date)) : null;
        if(!empty($begin_date) && empty($end_date))
        {
            $query->where('u.create_time','>=',$begin_date);
        }
        if(empty($begin_date) && !empty($end_date))
        {
            $query->where('u.create_time','<=',$end_date);
        }
        if(!empty($begin_date) && !empty($end_date))
        {
            $query->where('u.create_time','>=',$begin_date);
            $query->where('u.create_time','<=',$end_date);
        }

        // 总数
        $countQuery       = clone $query;
        $this->totalCount = $countQuery->count();

        // 数据
        $this->results    = $query->limit($this->start,$this->length)->select();

        // 结果集
        return $this->queryData();
    }
}