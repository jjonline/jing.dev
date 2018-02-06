<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-12 14:13
 * @file BaseSearch.php
 */

namespace app\manage\model\search;

use think\db\Query;
use think\Request;

class BaseSearch
{
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
     * @var string
     */
    public $pageError;

    /**
     * 初始化对象数据
     * @param Request $request
     */
    public function initData(Request $request)
    {
        $this->draw   = $request->param('draw/i');
        $this->columns= $request->param('columns/a');
        $this->order  = $request->param('order/a');
        $this->start  = $request->param('start/i');
        $this->length = $request->param('length/i');
    }

    /**
     * 返回查询的数据
     * @return array
     */
    public function queryData()
    {
        $result = [
            'draw' => $this->draw,
            'recordsTotal' => $this->totalCount,
            'recordsFiltered' => $this->totalCount,
            'data' => $this->results,
        ];
        if ($this->pageError) {
            $result['error'] = $this->pageError;
        }
        return $result;
    }

    /**
     * 执行排序
     * @param Query $query Query查询对象
     * @param string $table_alias 排序的字段名加上的table表的别名
     */
    public function orderBy(Query &$query,$table_alias = '')
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
}