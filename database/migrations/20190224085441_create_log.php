<?php
/**
 * 创建系统日志表
 */
use think\migration\Migrator;
use think\migration\db\Column;

class CreateLog extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('log')) {
            $table = $this->table('log', [
                'id'          => false,
                'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '用户操作动作的详细日志，默认每个请求都记录，可配置忽略',
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
                ->addColumn('ip', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '动作记录的ip地址',
                ])
                ->addColumn('user_agent', 'string', [
                    'limit'   => 512,
                    'default' => '',
                    'null'    => false,
                    'comment' => '请求头信息-浏览器头信息',
                ])
                ->addColumn('action', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '请求的操作，对应menu表的url字段值',
                ])
                ->addColumn('url', 'text', [
                    'null'    => true,
                    'comment' => '请求的完整Url',
                ])
                ->addColumn('method', 'string', [
                    'limit'   => 16,
                    'default' => '',
                    'null'    => false,
                    'comment' => '请求方式 GET、POST、PUT、DELETE等',
                ])
                ->addColumn('request_data', 'text', [
                    'null'    => true,
                    'comment' => '请求体数据',
                ])
                ->addColumn('extra_data', 'text', [
                    'null'    => true,
                    'comment' => '主动保存进日志的数据',
                ])
                ->addColumn('memory_usage', 'decimal', [
                    'precision' => '20',
                    'scale'     => '2',
                    'default'   => '0.00',
                    'null'      => false,
                    'comment'   => '占用内存大小（kb）',
                ])
                ->addColumn('execute_millisecond', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '执行耗时（毫秒）',
                ])
                ->addColumn('description', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '日志手动记录的说明文字',
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
                ->addIndex(['user_id','action'], [
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
        if ($this->hasTable('log')) {
            $this->dropTable('log');
        }
    }
}
