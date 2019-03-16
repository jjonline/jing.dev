<?php
/**
 * 后端文件上传统一管理控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-05 11=>04
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
     */
    public function uploadAction(AttachmentService $attachmentService)
    {
        $result = $attachmentService->uploadFile($this->request);
        return $this->asJson($result);
    }

    /**
     * UEditor的后端图片和文件上传控制器
     * @param AttachmentService $attachmentService
     * @return mixed|\think\Response
     */
    public function handleAction(AttachmentService $attachmentService)
    {
        $action = $this->request->get('action');
        if ($action == 'config') {
            return $this->asJson($attachmentService->getUEditorConfig());
        }

        if (in_array($action, ['uploadImage','uploadFile'])) {
            // 处理文件上传
            $result = $attachmentService->uploadFile($this->request);
            // 处理成UEditor所需的json格式
            $ue_result = [
                "state"    => $result['error_code'] == 0 ? 'SUCCESS' : $result['error_msg'],// SUCCESS用于标记成功
                "url"      => empty($result['data']['file_path']) ? '' : $result['data']['file_path'],
                "title"    => empty($result['data']['file_origin_name']) ? '' : $result['data']['file_origin_name'],
                "original" => empty($result['data']['file_origin_name']) ? '' : $result['data']['file_origin_name'],
                "type"     => empty($result['data']['file_origin_name'])
                    ? '' : strtolower(strrchr($result['data']['file_origin_name'], '.')),
                "size"     => empty($result['data']['file_size']) ? '' : $result['data']['file_size'],
            ];
            return $this->asJson($ue_result);
        }
        return $this->renderJson('error');
    }
}
