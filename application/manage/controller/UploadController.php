<?php
/**
 * 后端文件上传统一管理控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-05 11:04
 * @file UploadController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;
use app\common\service\AttachmentService;

class UploadController extends BaseController
{
    /**
     * 上传文件
     * @param AttachmentService $attachmentService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uploadAction(AttachmentService $attachmentService)
    {
        $result = $attachmentService->uploadFile($this->request);
        return $this->asJson($result);
    }

}
