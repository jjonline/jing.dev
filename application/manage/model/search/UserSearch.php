<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-09 11:16
 * @file UserSearch.php
 */

namespace app\manage\model\search;

use think\Db;

class UserSearch extends BaseSearch
{

    /**
     * 后台管理员列表
     * ----
     * 1、菜单权限必须是super或leader才能显示数据
     * 2、如果是super显示所有
     * 3、如果是leader仅显示该部门下的员工账号数据
     * ----
     * @param array $user_info 当前登录用户信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list($user_info)
    {
        // 1、超级管理员菜单权限可看全部
        // 2、leader菜单权限且属于部门领导可看所属部门以及子部门下成员
        // 3、leader菜单权限但不是领导只能看本部门下的子部门的会员数据
        $menu_auth = $user_info['menu_auth'];
        $dept_auth = $user_info['dept_auth'];
        if(!in_array($menu_auth['permissions'],['super','leader']))
        {
            $this->pageError = '抱歉，您没有操作权限';
            return $this->handleResult();
        }

        // 构造Query对象
        $Query = Db::name('user user')
               ->field([
                   'user.id',
                   'user.user_name',
                   'user.role_id',
                   'user.dept_id',
                   'user.real_name',
                   'user.mobile',
                   'user.telephone',
                   'user.is_leader',
                   'user.email',
                   'user.gender',
                   'user.enable',
                   'user.create_time',
                   'user.remark',
                   'role.name as role_name',
                   'department.name as dept_name',
               ])
               ->leftJoin('role role','role.id = user.role_id')
               ->leftJoin('department department','department.id = user.dept_id');

        // 如果是部门领导数据权限，限定只能查看该部门下的用户列表
        if($menu_auth['permissions'] == 'leader')
        {
            $Query->where('user.dept_id','IN',$dept_auth['dept_id_vector']);
        }

        /**
         * 检索条件
         */
        // 关键词搜索--方法体内部自动判断$this->keyword是否有值并执行sql构造
        $search_columns = ['user.user_name', 'user.real_name', 'user.mobile', 'user.email', 'user.remark'];
        $this->keywordSearch($Query,$search_columns,$this->keyword);

        // 选择了部门
        $select_dept_id = $this->request->param('dept_id/i');
        if(!empty($select_dept_id))
        {
            $Query->where('user.dept_id',$select_dept_id);
        }

        // 时间范围检索
        $this->dateTimeSearch($Query,'user.create_time');

        // 克隆Query对象读取总记录数
        $countQuery       = clone $Query;
        $this->totalCount = $countQuery->count();

        // 字段排序以及没有排序的情况下设定一个默认排序字段
        $this->orderBy($Query,'user');
        if ($Query->getOptions('order') === null) {
            $Query->order('user.create_time','DESC');
        }

        // 查询当前分页列表数据
        $this->results    = $Query->limit($this->start, $this->length)->select();

        // 处理结果集
        return $this->handleResult();
    }
}
