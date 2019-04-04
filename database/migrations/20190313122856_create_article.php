<?php
/**
 * 图文文章
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateArticle extends Migrator
{
    static private $table='article';

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
                'comment'     => '图文文章表',
            ]);
            $table->addColumn('cat_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '文章分类ID',
                ])
                ->addColumn('cover_id', 'char', [
                    'limit'   => 36,
                    'default' => '',
                    'null'    => false,
                    'comment' => '文章附图ID',
                ])->addColumn('title', 'string', [
                    'limit'   => 128,
                    'null'    => false,
                    'comment' => '文章标题',
                ])
                ->addColumn('excerpt', 'string', [
                    'limit'   => 256,
                    'default' => '',
                    'null'    => false,
                    'comment' => '概要导读',
                ])
                ->addColumn('content', 'text', [
                    'null'    => false,
                    'comment' => '文章富文本主体内容',
                ])
                ->addColumn('author', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '自定义文章前台显示作者',
                ])
                ->addColumn('source', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '文章来源：url或文字',
                ])
                ->addColumn('tags', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'tag_id半角逗号分割字符串，限定一篇文章最多不要超过5个tag',
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
                ->addColumn('click', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '文章点击次数',
                ])
                ->addColumn('is_home', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '是否首页展示 1是0否',
                ])
                ->addColumn('is_top', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '是否置顶 1是0否',
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
                    'comment' => '文章显示所使用模板名称',
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
                ->addColumn('show_time', 'datetime', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'comment' => '自定义显示时间',
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
                ->addIndex('cat_id', [
                    'unique' => false
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
