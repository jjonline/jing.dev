<?php
/**
 * 用户模型|公共
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-08 16:39
 * @file
 */

namespace app\common\model;

use app\common\helper\ArrayHelper;
use app\common\helper\FilterValidHelper;
use app\common\helper\GenerateHelper;
use think\exception\DbException;
use think\Model;

class User extends Model
{
    use PermissionsTrait;

    /**
     * 用户ID查找用户信息
     * @param $user_id
     * @return array
     * @throws DbException
     */
    public function getUserInfoById($user_id)
    {
        if (empty($user_id)) {
            return [];
        }
        $data = $this->get(['id' => $user_id]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 用户名查询用户信息
     * @param $user_name
     * @return array
     * @throws DbException
     */
    public function getUserInfoByUserName($user_name)
    {
        if (empty($user_name)) {
            return [];
        }
        $data = $this->get(['user_name' => $user_name]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 邮箱地址查找用户信息
     * @param $email
     * @return array
     * @throws DbException
     */
    public function getUserInfoByEmail($email)
    {
        if (empty($email)) {
            return [];
        }
        $data = $this->get(['email' => $email]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 手机号查找用户信息
     * @param $mobile
     * @return array
     * @throws DbException
     */
    public function getUserInfoByMobile($mobile)
    {
        if (empty($mobile)) {
            return [];
        }
        $data = $this->get(['mobile' => $mobile]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 智能判断参数值是用户名、邮箱还是手机号自动查找用户信息
     * @param $user_unique_key_field_value
     * @return array
     * @throws DbException
     */
    public function getUserInfoAutoByUniqueKey($user_unique_key_field_value)
    {
        if (FilterValidHelper::isMailValid($user_unique_key_field_value)) {
            return $this->getUserInfoByEmail($user_unique_key_field_value);
        }
        if (FilterValidHelper::isPhoneValid($user_unique_key_field_value)) {
            return $this->getUserInfoByMobile($user_unique_key_field_value);
        }
        return $this->getUserInfoByUserName($user_unique_key_field_value);
    }

    /**
     * 通过用户主键ID查询包含用户角色、部门的完整信息
     * @param string $user_id 用户主键ID
     * @param $user_id
     * @return array
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFullUserInfoById($user_id)
    {
        $data = $this->db()->alias('user')
              ->join('department dept', 'dept.id = user.dept_id')
              ->join('role role', 'role.id = user.role_id')
              ->field([
                  'user.*',
                  'dept.name as dept_name',
                  'dept.id as dept_id',
                  'dept.level as dept_level', // 用户所属部门的层级
                  'role.id as role_id',
                  'role.name as role_name',
              ])
              ->where('user.id', $user_id)
              ->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 重新生成|更新用户auth_code值并保存
     * @param mixed $user_id
     * @return bool
     */
    public function updateUserAuthCode($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $_user              = [];
        $_user['auth_code'] = GenerateHelper::makeNonceStr(8);
        return false !== $this->update($_user, ['id' => $user_id]);
    }

    /**
     * 获取所有用户id+真实姓名列表
     * @return array
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserTreeList()
    {
        $result = $this->field(['id', 'real_name'])
            ->order([
                'sort' => 'ASC',
                'id'   => 'DESC'
            ])->select();
        return $result->isEmpty() ? [] : $result->toArray();
    }

    /**
     * 获取指定部门id数组中的用户列表，用于实现指定用户下所属部门和下辖部门中的用户列表
     * @param array $multi_dept_id 所在部门和所辖部门id数组
     * @return array
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUsersByMultiDeptId(array $multi_dept_id)
    {
        if (empty($multi_dept_id)) {
            return [];
        }

        $result = $this->field(['id', 'real_name'])
            ->where('dept_id', 'in', ArrayHelper::filterByCallableThenUnique($multi_dept_id, 'intval'))
            ->order([
                'sort' => 'ASC', // 按排序字段排序
                'id'   => 'DESC'
            ])->select();
        return $result->isEmpty() ? [] : $result->toArray();
    }

    /**
     * 获取指定用户的权限下用户列表[所属部门和子部门的所有用户列表]
     * @param array $act_user
     * @return array
     * @throws DbException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAuthFullUserList(array $act_user)
    {
        $query = $this->db()->alias('user')
            ->join('department dept', 'dept.id = user.dept_id')
            ->join('role role', 'role.id = user.role_id')
            ->field([
                'user.*',
                'dept.name as dept_name',
                'dept.id as dept_id',
                'dept.level as dept_level', // 用户所属部门的层级
                'role.id as role_id',
                'role.name as role_name',
            ]);

        // 数据权限限定
        $this->permissionsLimitOrDeptSearch(
            $query,
            'user.dept_id',
            'user.id',
            $act_user
        );

        return $query->select()->toArray();
    }

    /**
     * 指定用户是否为根用户
     * @param $user_id
     * @return bool
     */
    public function isRootUser($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        return !!$this->where('id', $user_id)->value('is_root');
    }
}
