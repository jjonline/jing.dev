<?php
/**
 * 填充角色数据
 */
use think\migration\Seeder;

class CreateFirstRole extends Seeder
{
    /**
     * 填充角色数据
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
        if ($this->hasTable('role')) {
            // 检查不存在id为1的记录
            $hasRole = \think\Db::name('role')->find(1);
            if (empty($hasRole)) {
                $data = [
                    [
                        'id'          => '1',
                        'name'        => '超级管理员-Super',
                        'remark'      => '线上开发团队超管角色，Migration Seeder创建的Super超管角色',
                        'sort'        => '1',
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s'),
                    ]
                ];
                $posts = $this->table('role');
                $posts->insert($data)->save();
            } else {
                $this->output->warning(' == 角色表role已存在id为1的数据，请手动检查，本次seed忽略');
            }
        } else {
            throw new \think\Exception('角色表role不存在，请先执行`php think migrate:run`数据库迁移命令');
        }
    }
}
