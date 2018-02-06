<?php
/**
 * 部门列表数据源管理模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-12 14:12
 * @file DepartmentSearch.php
 */

namespace app\manage\model\search;

use app\manage\service\UserService;
use think\Db;
use think\facade\Session;
use think\Request;

class DepartmentSearch extends BaseSearch
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
     * @return []
     */
    public function search(Request $request)
    {
        // 初始化Param
        $this->initData($request);

        // Query对象
        $query = Db::name('department d1')
               ->leftJoin('department d2','d1.parent_id = d2.id')
               ->field(['d1.*','d2.name as parent_name']);

        // 排序
        $this->orderBy($query,'d1');
        if ($query->getOptions('order') === null) {
            $query->order(['d1.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword',null,'trim');
        if(!empty($keyword))
        {
            $query->where('(d1.name like :keyword1 OR d2.name like :keyword2)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
            ]);
        }
        if($request->param('level'))
        {
            $query->where(['d1.level' => $request->param('level/i')]);
        }

        // 总数
        $countQuery       = clone $query;
        $this->totalCount = $countQuery->count();

        // 数据
        $this->results    = $query->limit($this->start,$this->length)->select();

        // 结果集
        return $this->queryData();
    }

    /**
     * 公司管理员查看部门列表
     * @param Request $request
     * @throws
     * @return []
     */
    public function searchMineDepartment(Request $request)
    {
        // 初始化Param
        $this->initData($request);

        $default_dept1 = Session::get('default_dept1');
        //$default_dept2 = Session::get('default_dept2');

        // Query对象
        $query = Db::name('department d1')
               ->leftJoin('user_department ud','ud.dept_id2 = d1.id AND ud.dept_id2 IS NOT NULL')
               ->leftJoin('user u','ud.dept_id2 = d1.id AND u.id = ud.user_id')
               ->field('d1.name,d1.id,d1.sort,d1.remark,d1.create_time,count(u.id) as total')
               ->where('d1.parent_id',$default_dept1['dept_id'])
               ->group('d1.id');

        // 排序
        $this->orderBy($query,'d1');
        if ($query->getOptions('order') === null) {
            $query->order(['d1.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword',null,'trim');
        if(!empty($keyword))
        {
            $query->where('(d1.name like :keyword1 OR d1.remark like :keyword2)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
            ]);
        }

        // 检索时间范围
        $begin_date = $request->param('begin_date');
        $end_date   = $request->param('end_date');
        $begin_date = $begin_date ? date('Y-m-d H:i:s',strtotime($begin_date)) : null;
        $end_date   = $end_date ? date('Y-m-d H:i:s',strtotime($end_date)) : null;
        if(!empty($begin_date) && empty($end_date))
        {
            $query->where('d1.create_time','>=',$begin_date);
        }
        if(empty($begin_date) && !empty($end_date))
        {
            $query->where('d1.create_time','<=',$end_date);
        }
        if(!empty($begin_date) && !empty($end_date))
        {
            $query->where('d1.create_time','>=',$begin_date);
            $query->where('d1.create_time','<=',$end_date);
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
