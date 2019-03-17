<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9
 * Time: 16:54
 */

namespace app\manage\service;

use app\common\model\Department;
use app\manage\model\User;
use think\db\Query;
use think\Request;

class UserService
{
    /**
     * @var User
     */
    public $User;
    /**
     * @var Department
     */
    public $Department;

    public function __construct(User $user, Department $department)
    {
        $this->User = $user;
        $this->Department = $department;
    }

    /**
     * 检索用户列表
     * @param Request $request
     * @param $act_user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchUserList(Request $request, $act_user_info)
    {
        if (empty($act_user_info) || empty($act_user_info['dept_auth'])) {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        $dept_id   = $request->param('dept_id'); // 检索限定用户部门及子部门
        $keyword   = $request->param('query'); // 检索词

        // 是否限定了部门进行检索
        $dept_auth = [];
        if ($dept_id) {
            $dept_auth = $this->Department->getDeptChildAndSelfIdArrayById($dept_id);
        }

        // 空关键词
        if (empty($keyword)) {
            $query = $this->User->db()->alias('user')
                ->leftJoin('department department', 'department.id = user.dept_id')
                ->order('user.create_time', 'DESC')
                ->field([
                    'user.id',
                    'user.user_name',
                    'user.real_name',
                    'user.gender',
                    'user.mobile',
                    'department.name as dept_name'
                ])
                ->limit(10);
            if (!empty($dept_auth)) {
                $query->where('user.dept_id', 'IN', $dept_auth);
            }
            return ['error_code' => 0,'error_msg'   => '请求成功','data' => $query->select()];
        }

        // 有关键词
        $data = $this->User->db()->alias('user')
            ->leftJoin('department department', 'department.id = user.dept_id')
            ->where(function (Query $query) use ($keyword, $dept_auth) {
                if (!empty($dept_auth)) {
                    $query->where('user.dept_id', 'IN', $dept_auth);
                }
                $query->where('user.user_name|user.real_name|user.mobile', 'LIKE', '%'.$keyword.'%');
            })
            ->order('user.create_time', 'DESC')
            ->field([
                'user.id',
                'user.user_name',
                'user.real_name',
                'user.gender',
                'user.mobile',
                'department.name as dept_name'
            ])
            ->limit(10)
            ->select();
        return ['error_code' => 0,'error_msg'   => '请求成功','data' => $data];
    }
}
