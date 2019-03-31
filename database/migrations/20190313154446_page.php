<?php
/**
 * 系统落地页page表
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class Page extends Migrator
{
    static private $table='page';

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
                'comment'     => '落地单页配置和参数表--依赖前台落地页的代码实现',
            ]);
            $table->addColumn('flag', 'string', [
                    'limit'   => 32,
                    'null'    => false,
                    'comment' => '落地页唯一flag标记',
                ])
                ->addColumn('title', 'string', [
                    'limit'   => 32,
                    'null'    => false,
                    'default' => '',
                    'comment' => '落地页标题',
                ])
                ->addColumn('cover_id', 'string', [
                    'limit'   => 36,
                    'default' => '',
                    'null'    => false,
                    'comment' => '落地页单独的可选的大封面图id',
                ])->addColumn('keywords', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '页面关键词，用于落地页面的keywords标签，半角逗号分隔不宜过多',
                ])
                ->addColumn('description', 'string', [
                    'limit'   => 256,
                    'default' => '',
                    'null'    => false,
                    'comment' => '页面描述，用于落地页面的description标签，最多140字',
                ])
                ->addColumn('config', 'json', [
                    'null'    => true,
                    'comment' => 'json格式的落地页配置参数',
                ])
                ->addColumn('setting', 'json', [
                    'null'    => true,
                    'comment' => 'json格式的落地页配置项对应的具体参数内容',
                ])
                ->addColumn('sample_id', 'string', [
                    'limit'   => 36,
                    'default' => '',
                    'null'    => false,
                    'comment' => '单页面配置效果样例附图id',
                ])
                ->addColumn('enable', 'boolean', [ // tinyint(1)类型
                    'default' => '1',
                    'null'    => false,
                    'comment' => '标记是否有效1-有效0-禁用',
                ])
                ->addColumn('template', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '可选的落地页使用的模板名称',
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
                ->addIndex('flag', [
                    'unique' => true
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
