<?php
/**
 * 本地存储引擎，空架子
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-02 17:11
 * @file OssStorage.php
 */

namespace app\common\storage;

use app\common\helper\AttachmentHelper;
use think\facade\Config;

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
        $expire_time = Config::get('attachment.attachment_expire_time', 1800);
        if ($attachment['is_safe']) {
            $param               = [];
            $param['expire_in']  = time() + $expire_time;
            // 生成ID的加密字符串 半小时有效
            $param['access_key'] = AttachmentHelper::transferEncrypt(
                $attachment['id'],
                Config::get('local.auth_key'),
                $expire_time
            );
            return '/manage/common/attachment?'.http_build_query($param);
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
