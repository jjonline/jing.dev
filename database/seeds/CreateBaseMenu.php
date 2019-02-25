<?php
/**
 * 填充基本菜单数据
 */
use think\migration\Seeder;

class CreateBaseMenu extends Seeder
{
    /**
     * 填充基本菜单数据
     * ----
     * 1、表不存在报错
     * 2、菜单数据存在则跳过
     * ----
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function run()
    {
        // 检查存在表
        if ($this->hasTable('menu')) {
            // 检查不存在id为1的记录
            $hasMenu = \think\Db::name('menu')->count();
            $data    = $this->getSeed();
            // menu表为空且seed数据存在才执行seed
            if (empty($hasMenu) && !empty($data)) {
                if (empty($data)) {
                    $this->output->warning(' == 菜单表seed数据不存在，请手动检查database/stubs/menu_seed.php，本次seed忽略');
                } else {
                    $posts = $this->table('menu');
                    $posts->insert($data)->save();
                }
            } else {
                $this->output->warning(' == 菜单表menu已存在数据，若确需seed请先手动清空menu表，本次seed忽略');
            }
        } else {
            throw new \think\Exception('菜单表menu不存在，请先执行`php think migrate:run`数据库迁移命令');
        }
    }

    /**
     * 读取seed数据
     * @return array
     */
    protected function getSeed()
    {
        // 相对于cli的think文件
        $seed_file = './database/stubs/menu_seed.php';
        if (is_file($seed_file)) {
            return include $seed_file;
        }
        return [];
    }
}
