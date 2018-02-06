<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 17:15
 * @file ChatDataSearch.php
 */

namespace app\manage\model\search;

use think\Db;
use app\manage\service\UserService;
use think\facade\Session;
use think\Request;

class ChatDataSearch extends BaseSearch
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

        // 查找聊天内容
        $query = Db::name('chat_data d')
            ->join('user_department user_dept','(user_dept.dept_id1 = d.dept_id1 AND user_dept.dept_id2 IS NULL) OR (user_dept.dept_id1 = d.dept_id1 AND user_dept.dept_id2 = d.dept_id2 AND user_dept.user_id = d.user_id AND user_dept.dept_id2 IS NOT NULL)')
            ->leftJoin('user u','d.user_id = u.id')
            ->leftJoin('chat_group dg','dg.id = d.chat_group_id');
        // 业态筛选条件处理
        if(empty($default_dept2))
        {
            // 公司管理员
            $query->where('(((user_dept.user_id =:user1 AND user_dept.dept_id2 IS NULL) OR (d.user_id=:user2 AND user_dept.dept_id2 IS NOT NULL)) AND d.delete_time IS NULL) AND d.dept_id1 = :default_dept1',['user1' => $user_id,'user2'=>$user_id,'default_dept1' => $default_dept1['dept_id']]);
        }else {
            // 业务员
            $query->where('(((user_dept.user_id =:user1 AND user_dept.dept_id2 IS NULL) OR (d.user_id=:user2 AND user_dept.dept_id2 IS NOT NULL)) AND d.delete_time IS NULL) AND d.dept_id1 = :default_dept1 AND d.dept_id2 = :default_dept2',['user1' => $user_id,'user2'=>$user_id,'default_dept1' => $default_dept1['dept_id'],'default_dept2' => $default_dept2['dept_id']]);
        }
        $query->field(['d.*','u.real_name','u.username','dg.name as chat_group_name'])
              ->group('d.id');

        // 排序
        $this->orderBy($query, 'd');
        if ($query->getOptions('order') === null) {
            $query->order(['d.create_time' => 'DESC']);
        }

        // 检索条件
        $keyword = $request->param('keyword/s',null,'trim');
        if (!empty($keyword)) {
            $query->where('(d.reply like :keyword1 OR d.send like :keyword2 OR d.remark like :keyword3 OR u.username like :keyword4 OR u.real_name like :keyword5 OR dg.name like :keyword6)',[
                'keyword1' => '%'.$keyword.'%',
                'keyword2' => '%'.$keyword.'%',
                'keyword3' => '%'.$keyword.'%',
                'keyword4' => '%'.$keyword.'%',
                'keyword5' => '%'.$keyword.'%',
                'keyword6' => '%'.$keyword.'%',
            ]);//一个where条件，启用绑定特性，防止整句sql被OR打乱了先前的严格限定条件
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
