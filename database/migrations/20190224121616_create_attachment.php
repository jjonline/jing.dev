<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateAttachment extends Migrator
{
    /**
     * 执行迁移被运行的方法
     * ----
     * cli执行命令php think migrate:run所被执行的方法
     * ----
     */
    public function up()
    {
        if (!$this->hasTable('attachment')) {
            $table = $this->table('attachment', [
                'id'          => false,
                'primary_key' => 'id',
                'engine'      => 'InnoDB',
                'collation'   => 'utf8mb4_general_ci',
                'comment'     => '附件表：用户上传资源数据',
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
                ->addColumn('file_origin_name', 'string', [
                    'limit'   => 128,
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '带后缀的上传时的原始文件名',
                ])
                ->addColumn('file_name', 'string', [
                    'limit'   => 128,
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '带后缀的上传完毕保存的文件名',
                ])
                ->addColumn('file_path', 'string', [
                    'limit'   => 512,
                    // 'default' => '',
                    'null'    => false,
                    'comment' => '相对于网站根目录的带文件名的文件路径，斜杠开头，方便切换CDN',
                ])
                ->addColumn('file_mime', 'string', [
                    'limit'   => 64,
                    // 'null'    => false,
                    'comment' => '文件mime类型',
                ])
                ->addColumn('file_size', 'integer', [
                    'limit'   => MysqlAdapter::INT_BIG, // bigint类型
                    // 'default' => '0',
                    'null'    => false,
                    'comment' => '资源大小，单位：Bytes即B，1024B = 1KB',
                ])
                ->addColumn('file_sha1', 'string', [
                    'limit'   => 40,
                    // 'default' => null,
                    'comment' => '资源的sha1值',
                ])
                ->addColumn('image_width', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '图片类型宽资源的宽度',
                ])
                ->addColumn('image_height', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '图片类型资源的高度',
                ])
                ->addColumn('is_safe', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY, // tinyint类型
                    'default' => '0',
                    'null'    => false,
                    'comment' => '资源文件是否需要安全存储不暴露公网url，1是0否',
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
                ->addIndex(['user_id','file_sha1'], [
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
        if (\think\Db::name('attachment')->count() <= 0) {
            parent::down();
            if ($this->hasTable('attachment')) {
                $this->dropTable('attachment');
            }
        } else {
            throw new \think\Exception('检测到attachment表非空，确需rollback请先手动清空该表数据，请不要随意执行`rollback`');
        }
    }
}
