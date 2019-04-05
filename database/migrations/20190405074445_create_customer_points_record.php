<?php
/**
 * 会员有效积分变动流水记录
 */

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCustomerPointsRecord extends Migrator
{
    static private $table='customer_points_record';

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
                'comment'     => '会员有效积分变动流水记录',
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
                    'comment' => '积分变动操作设备来源：1-pc 2-h5移动端 3-Android 4-iOs 5-微信小程序 6-支付宝小程序',
                ])
                ->addColumn('points_type', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '积分变动类型，1-有效积分增加 2-有效积分扣除 3-冻结积分增加 4-冻结积分减少 5-等级积分增加 6-等级积分扣除',
                ])
                ->addColumn('points_excerpt', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '积分变动简要说明',
                ])
                ->addColumn('points_effect_change', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '有效积分变动数量：正数增加积分 负数扣减积分',
                ])
                ->addColumn('points_freeze_change', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '有效积分变动数量：正数增加积分 负数扣减积分',
                ])
                ->addColumn('points_level_change', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '等级积分变动数量：正数增加等级积分 负数扣减等级积分',
                ])
                ->addColumn('points_effect_total', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '变动后等级积分总数',
                ])
                ->addColumn('points_freeze_total', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '变动后动机积分总数：只可能是大于0的整数，表示冻结数量',
                ])
                ->addColumn('points_level_total', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '变动后等级积分总数',
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
                ->addIndex('points_type', [
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
