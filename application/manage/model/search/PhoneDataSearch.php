<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-18 10:38
 * @file AccountDataSearch.php
 */

namespace app\manage\model\search;

use app\common\helpers\FilterValidHelper;
use app\common\model\PhoneData;
use think\Db;
use think\facade\Session;
use think\Request;

class PhoneDataSearch extends BaseSearch
{
    /**
     * @var PhoneData
     */
    public $PhoneData;

    public function __construct(PhoneData $phoneData)
    {
        $this->PhoneData = $phoneData;
    }

    /**
     * 仅公司管理员可查看
     * 执行datatable插件的后端数据源动作
     * @param Request $request
     * @throws
     */
    public function search(Request $request)
    {
        // 初始化Param
        $this->initData($request);

        // 用户信息
        $user_id       = Session::get('user_id');//管理员只需要依据公司、业态即可查看数据
        $default_dept1 = Session::get('default_dept1');
        $default_dept2 = Session::get('default_dept2');

        // 导入的好友列表
        $query = Db::name('phone_data d')
               ->leftJoin('user u','d.user_id = u.id')
               ->where(['d.dept_id1' => $default_dept1['dept_id']]);//限定公司

        // 筛选业态
        if(!empty($default_dept2))
        {
            $query->where(['d.dept_id2' => $default_dept2['dept_id']]);
        }

        $query->field(['d.*','u.real_name'])
              ->group('d.id');

        // 排序
        $this->orderBy($query, 'd');
        if ($query->getOptions('order') === null) {
            $query->order(['d.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword',null,'trim');
        if (!empty($keyword)) {
            $query->where('(d.phone like :keyword1 OR d.name like :keyword2 OR u.real_name like :keyword3)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
                'keyword3' => '%'.$keyword.'%'
            ]);
        }

        // 指定使用状态
        if($request->has('is_use','get'))
        {
            $query->where('d.is_use', '=', intval($request->get('is_use')));
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
        $results = $query->limit($this->start, $this->length)->select()->toArray();

        // 隐藏手机号细节
        foreach ($results as $key => $item) {
            $results[$key]['phone'] = FilterValidHelper::hide_name($item['phone']);
        }
        $this->results = $results;

        // 结果集
        return $this->queryData();
    }
}
