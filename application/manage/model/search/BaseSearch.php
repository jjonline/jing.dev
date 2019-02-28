<?php
/**
 * DataTable检索基类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 11:19:00
 * @file BaseSearch.php
 */

namespace app\manage\model\search;

use app\common\model\Department;
use app\common\model\Menu;
use think\db\Query;
use think\Exception;
use think\Request;

class BaseSearch
{
    /**
     * @var Request
     */
    public $request;
    /**
     * @var
     */
    public $draw;
    /**
     * @var array 列集合
     */
    public $columns = [];
    /**
     * @var array 排序列表
     */
    public $order = [];
    /*
     * @var int 偏移量
     */
    public $start = 0;
    /*
     * @var int 数据条数
     */
    public $length = 10;
    /**
     * @var int
     */
    public $totalCount = 0;
    /**
     * @var array
     */
    public $results = [];
    /**
     * @var string 错误信息
     */
    public $pageError;
    /**
     * @var string 处理添加百分号后的[若有]检索关键词
     */
    public $keyword;
    /**
     * @var string 原始检索关键词
     */
    public $keyword_origin;
    /**
     * @var int 可能的按部门查看数据的部门参数
     */
    public $dept_id;

    public function __construct()
    {
        $this->request        = app('request');
        // 初始化dataTable传递过来的相关Get变量参数[post亦可]
        $this->draw           = $this->request->param('draw/i');
        $this->columns        = $this->request->param('columns/a');
        $this->order          = $this->request->param('order/a');
        $this->start          = $this->request->param('start/i');
        $this->length         = $this->request->param('length/i');
        $this->dept_id        = $this->request->param('dept_id/i', 0);
        $this->keyword_origin = $this->request->param('keyword', null);
        $this->keyword        = $this->keyword_origin ? '%' . $this->keyword_origin . '%' : null;
    }

    /**
     * 执行排序
     * @param Query $query Query查询对象
     * @param string $table_alias 排序的字段名加上的table表的别名
     */
    protected function orderBy(Query &$query, $table_alias = '')
    {
        foreach ($this->order as $order) {
            if (isset($this->columns[$order['column']]['data'])) {
                $columnName = $this->columns[$order['column']]['data'];
                if ($columnName == 'operate') {
                    continue;
                }
                // 附加别名
                if (!empty($table_alias)) {
                    $columnName = $table_alias.'.'.$columnName;
                }
                $sort = $order['dir'] == 'desc' ? 'DESC' : 'ASC';
                $query->order([$columnName => $sort]);
            }
        }
    }

    /**
     * 关键词检索
     * @param Query $query   引用模式的Query查询对象
     * @param array $columns 待检索的字段数组，可带别名
     * @param null $keyword  检索的关键词，关键词构造函数已自动补充好两边的百分号模糊条件，无需再额外补充百分号
     */
    protected function keywordSearch(Query &$query, $columns = array(), $keyword = null)
    {
        if (!empty($keyword)) {
            $like = [];
            $bind = [];
            foreach ($columns as $key => $value) {
                // 超过10个字符时使用数字索引导致bug
                // 最多支持52个字段同时检索，绰绰有余，超过10个检索字段性能就已经很低下了
                $_key                = chr($key + 65);
                $like[]              = $value . ' LIKE :key' . $_key;
                $bind['key' . $_key] = $keyword;
            }
            $query->where(implode(' OR ', $like), $bind);
        }
    }

    /**
     * 时间范围检索
     * @param Query $query       引用模式的Query查询对象
     * @param string $column     待检索的单个datetime类型的字段，可带别名
     * @param string $begin_date 指定的检索的开始时间，不传则获取变量中名为begin_date的值
     * @param string $end_date   指定的减速偶的结束时间，不传则获取变量中名为end_date的值
     */
    protected function dateTimeSearch(Query &$query, $column, $begin_date = null, $end_date = null)
    {
        if (is_null($begin_date)) {
            $begin_date = $this->request->param('begin_date');
        }
        if (is_null($end_date)) {
            $end_date = $this->request->param('end_date');
        }
        $begin_date = $begin_date ? date('Y-m-d H:i:s', strtotime($begin_date)) : null;
        $end_date   = $end_date ? date('Y-m-d H:i:s', strtotime($end_date)) : null;
        if (!empty($begin_date) && empty($end_date)) {
            $query->where($column, '>=', $begin_date);
        }
        if (empty($begin_date) && !empty($end_date)) {
            $query->where($column, '<=', $end_date);
        }
        if (!empty($begin_date) && !empty($end_date)) {
            $query->where($column, '>=', $begin_date);
            $query->where($column, '<=', $end_date);
        }
    }

