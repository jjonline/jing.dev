<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9
 * Time: 16:54
 */

namespace app\manage\service;

use app\manage\model\User;
use think\db\Query;

class UserService
{
    /**
     * @var User
     */
    public $User;
    public function __construct(User $user)
    {
        $this->User = $user;
    }

    /**
     * 检索用户列表
     * @param $keyword
     * @param $act_user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchUserList($keyword, $act_user_info)
    {
        if (empty($act_user_info) || empty($act_user_info['dept_auth'])) {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        $dept_auth = $act_user_info['dept_auth'];
        if (empty($keyword)) {
            $data = $this->User->db()->alias('user')
                ->leftJoin('department department', 'department.id = user.dept_id')
                ->where('user.dept_id', 'IN', $dept_auth['dept_id_vector'])
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
        $data = $this->User->db()->alias('user')
            ->leftJoin('department department', 'department.id = user.dept_id')
            ->where(function (Query $query) use ($keyword, $dept_auth) {
                $query->where('user.dept_id', 'IN', $dept_auth['dept_id_vector'])
                  ->where('user.user_name|user.real_name|user.mobile', 'LIKE', '%'.$keyword.'%');
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
