<?php
/**
 * 创建后台用户日志表
 */
use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserLog extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('user_log')) {
            $table = $this->table('user_log', [
                'id'          => false,
                'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '后台用户可识别日志表',
            ]);
            $table->addColumn('id', 'char', [
                    'limit'   => 36,
                    'comment' => 'ID，UUID形式',
                ])
                ->addColumn('user_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '用户ID',
                ])
                ->addColumn('dept_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '所属部门ID',
                ])
                ->addColumn('title', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '日志标题或描述',
                ])
                ->addColumn('os', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '操作系统信息',
                ])
                ->addColumn('browser', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '浏览器信息',
                ])
                ->addColumn('ip', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'IP地址',
                ])
                ->addColumn('location', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'IP地址解析出的归属地信息',
                ])
                ->addColumn('create_time', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'comment' => '创建时间',
                ])
                ->addColumn('update_time', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update'  => 'CURRENT_TIMESTAMP',
                    'comment' => '最后修改时间',
                ])
                ->addIndex('user_id', [
                    'unique' => false
                ])
                ->addIndex('dept_id', [
                    'unique' => false
                ])
                ->addIndex('create_time', [
                    'unique' => false
                ])
                ->create();
        }
    }

    /**
     * 回滚迁移执行的动作
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function down()
    {
        parent::down();
        if ($this->hasTable('user_log')) {
            $this->dropTable('user_log');
        }
    }
}
