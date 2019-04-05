<?php
/**
 * 创建站点自定义配置表
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateSiteConfig extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('site_config')) {
            $table = $this->table('site_config', [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '网站自定义配置表',
            ]);
            $table->addColumn('flag', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '配置项分组标记，相同flag是一个分组，允许中文',
                ])
                ->addColumn('key', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '配置项名称-唯一字符串标记',
                ])
                ->addColumn('value', 'string', [
                    'limit'   => 1024,
                    'default' => '',
                    'null'    => false,
                    'comment' => '配置值-配置项的配置值，最多1024字符',
                ])
                ->addColumn('default', 'string', [
                    'limit'   => 1024,
                    'default' => '',
                    'null'    => false,
                    'comment' => '配置项的默认值-最多1024字符',
                ])
                ->addColumn('name', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '配置项可显示的中文名称、称呼',
                ])
                ->addColumn('description', 'string', [
                    'limit'   => 512,
                    'default' => '',
                    'null'    => false,
                    'comment' => '配置项的中文说明-填写说明、功能说明等',
                ])
                ->addColumn('type', 'enum', [
                    'values'  => ['text','select','textarea'],
                    'default' => 'text',
                    'null'    => false,
                    'comment' => '配置项显示配置页面时的输入框类型:text-文本、select-单选下拉菜单、textarea-大段文本',
                ])
                ->addColumn('select_items', 'json', [
                    'default' => null,
                    'null'    => true,
                    'comment' => 'select类型时的待选项，[{"name": "选项名称", "value": "选项值"}...]',
                ])
                ->addColumn('is_config_hide', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '统一的配置页面是否隐藏 1-隐藏则有单独的页面进行配置 0不隐藏则统一的配置页面进行配置',
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
                ->addIndex('key', [
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
        if ($this->hasTable('site_config')) {
            if (\think\Db::name('site_config')->count() <= 0) {
                parent::down();
                $this->dropTable('site_config');
            } else {
                throw new \think\Exception('检测到site_config表非空，确需rollback请先手动清空该表数据，请不要随意执行`rollback`');
            }
        }
    }
}
