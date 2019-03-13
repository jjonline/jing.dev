<?php
/**
 * 创建菜单表
 */
use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateMenu extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('menu')) {
            $table = $this->table('menu', [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '网站后台所有菜单节点信息表',
            ]);
            $table->addColumn('tag', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '唯一菜单标记Tag',
                ])
                ->addColumn('name', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '菜单中文名称',
                ])
                ->addColumn('icon', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'fontawesome、glyphicon或ionicons图标的class',
                ])
                ->addColumn('url', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '菜单Url-无域名无斜线前缀',
                ])
                ->addColumn('parent_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '父菜单ID',
                ])
                ->addColumn('level', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY, // tinyint类型
                    // 'default' => '0',
                    'null'    => false,
                    'comment' => '当前层级 1为一级导航 2为二级导航 3为二级导航页面中的功能按钮',
                ])
                ->addColumn('is_required', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '标记是否必选-1必选0权限控制，为1时选择角色菜单权限的时候默认勾选且不可取消',
                ])
                ->addColumn('is_badge', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '菜单中是否需要显示待办事项badge',
                ])
                ->addColumn('is_system', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => true,
                    'comment' => '标记是否系统菜单-1不允许删除 0允许删除',
                ])
                ->addColumn('is_permissions', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => true,
                    'comment' => '标记是否有数据范围控制-1则会按部门划分数据范围进行控制',
                ])
                ->addColumn('is_column', 'boolean', [ // tinyint(1)类型
                    'default' => '0',
                    'null'    => true,
                    'comment' => '标记是否需要控制字段显示-1则有显示字段控制',
                ])
                ->addColumn('all_columns', 'json', [
                    'default' => null,
                    'null'    => true,
                    'comment' => '菜单额外配置的选项数据，譬如需控制字段时则预置字段列表选项',
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
                ->addIndex('tag', [
                    'unique' => true
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
        if ($this->hasTable('menu')) {
            if (\think\Db::name('menu')->count() <= 0) {
                parent::down();
                $this->dropTable('menu');
            } else {
                throw new \think\Exception('检测到menu表已有非seed填充的数据，请不要随意执行`php think migrate:rollback`');
            }
        }
    }
}
