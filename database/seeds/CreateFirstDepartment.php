<?php
/**
 * 填充部门数据
 */
use think\migration\Seeder;

class CreateFirstDepartment extends Seeder
{
    /**
     * 填充部门数据
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
        if ($this->hasTable('department')) {
            // 检查不存在id为1的记录
            $hasDepartment = \think\Db::name('department')->find(1);
            if (empty($hasDepartment)) {
                $data = [
                    [
                        'id'          => '1',
                        'name'        => '线上团队-顶级部门',
                        'parent_id'   => null,
                        'level'       => '1',
                        'sort'        => '1',
                        'remark'      => '开发测试线上团队的顶级部门，Migration Seeder创建的顶级部门数据',
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s'),
                    ]
                ];
                $posts = $this->table('department');
                $posts->insert($data)->save();
            } else {
                $this->output->warning(' == 用户表department已存在id为1的数据，请手动检查，本次seed忽略');
            }
        } else {
            throw new \think\Exception('部门表department不存在，请先执行`php think migrate:run`数据库迁移命令');
        }
    }
}