    /**
     * 范围检索
     * @param Query  $query       引用模式的Query查询对象
     * @param string $column      待检索的单个datetime类型的字段，可带别名
     * @param string $begin_range 检索开始值，可空
     * @param string $end_range   检索结束值，可空
     */
    protected function rangeSearch(Query &$query, $column, $begin_range = null, $end_range = null)
    {
        if (empty($begin_range) && empty($end_range)) {
            return;
        }
        if (!empty($begin_range) && empty($end_range)) {
            $query->where($column, '>=', $begin_range);
        }
        if (empty($begin_range) && !empty($end_range)) {
            $query->where($column, '<=', $end_range);
        }
        if (!empty($begin_range) && !empty($end_range)) {
            $query->where($column, '>=', $begin_range);
            $query->where($column, '<=', $end_range);
        }
    }

    /**
     * 数据范围限制和部门检索融合方法
     * ----
     * 1、有数据范围限定的执行范围限定逻辑
     * 2、有部门检索需求的执行部门及子部门数据检索
     * ----
     * @param Query  $query
     * @param string $dept_column  当前被检索的表的部门字段名称，一般是dept_id，可能会有别名
     * @param string $user_column  当前数据表的用户ID字段，可能会有别名
     * @param array  $user_info    当前登录用户信息，由控制器传递过来
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function permissionLimitOrDeptSearch(Query &$query, $dept_column, $user_column, $user_info = [])
    {
        $search_dept_id = $this->dept_id; // 可能的本次检索的部门参数
        // 有数据范围限制 + 部门检索条件的处理
        if ($user_info['menu_auth']['is_permissions']) {
            // 全部数据--部门及子部门检索
            if (Menu::PERMISSION_SUPER == $user_info['menu_auth']) {
                // 全部数据且没有部门检索要求返回
                if (empty($search_dept_id)) {
                    return;
                }
                // 获取拟查找部门的所有子部门
                $departModel   = new Department();
                $child_dept    = $departModel->getChildDeptByParentId($search_dept_id);
                // 检索的部门id索引数组
                $search_dept   = $child_dept ?: [];
                $search_dept[] = $search_dept_id;
                // 去重+重排数字索引后按部门检索
                $query->where($dept_column, 'IN', array_merge(array_unique($search_dept)));
            }
            // 部门及子部门数据范围--部门及子部门限定条件下的检索指定部门数据
            if (Menu::PERMISSION_LEADER == $user_info['menu_auth']) {
                // 部门及子部门数据范围，没有检索部门条件，将用户所属部门设置成部门检索条件
                if (empty($search_dept_id)) {
                    $search_dept_id = $user_info['dept_id'];
                }
                // 没有该部门查看权限，抛异常终止
                if (!in_array($search_dept_id, $user_info['dept_auth']['dept_id_vector'])) {
                    throw new Exception('您的权限不能查看该部门数据');
                }
                // 获取拟查找部门的所有子部门
                $departModel   = new Department();
                $child_dept    = $departModel->getChildDeptByParentId($search_dept_id);
                // 检索的部门id索引数组
                $search_dept   = $child_dept ?: [];
                $search_dept[] = $search_dept_id;
                // 去重+重排数字索引后按部门检索
                $query->where($dept_column, 'IN', array_merge(array_unique($search_dept)));
            }
            // 个人数据--只能查看个人数据，用户ID条件必选
            if (Menu::PERMISSION_STAFF == $user_info['menu_auth']) {
                if (empty($search_dept_id)) {
                    /**
                     * 没有部门检索
                     */
                    $query->where($user_column, $user_info['id']);
                } else {
                    /**
                     * 子部门可能存在个人数据，按部门筛选个人数据的需求
                     */
                    $query->where(function (Query $subQuery) use (
                        $search_dept_id,
                        $dept_column,
                        $user_column,
                        $user_info
                    ) {
                        $subQuery->where($user_column, $user_info['id'])
                                 ->where($dept_column, $search_dept_id);
                    });
                }
            }
            // 访客权限，不允许查看任何数据
            if (Menu::PERMISSION_GUEST == $user_info['menu_auth']) {
                throw new Exception('您没有查看数据的权限');
            }
            return;
        }

        /**
         * 菜单中没有数据范围限制，但可能需要按部门来筛选数据
         */
        // 没有部门检索条件 返回
        if (empty($search_dept_id)) {
            return;
        }
        // 获取拟查找部门的所有子部门
        $departModel   = new Department();
        $child_dept    = $departModel->getChildDeptByParentId($search_dept_id);
        // 检索的部门id索引数组并检索检索部门
        $search_dept   = $child_dept ?: [];
        $search_dept[] = $search_dept_id;
        // 去重+重排数字索引后按部门检索
        $query->where($dept_column, 'IN', array_merge(array_unique($search_dept)));
    }

    /**
     * 处理返回查询的数据结构数组
     * @return array
     */
    protected function handleResult()
    {
        $result = [
            'draw'            => $this->draw,
            'recordsTotal'    => $this->totalCount,
            'recordsFiltered' => $this->totalCount,
            'data'            => $this->results,
        ];
        if ($this->pageError) {
            $result['error'] = $this->pageError;
        }
        return $result;
    }
}
