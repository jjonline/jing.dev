<?php
/**
 * 填充超级管理员数据
 */
use think\migration\Seeder;
use app\common\helper\GenerateHelper;

class CreateFirstSuperUser extends Seeder
{
    /**
     * 创建超级管理员1号用户
     * ----
     * 1、表不存在报错
     * 2、id为1的记录存在报错
     * ----
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function run()
    {
        // 检查存在表
        if ($this->hasTable('user')) {
            // 检查不存在id为1的记录
            $hasSuper = \think\Db::name('user')->find(1);
            if (empty($hasSuper)) {
                $pwd = 'cf123456'; // default pwd
                $pwd = password_hash(config('local.auth_key').trim($pwd), PASSWORD_BCRYPT);
                $data = [
                    [
                        'id'          => '1',
                        'user_name'   => 'jing',
                        'password'    => $pwd,
                        'real_name'   => '杨晶晶',
                        'gender'      => '1',
                        'mobile'      => '15872254727',
                        'email'       => 'jjonline@jjonline.cn',
                        'telephone'   => '',
                        'auth_code'   => GenerateHelper::makeNonceStr(16),
                        'is_leader'   => '1',
                        'dept_id'     => '1',
                        'role_id'     => '1',
                        'enable'      => '1',
                        'remark'      => 'Migration Seeder创建的1号Super用户',
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s'),
                    ]
                ];
                $posts = $this->table('user');
                $posts->insert($data)->save();
            } else {
                $this->output->warning(' == 用户表user已存在id为1的super超级用户，请手动检查，本次seed忽略');
            }
        } else {
            throw new \think\Exception('用户表user不存在，请先执行`php think migrate:run`数据库迁移命令');
        }
    }
}
