<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateOperationRecord extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('operation_record')) {
            $table = $this->table('operation_record', [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '后台用户操作动作和历史记录表-显式记录的关联业务操作记录',
            ]);
            $table->addColumn('operation_type', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY, // tinyint类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '内部int型标记的操作类型，业务代码具体定义映射map',
                ])
                ->addColumn('operation_name', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '可直接显示的简短操作名称，譬如：新增xx、变更xx、查看xx等',
                ])
                ->addColumn('operation_desc', 'string', [
                    'limit'   => 512,
                    'default' => '',
                    'null'    => false,
                    'comment' => '可直接显示的操作描述内容，不宜过长512字符以内',
                ])
                ->addColumn('business_type', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '操作关联的业务类型标记字符串，一般是表名称，譬如：article',
                ])
                ->addColumn('business_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '操作关联的业务ID',
                ])
                ->addColumn('business_param', 'json', [
                    'null'    => true,
                    'comment' => '操作关联的业务额外参数，json格式',
                ])
                ->addColumn('creator_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '创建人ID',
                ])
                ->addColumn('creator_name', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '创建人称呼-譬如后台用户真实姓名',
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
                ->addIndex('creator_id', [
                    'unique' => false
                ])
                ->addIndex(['business_type','business_id'], [
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
        if ($this->hasTable('operation_record')) {
            if (\think\Db::name('operation_record')->count() <= 0) {
                parent::down();
                $this->dropTable('operation_record');
            } else {
                throw new \think\Exception('检测到operation_record表非空，确需rollback请先手动清空该表数据，请不要随意执行`rollback`');
            }
        }
    }
}
