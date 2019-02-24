<?php
/**
 * 创建用户表
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateUser extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('user')) {
            $table = $this->table('user', [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '后台统一用户表：系统本身的登录授权基础表',
            ]);
            $table->addColumn('user_name', 'string', [
                    'limit'   => 32,
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '账号',
                ])
                ->addColumn('password', 'string', [
                    'limit'   => 255,
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '密码（密文）',
                ])
                ->addColumn('real_name', 'string', [
                    'limit'   => 32,
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '真实姓名',
                ])
                ->addColumn('gender', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '-1',
                    'null'    => false,
                    'comment' => '性别：-1未知0女1男',
                ])
                ->addColumn('mobile', 'string', [
                    'limit'   => 20,
                    'default' => '',
                    'null'    => false,
                    'comment' => '手机号码',
                ])
                ->addColumn('email', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '电子邮箱',
                ])
                ->addColumn('telephone', 'string', [
                    'limit'   => 20,
                    'default' => '',
                    'null'    => false,
                    'comment' => '座机号码',
                ])
                ->addColumn('auth_code', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '授权code，用于cookie加密(可变)',
                ])
                ->addColumn('is_leader', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '是否本部门的领导：1是0不是 用于直属部门内部审批、数据权限等识别',
                ])
                ->addColumn('dept_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '所属部门ID',
                ])
                ->addColumn('role_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '所属角色ID',
                ])
                ->addColumn('enable', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '启用禁用标记：1启用0禁用',
                ])
                ->addColumn('remark', 'string', [
                    'limit'   => 255,
                    'default' => '',
                    'null'    => false,
                    'comment' => '备注信息',
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
                ->addIndex('user_name', [
                    'unique' => true
                ])
                ->addIndex('mobile', [
                    'unique' => false
                ])
                ->addIndex('email', [
                    'unique' => false
                ])
                ->addIndex('dept_id', [
                    'unique' => false
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
        if (\think\Db::name('user')->count() == 1) {
            parent::down();
            if ($this->hasTable('user')) {
                $this->dropTable('user');
            }
        } else {
            throw new \think\Exception('检测到user表已有非seed填充的数据，请不要随意执行`php think migrate:rollback`');
        }
    }
}
