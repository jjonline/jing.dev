<?php
/**
 * 前台会员表
 */

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCustomer extends Migrator
{
    static private $table='customer';

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
                'comment'     => '前台会员表',
            ]);
            $table->addColumn('customer_name', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '唯一用户名',
                ])
                ->addColumn('customer_type', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '用户类别：标记用户类型，具体在业务代码中标记，譬如1个人用2企业用户 1消费者 2商家 等等',
                ])
                ->addColumn('reveal_name', 'string', [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                    'comment' => '前台展示显示的名称，默认写入customer_name',
                ])
                ->addColumn('real_name', 'string', [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                    'comment' => '真实姓名|企业名称 等',
                ])
                ->addColumn('password', 'string', [
                    'limit'   => 255,
                    'default' => '',
                    'null'    => false,
                    'comment' => '密码密文',
                ])
                ->addColumn('gender', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '-1',
                    'null'    => false,
                    'comment' => '性别：-1未知 0女 1男',
                ])
                ->addColumn('mobile', 'string', [
                    'limit'   => 20,
                    'default' => '',
                    'null'    => false,
                    'comment' => '手机号',
                ])
                ->addColumn('email', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '邮箱',
                ])
                ->addColumn('birthday', 'date', [
                    // 'default' => null,
                    'null'    => true,
                    'comment' => '出生年月日，null表示未知',
                ])
                ->addColumn('age', 'integer', [
                    'limit'   => MysqlAdapter::INT_TINY,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '年龄，0默认值表示未知',
                ])
                ->addColumn('card_id', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '身份证号',
                ])
                ->addColumn('motto', 'string', [
                    'limit'   => 64,
                    'default' => '',
                    'null'    => false,
                    'comment' => '座右铭，一句话简介',
                ])
                ->addColumn('province', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '所属省份',
                ])
                ->addColumn('city', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '所属地区|市',
                ])
                ->addColumn('district', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '所属县级',
                ])
                ->addColumn('location', 'string', [
                    'limit'   => 256,
                    'default' => '',
                    'null'    => false,
                    'comment' => '去除省市县之外的归属地址',
                ])
                ->addColumn('job_organization', 'string', [
                    'limit'   => 128,
                    'default' => '',
                    'null'    => false,
                    'comment' => '工作单位',
                ])
                ->addColumn('job_number', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '工作联系电话',
                ])
                ->addColumn('job_location', 'string', [
                    'limit'   => 256,
                    'default' => '',
                    'null'    => false,
                    'comment' => '完整的工作单位地址',
                ])
                ->addColumn('level', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '等级积分计算出来的用户等级名称',
                ])
                ->addColumn('points_effect', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '有效积分总数',
                ])
                ->addColumn('points_freeze', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '当前冻结中的积分：用于积分消费业务中的中间状态冻结',
                ])
                ->addColumn('points_level', 'integer', [
                    'limit'   => MysqlAdapter::INT_REGULAR,
                    'default' => '0',
                    'null'    => false,
                    'comment' => '等级积分总数：计算等级的积分总数，普通操作只增不减，仅后台管理员可降级减积分',
                ])
                ->addColumn('auth_code', 'string', [
                    'limit'   => 32,
                    'default' => '',
                    'null'    => false,
                    'comment' => '授权code，用于cookie加密(可变)',
                ])
                ->addColumn('user_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '分配给的管理人ID',
                ])
                ->addColumn('dept_id', 'integer', [
                    'default' => '0',
                    'null'    => false,
                    'comment' => '分配给的管理人所属部门ID',
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
                ->addIndex('customer_name', [
                    'unique' => true
                ])
                ->addIndex('mobile', [
                    'unique' => false
                ])
                ->addIndex('email', [
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
