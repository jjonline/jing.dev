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
use app\common\helper\AttachmentHelper;
use app\common\helper\StringHelper;
use app\common\service\AttachmentService;
use think\facade\Config;

class CommonController extends BasicController
{
    /**
     * 显示安全资源方法
     * @param AttachmentService $attachmentService
     * @return object|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function attachmentAction(AttachmentService $attachmentService)
    {
        $expire_in  = $this->request->param('expire_in/i');
        $access_key = $this->request->param('access_key');
        if($expire_in < time())
        {
            return xml([
                    'Code'    => '404',
                    'Key'     => 'Expired',
                    'Message' => 'Link has expired.',
                ],200,[],['root_node' => 'Error']);
        }
        $attachment_id = AttachmentHelper::transfer_decrypt($access_key,Config::get('local.auth_key'));
        if(empty($attachment_id))
        {
            return xml([
                'Code'    => '500',
                'Key'     => 'NoSuchKey',
                'Message' => 'The specified key does not exist.',
            ],200,[],['root_node' => 'Error']);
        }
        $attachment  = $attachmentService->Attachment->getAttachmentById($attachment_id);
        if(empty($attachment) || !file_exists('.'.$attachment['file_path']))
        {
            return xml([
                'Code'    => '404',
                'Key'     => 'Expired',
                'Message' => 'Link has expired.',
            ],200,[],['root_node' => 'Error']);
        }
        $filename = realpath('.'.$attachment['file_path']);
        $response = app('response');
        $response->contentType($attachment['file_mime']);
        $response->header('Content-Length',$attachment['file_size']);
        readfile($filename);
        return $response;
    }

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
