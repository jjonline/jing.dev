<?php
/**
 * 填充必备的站点配置项
 */

use think\migration\Seeder;
use app\common\helper\GenerateHelper;

class CreateBaseSiteConfig extends Seeder
{
    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function run()
    {
        // 检查存在表
        if ($this->hasTable('site_config')) {
            // 检查是否空表
            $hasSiteConfig = \think\Db::name('site_config')->count();
            if (empty($hasSiteConfig)) {
                $data = [
                    [
                        'id'             => '1',
                        'flag'           => '基础设置',
                        'key'            => 'site_name',
                        'value'          => '',
                        'default'        => 'jing.dev',
                        'name'           => '网站名称',
                        'description'    => '基础的网站名称，网页显示时作为标题的一部分，字数不宜过多，首页一定用到；譬如：晶晶在线',
                        'type'           => 'text',
                        'is_config_hide' => '0',
                        'select_items'   => null,
                        'sort'           => '1',
                        'create_time'    => date('Y-m-d H:i:s'),
                        'update_time'    => date('Y-m-d H:i:s'),
                    ],
                    [
                        'id'             => '2',
                        'flag'           => '基础设置',
                        'key'            => 'site_keywords',
                        'value'          => '',
                        'default'        => 'jing.dev',
                        'name'           => '默认SEO关键词',
                        'description'    => '网站默认的SEO关键词，最多设置5个，多个之间使用半角逗号分隔（即英文逗号），首页一定用到',
                        'type'           => 'text',
                        'select_items'   => null,
                        'is_config_hide' => '0',
                        'sort'           => '2',
                        'create_time'    => date('Y-m-d H:i:s'),
                        'update_time'    => date('Y-m-d H:i:s'),
                    ],
                    [
                        'id'             => '3',
                        'flag'           => '基础设置',
                        'key'            => 'site_description',
                        'value'          => '',
                        'default'        => 'jing.dev',
                        'name'           => '默认SEO说明',
                        'description'    => '网页说明描述，最多140字，百度收录中88字最佳，首页一定用到',
                        'type'           => 'textarea',
                        'select_items'   => null,
                        'is_config_hide' => '0',
                        'sort'           => '3',
                        'create_time'    => date('Y-m-d H:i:s'),
                        'update_time'    => date('Y-m-d H:i:s'),
                    ],
                ];

                $posts = $this->table('site_config');
                $posts->insert($data)->save();
            } else {
                $this->output->warning(' == 站点配置表site_config已存在数据，请手动检查，本次seed忽略');
            }
        } else {
            throw new \think\Exception('站点配置表site_config不存在，请先执行`php think migrate:run`数据库迁移命令');
        }
    }
}
