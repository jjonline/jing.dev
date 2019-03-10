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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uploadAction(AttachmentService $attachmentService)
    {
        $result = $attachmentService->uploadFile($this->request);
        return $this->asJson($result);
    }

    /**
     * UEditor的后端图片和文件上传控制器
     * @param AttachmentService $attachmentService
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handleAction(AttachmentService $attachmentService)
    {
        $action = $this->request->get('action');
        if ($action == 'config') {
            return $this->asJson($this->getUEditorConfig());
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

    /**
     * UEditor的配置项目
     * @return array
     */
    private function getUEditorConfig()
    {
        return [
            /* 上传图片配置项 */
            "imageActionName"         => "uploadImage", /* 执行上传图片的action名称 */
            "imageFieldName"          => "File", /* 提交的图片表单名称 */
            "imageMaxSize"            => 2048000, /* 上传大小限制，单位B */
            "imageAllowFiles"         => [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
            "imageCompressEnable"     => true, /* 是否压缩图片,默认是true */
            "imageCompressBorder"     => 1600, /* 图片压缩最长边限制 */
            "imageInsertAlign"        => "none", /* 插入的图片浮动方式 */
            "imageUrlPrefix"          => "", /* 图片访问路径前缀 */
            "imagePathFormat"         => "/uploads/{yyyy}/{file_sha1}", /* 上传保存路径,可以自定义保存路径和文件名格式 */

            /* 上传文件配置 */
            "fileActionName"          => "uploadFile", /* controller里,执行上传视频的action名称 */
            "fileFieldName"           => "File", /* 提交的文件表单名称 */
            "filePathFormat"          => "/uploads/{yyyy}/{file_sha1}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "fileUrlPrefix"           => "", /* 文件访问路径前缀 */
            "fileMaxSize"             => 51200000, /* 上传大小限制，单位B，默认50MB */
            "fileAllowFiles"          => [
                // ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                // ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                // ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                // ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt"
            ], /* 上传文件格式显示 */
        ];
    }
}
