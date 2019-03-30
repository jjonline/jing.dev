<?php
/**
 * 异步任务表创建
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateAsyncTaskBack extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('async_task_back')) {
            $table = $this->table('async_task_back', [
                'id'          => false,
                'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '异步任务冷数据表[async_task仅保留最近3个月的数据，其他数据存放至该表]',
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
                    'comment' => '异步任务可识读标题-由底层类属性标记',
                ])
                ->addColumn('task', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '异步任务-对应底层类名',
                ])
                ->addColumn('task_data', 'text', [
                    'null'    => false,
                    'comment' => '异步任务参数数据-JSON字符串',
                ])
                ->addColumn('result', 'text', [
                    'limit'   => MysqlAdapter::TEXT_MEDIUM, // MEDIUMTEXT类型
                    'null'    => false,
                    'comment' => '异步任务执行结果描述-描述文本',
                ])
                ->addColumn('task_status', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY, // tinyint类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '异步任务执行状态：0、未投递未执行，1、已投递正在执行，2、执行成功，3、执行失败',
                ])
                ->addColumn('delivery_time', 'datetime', [
                    'default' => null,
                    'null'    => true,
                    'comment' => '任务投递开始执行时间',
                ])
                ->addColumn('finish_time', 'datetime', [
                    'default' => null,
                    'null'    => true,
                    'comment' => '任务执行结束时间',
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
        if ($this->hasTable('async_task_back')) {
            $this->dropTable('async_task_back');
        }
    }
}
