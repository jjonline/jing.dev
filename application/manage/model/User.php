<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/14
 * Time: 20:38
 */

namespace app\manage\model;


use think\Model;

class User extends Model
{

    /**
     * ID查找用户
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserById($id)
    {
        if(empty($id))
        {
            return [];
        }
        $data = $this->alias('user')
            ->leftJoin('department department','department.id = user.dept_id')
            ->where('user.id', $id)
            ->field(['user.*','department.name as dept_name'])
            ->find();
        //要求返回一个数组，如果对象为空返回一个空数组，不为空转换成数组返回
        return !$data ? [] : $data->toArray();
    }

    /**
     * 用户名查找用户
     * @param $user_name
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserByUserName($user_name)
    {
        if(empty($user_name))
        {
            return [];
        }
        $data = $this->where('user_name',$user_name)->find();
        return !$data ? [] : $data->toArray();
    }
}
