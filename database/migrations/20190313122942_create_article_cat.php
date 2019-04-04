<?php
/**
 * 文章分类表
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateArticleCat extends Migrator
{
    static private $table='article_cat';

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
                'comment'     => '文章分类',
            ]);
            $table->addColumn('name', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '分类名称',
                ])
                ->addColumn('icon', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '预留的分类icon图标',
                ])
                ->addColumn('parent_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '父分类ID，0为顶级无父分类',
                ])
                ->addColumn('level', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY, // tinyint类型
                    // 'default' => '1', // 不得为空，必须赋值，不给默认值
                    'null'    => false,
                    'comment' => '层级 1一级 2二级 3三级',
                ])
                ->addColumn('sort', 'integer', [
                    'limit'   => MysqlAdapter::INT_BIG, // bigint类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '排序，数字越小越靠前',
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
