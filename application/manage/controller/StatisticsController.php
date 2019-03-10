<?php
/**
 * 统计报表控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-23 17:04
 * @file StatisticsController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;

class StatisticsController extends BaseController
{

    /**
     * 获取侧边栏badge提示数字，此处仅列出返回数据的初步结构，待完善
     * @return mixed
     */
    public function badgeAction()
    {
        $tags  = $this->request->post('tags/a');
        $badge =  array_flip($tags);
        $data  = [
            'error_code' => 0,
            'error_msg'  => 'ok',
            'data'       => $badge,
        ];
        return $this->asJson($data);
    }
}
