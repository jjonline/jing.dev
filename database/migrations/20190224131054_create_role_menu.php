<?php
/**
 * 角色菜单表创建
 */
use think\migration\Migrator;
use think\migration\db\Column;

class CreateRoleMenu extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('role_menu')) {
            $table = $this->table('role_menu', [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '角色所拥有的菜单权限[role表与menu表多对多]',
            ]);
            $table->addColumn('role_id', 'integer', [
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '角色ID',
                ])
                ->addColumn('menu_id', 'integer', [
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '角色可使用的菜单ID',
                ])
                ->addColumn('permissions', 'enum', [
                    'values'   => ['super','leader','staff','guest'],
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '角色权限级别，super超级管理员，leader部门管理员，staff职员，guest游客',
                ])
                ->addColumn('show_columns', 'json', [
                    'default' => null,
                    'null'    => true,
                    'comment' => '该菜单若有字段控制，则存储该角色的该菜单能显示的字段[{"column":"name","name":"名称"}...]',
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
                ->addIndex('role_id', [
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
     * @throws \think\Exception
     */
    public function down()
    {
        if ($this->hasTable('role_menu')) {
            if (\think\Db::name('role_menu')->count() <= 0) {
                parent::down();
                $this->dropTable('role_menu');
            } else {
                throw new \think\Exception('检测到role_menu表非空，确需rollback请先手动清空该表数据，请不要随意执行`rollback`');
            }
        }
    }
}
