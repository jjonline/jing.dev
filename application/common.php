<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 检查当前登录用户指定菜单Url或tag的权限
 * @param string $tag 待检查的无前缀斜线无文件后缀的菜单Url或菜单tag标签
 * @return boolean
 */
function user_has_permission($tag = null)
{
    return app('app\common\service\AuthService')->userHasPermission($tag);
}
