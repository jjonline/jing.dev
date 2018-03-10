<?php
/**
 * 无需登录和权限效验的公共控制器
 * ----
 * 主要用作公用的一些ajax请求后端
 * ----
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-10 15:35
 * @file CommonController.php
 */

namespace app\manage\controller;

use app\common\controller\BasicController;
use app\common\helper\StringHelper;

class CommonController extends BasicController
{

    /**
     * ajax请求将中文转换为拼音
     * @return \think\Response
     */
    public function chineseToPinyinAction()
    {
        $chinese = $this->request->param('chinese');
        if(empty($chinese))
        {
            return $this->renderJson('待转换中文不得为空',404);
        }
        $pinyin = StringHelper::convertToPinyin($chinese);
        return $this->renderJson('success',0,$pinyin);
    }

}
