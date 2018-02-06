<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-25 17:28
 * @file StaticResourceSearch.php
 */

namespace app\manage\model\search;

use think\Db;
use think\facade\Session;
use think\Request;

class StaticResourceSearch extends BaseSearch
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

        // 用户信息
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
        $query = Db::name('static_resource sr')
            ->join('user_department user_dept','(user_dept.dept_id1 = sr.dept_id1 AND user_dept.dept_id2 IS NULL) OR (user_dept.dept_id1 = sr.dept_id1 AND user_dept.dept_id2 = sr.dept_id2 AND user_dept.user_id = sr.user_id AND user_dept.dept_id2 IS NOT NULL)')
            ->leftJoin('user u','sr.user_id = u.id');
        // 业态筛选条件处理
        if(empty($default_dept2))
        {
            // 公司管理员
            $query->where('(((user_dept.user_id =:user1 AND user_dept.dept_id2 IS NULL) OR (sr.user_id=:user2 AND user_dept.dept_id2 IS NOT NULL)) AND sr.delete_time IS NULL) AND sr.dept_id1 = :default_dept1',['user1' => $user_id,'user2'=>$user_id,'default_dept1' => $default_dept1['dept_id']]);
        }else {
            // 业务员
            $query->where('(((user_dept.user_id =:user1 AND user_dept.dept_id2 IS NULL) OR (sr.user_id=:user2 AND user_dept.dept_id2 IS NOT NULL)) AND sr.delete_time IS NULL) AND sr.dept_id1 = :default_dept1 AND sr.dept_id2 = :default_dept2',['user1' => $user_id,'user2'=>$user_id,'default_dept1' => $default_dept1['dept_id'],'default_dept2' => $default_dept2['dept_id']]);
        }
        $query->field(['sr.*','u.real_name','u.username'])
            ->group('sr.id');

        // 排序
        $this->orderBy($query, 'sr');
        if ($query->getOptions('order') === null) {
            $query->order(['sr.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword/s',null,'trim');
        if (!empty($keyword)) {
            $query->where('(sr.tag like :keyword1 OR sr.name like :keyword2 OR u.real_name like :keyword3)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
                'keyword3' => '%'.$keyword.'%',
            ]);//一个where条件，启用绑定特性，防止整句sql被OR打乱了先前的严格限定条件
        }

        // 检索时间范围
        $begin_date = $request->param('begin_date');
        $end_date   = $request->param('end_date');
        $begin_date = $begin_date ? date('Y-m-d H:i:s',strtotime($begin_date)) : null;
        $end_date   = $end_date ? date('Y-m-d H:i:s',strtotime($end_date)) : null;
        if(!empty($begin_date) && empty($end_date))
        {
            $query->where('sr.create_time','>=',$begin_date);
        }
        if(empty($begin_date) && !empty($end_date))
        {
            $query->where('sr.create_time','<=',$end_date);
        }
        if(!empty($begin_date) && !empty($end_date))
        {
            $query->where('sr.create_time','>=',$begin_date);
            $query->where('sr.create_time','<=',$end_date);
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
