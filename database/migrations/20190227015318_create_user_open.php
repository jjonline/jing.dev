<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateUserOpen extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('user_open')) {
            $table = $this->table('user_open', [
                //'id'          => false,
                //'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '网站后台所有菜单节点信息表',
            ]);
            $table->addColumn('user_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '后台用户ID',
                ])
                ->addColumn('open_type', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY, // tinyint类型
                    'null'    => false,
                    'comment' => '开放平台类型1-QQ、2-微信...',
                ])
                ->addColumn('open_id', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '开放平台的OpenID',
                ])
                ->addColumn('access_token', 'string', [
                    'limit'   => 256,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'AccessToken',
                ])
                ->addColumn('token_expire_in', 'integer', [
                    'null'    => false,
                    'default' => '0',
                    'comment' => 'Token过期时间-linux时间戳',
                ])
                ->addColumn('refresh_token', 'string', [
                    'limit'   => 256,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'RefreshToken-大部分时候用不上',
                ])
                ->addColumn('name', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '开放平台账号的名称',
                ])
                ->addColumn('gender', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY, // tinyint类型
                    'default' => '-1',
                    'null'    => false,
                    'comment' => '性别：-1未知0女1男',
                ])
                ->addColumn('figure', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '头像图src-可能需要本地化',
                ])
                ->addColumn('union_id', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'UnionID',
                ])
                ->addColumn('full_user_info', 'json', [
                    'default' => null,
                    'null'    => true,
                    'comment' => '获取到的用户全部字段信息-{"province":"xx","following":1122...}',
                ])
                ->addColumn('enable', 'boolean', [ // tinyint(1)类型
                    'default' => '1',
                    'null'    => false,
                    'comment' => '标记是否有效1-有效0-禁用',
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
                ->addIndex(['open_id','open_type'], [
                    'unique' => true
                ])
                ->addIndex('user_id', [
                    'unique' => false
                ])
                ->addIndex('union_id', [
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
        if ($this->hasTable('user_open')) {
            if (\think\Db::name('user_open')->count() <= 0) {
                parent::down();
                $this->dropTable('user_open');
            } else {
                throw new \think\Exception('检测到user_open表已有非seed填充的数据，请不要随意执行`rollback`');
            }
        }
    }
}
