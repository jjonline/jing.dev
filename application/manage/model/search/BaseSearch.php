<?php
/**
 * DataTable检索基类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 11:19:00
 * @file BaseSearch.php
 */

namespace app\manage\model\search;

use think\db\Query;
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
     * @var string 检索关键词
     */
    public $keyword;

    public function __construct()
    {
        $this->request = app('request');
        // 初始化dataTable传递过来的相关Get变量参数[post亦可]
        $this->draw    = $this->request->param('draw/i');
        $this->columns = $this->request->param('columns/a');
        $this->order   = $this->request->param('order/a');
        $this->start   = $this->request->param('start/i');
        $this->length  = $this->request->param('length/i');
        $this->keyword = $this->request->param('keyword',null);
        $this->keyword = $this->keyword ? '%'.$this->keyword.'%' : null;
    }

    /**
     * 执行排序
     * @param Query $query Query查询对象
     * @param string $table_alias 排序的字段名加上的table表的别名
     */
    protected function orderBy(Query &$query,$table_alias = '')
    {
        foreach ($this->order as $order) {
            if(isset($this->columns[$order['column']]['data']))
            {
                $columnName = $this->columns[$order['column']]['data'];
                if($columnName == 'operate')
                {
                    continue;
                }
                // 附加别名
                if(!empty($table_alias))
                {
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
    protected function keywordSearch(Query &$query,$columns = array(),$keyword = null)
    {
        if(!empty($keyword))
        {
            $like = [];
            $bind = [];
            foreach ($columns as $key => $value)
            {
                $like[]            = $value.' LIKE :key'.$key;
                $bind['key'.$key]  = $keyword;
            }
            $query->where(implode(' OR ',$like),$bind);
        }
    }

    /**
     * 时间范围检索
     * @param Query $query       引用模式的Query查询对象
     * @param string $column     待检索的单个datetime类型的字段，可带别名
     * @param string $begin_date 指定的检索的开始时间
     * @param string $end_date   指定的减速偶的结束时间
     */
    protected function dateTimeSearch(Query &$query,$column,$begin_date,$end_date)
    {
        $begin_date = $begin_date ? date('Y-m-d H:i:s',strtotime($begin_date)) : null;
        $end_date   = $end_date ? date('Y-m-d H:i:s',strtotime($end_date)) : null;
        if(!empty($begin_date) && empty($end_date))
        {
            $query->where($column,'>=',$begin_date);
        }
        if(empty($begin_date) && !empty($end_date))
        {
            $query->where($column,'<=',$end_date);
        }
        if(!empty($begin_date) && !empty($end_date)) {
            $query->where($column, '>=', $begin_date);
            $query->where($column, '<=', $end_date);
        }
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