<?php
/**
 * 会员操作记录
 */

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCustomerLog extends Migrator
{
    static private $table='customer_log';

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
                'comment'     => '前台会员操作动作日志记录',
            ]);
            $table->addColumn('customer_id', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '会员ID',
                ])
                ->addColumn('device', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '1',
                    'null'    => false,
                    'comment' => '日志记录设备来源：1-pc 2-h5移动端 3-Android 4-iOs 5-微信小程序 6-支付宝小程序',
                ])
                ->addColumn('log_type', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '会员操作动作类型，业务中具体规定',
                ])
                ->addColumn('log_excerpt', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '操作动作简要描述',
                ])
                ->addColumn('ip', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '动作记录的ip地址',
                ])
                ->addColumn('location', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => 'IP地址解析出的归属地信息',
                ])
                ->addColumn('user_agent', 'string', [
                    'limit'   => 512,
                    'default' => '',
                    'null'    => false,
                    'comment' => '请求头信息-浏览器头信息',
                ])
                ->addColumn('url', 'text', [
                    'null'    => true,
                    'comment' => '请求的完整Url',
                ])
                ->addColumn('method', 'string', [
                    'limit'   => 16,
                    'default' => '',
                    'null'    => false,
                    'comment' => '请求方式 GET、POST、PUT、DELETE等',
                ])
                ->addColumn('request_data', 'text', [
                    'null'    => true,
                    'comment' => '请求体数据',
                ])
                ->addColumn('extra_data', 'text', [
                    'null'    => true,
                    'comment' => '主动保存进日志的数据，前台不显示',
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
                ->addIndex('customer_id', [
                    'unique' => false
                ])
                ->addIndex('log_type', [
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
