<?php
/**
 * 本地存储引擎，空架子
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-02 17:11
 * @file OssStorage.php
 */

namespace app\common\storage;

use app\common\helper\AttachmentHelper;

class LocalStorage extends BaseStorage
{
    /**
     * 本地推送不执行方法直接返回true
     * @param array $attachment 单个资源的信息数组
     * @return bool
     */
    public function put($attachment)
    {
        return true;
    }

    /**
     * 从存储引擎获取单一文件前台可访问完整资源url
     * @param array $attachment 单个资源的信息数组
     * @return string|false 获取成功字符串，获取失败false
     */
    public function get($attachment)
    {
        if ($attachment['is_safe']) {
            return AttachmentHelper::generateLocalSafeUrl($attachment['id']);
        }
        return app('request')->domain().$attachment['file_path'];
    }

    /**
     * 本地存储引擎获取所有数据返回空数组
     * @return array
     */
    public function all()
    {
        return [];
    }
}
