<?php
/**
 * 网页轮播图
 */

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class Image extends Migrator
{
    static private $table='image';

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
                'comment'     => '集中的网页轮播图表',
            ]);
            $table->addColumn('tag', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '轮播图分组标签',
                ])
                ->addColumn('title', 'string', [
                    'limit'   => 32,
                    'null'    => false,
                    'default' => '',
                    'comment' => '轮播图标题',
                ])
                ->addColumn('cover_id', 'string', [
                    'limit'   => 36,
                    'default' => '',
                    'null'    => false,
                    'comment' => '轮播图附件ID',
                ])
                ->addColumn('url', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '可选的轮播图点击后的跳转url',
                ])
                ->addColumn('enable', 'boolean', [ // tinyint(1)类型
                    'default' => '1',
                    'null'    => false,
                    'comment' => '标记是否有效1-有效0-禁用',
                ])
                ->addColumn('remark', 'string', [
                    'limit'   => 255,
                    'default' => '',
                    'null'    => false,
                    'comment' => '备注信息',
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
