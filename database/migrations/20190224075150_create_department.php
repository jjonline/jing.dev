<?php
/**
 * 创建部门表
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateDepartment extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('department')) {
            $table = $this->table('department', [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '多层级系统部门信息表',
            ]);
            $table->addColumn('name', 'string', [
                    'limit'   => 200,
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '部门名称',
                ])
                ->addColumn('parent_id', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => null,
                    'null'    => true,
                    'comment' => '父级部门ID，为0则是顶级部门',
                ])
                ->addColumn('level', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    // 'default' => '0',
                    'null'    => false,
                    'comment' => '部门层级：1->2->3逐次降低，最大层级5',
                ])
                ->addColumn('user_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '创建人用户ID',
                ])
                ->addColumn('dept_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '创建人所属部门ID',
                ])
                ->addColumn('sort', 'integer', [
                    'limit'   => MysqlAdapter::INT_BIG,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '部门排序，数字越小越靠前',
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
                ->addIndex('parent_id', [
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
        if ($this->hasTable('department')) {
            if (\think\Db::name('department')->count() == 1) {
                parent::down();
                $this->dropTable('department');
            } else {
                throw new \think\Exception('检测到dept表已有非seed填充的数据，请不要随意执行`php think migrate:rollback`');
            }
        }
    }
}
