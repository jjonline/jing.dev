<?php
/**
 * 系统关键词
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateTag extends Migrator
{
    static private $table='tag';

    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable(self::$table)) {
            $table = $this->table(self::$table, [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '系统tag关键词表',
            ]);
            $table->addColumn('tag', 'string', [
                    'limit'   => 24,
                    'null'    => false,
                    'comment' => 'tag关键词',
                ])
                ->addColumn('cover_id', 'string', [
                    'limit'   => 36,
                    'default' => '',
                    'null'    => false,
                    'comment' => '可能的tag附图ID',
                ])
                ->addColumn('excerpt', 'string', [
                    'limit'   => 255,
                    'default' => '',
                    'null'    => false,
                    'comment' => '可能的tag简介',
                ])
                ->addColumn('user_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '创建人ID',
                ])
                ->addColumn('dept_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '创建人部门ID',
                ])
                ->addColumn('quota', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => 'tag引用次数',
                ])
                ->addColumn('sort', 'integer', [
                    'limit'   => MysqlAdapter::INT_BIG,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '排序，数字越小越靠前',
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
                ->addIndex('tag', [
                    'unique' => true
                ])
                ->addIndex('user_id', [
                    'unique' => false
                ])
                ->addIndex('dept_id', [
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
        if ($this->hasTable(self::$table)) {
            if (\think\Db::name(self::$table)->count() <= 0) {
                parent::down();
                $this->dropTable(self::$table);
            } else {
                throw new \think\Exception('检测到'.self::$table.'表已有非seed填充的数据，请不要随意执行`rollback`');
            }
        }
    }
}
