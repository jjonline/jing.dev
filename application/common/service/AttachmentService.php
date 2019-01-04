<?php
/**
 * 附件资源服务，处理文件上传业务逻辑
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-05 14:55
 * @file AttachmentService.php
 */

namespace app\common\service;

use app\common\helper\AttachmentHelper;
use app\common\helper\GenerateHelper;
use app\common\model\Attachment;
use think\facade\Config;
use think\Image;
use think\Request;

class AttachmentService
{
    /**
     * @var Attachment
     */
    public $Attachment;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(
        Attachment $attachment,
        LogService $logService
    ) {
        $this->LogService = $logService;
        $this->Attachment = $attachment;
    }

    /**
     * 上传文件
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uploadFile(Request $request)
    {
        //检查上传文件的允许类型
        $paramDir       =   $request->param('file_type');
        $allowedExt     =   array(
            'image' =>  array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' =>  array('swf', 'flv'),
            'media' =>  array('swf', 'flv', 'mp4'),//媒体类型 仅允许该三种
            'file'  =>  array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pdf', 'txt', 'zip', 'rar', 'gz', 'bz2','7z'),
            'music' =>  array('mp3','mp4','m4a','amr'),// 媒体语音音乐类型
        );
        // 补充未知file_type（即未指定file_type参数的上传请求）
        $origin_file = $request->file('File');
        if (empty($origin_file)) {
            $file_obj_arr = $request->file();
            if (empty($file_obj_arr)) {
                return ['error_code' => 500,'error_msg' => '未检测到上传的文件，本系统指定的上传文件域为：File'];
            }
            // 文件域不为File，自主读取第一个进行处理
            $origin_file  = array_shift($file_obj_arr);
        }
        // 获取上传文件的后缀
        $file_ext = strtolower(pathinfo($origin_file->getInfo('name'), PATHINFO_EXTENSION));
        if (empty($paramDir)) {
            foreach ($allowedExt as $key => $value) {
                if (in_array($file_ext, $value)) {
                    $paramDir = $key;
                    break;
                }
            }
        }

        // 初步检查文件后缀要求是否通过
        if (!isset($allowedExt[$paramDir])) {
            //上传的类型或参数错误
            return ['error_code' => 500,'error_msg' => '不允许上传的文件类型：'.$file_ext];
        }

        // 检查用户级别的资源重复
        $exist_attachment = $this->Attachment->getAttachmentByUserFileSha1($origin_file->hash('sha1'));
        if (!empty($exist_attachment)) {
            //限定了文件类型
            $extension = strtolower(pathinfo($exist_attachment['file_path'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExt[$paramDir])) {
                return ['error_code' => 500,'error_msg' => '不允许上传的文件后缀：'.$extension];
            }
            // 存在的文件使用安全域
            if ($exist_attachment['is_safe']) {
                $exist_attachment['file_path'] = AttachmentHelper::generateSafeAttachmentPath($exist_attachment['id']);
            }
            return ['error_code' => 0,'error_msg' => '上传成功：文件曾上传过','data' => $exist_attachment];
        }
        //不同类型文件存储的根目录 如图片则是./Uploads/Image/
        $saveDir = './uploads/'.$paramDir.'/'.date('Y').'/';
        $file    = $origin_file->validate(['ext' => $allowedExt[$paramDir]])
            ->rule('sha1')
            ->move($saveDir);
        if (!$file) {
            return ['error_code' => 500,'error_msg' => $origin_file->getError()];
        }

        // 记录attachment
        $attachment                     = [];
        $attachment['id']               = GenerateHelper::uuid();
        $attachment['user_id']          = $request->session('user_id') ? $request->session('user_id') : 0;
        $attachment['file_origin_name'] = $origin_file->getInfo('name');
        $attachment['file_name']        = $file->getFilename();
        $attachment['file_path']        = trim($saveDir.$file->getSaveName(), '.');
        $attachment['file_mime']        = $file->getMime();
        $attachment['file_size']        = $file->getSize();
        $attachment['file_sha1']        = $file->hash('sha1');

        // 是否安全资源
        $is_safe                        = $request->has('is_safe', 'post');
        $attachment['is_safe']          = $is_safe ? 1 : 0;

        // 如果图片增加图片高宽尺寸
        if ($paramDir == 'image') {
            try {
                $Image                      = Image::open($file);
                $attachment['image_width']  = $Image->width();
                $attachment['image_height'] = $Image->height();
            } catch (\Throwable $e) {
                // 图片读取出错，将该文件删除然后返回错误信息
                $exception_file = $saveDir.$file->getSaveName();
                is_file($exception_file) && unlink($exception_file);
                return ['error_code' => 500,'error_msg' => '上传失败：'.$e->getMessage()];
            }
        }
        $result = $this->Attachment->isUpdate(false)->data($attachment)->save();
        if (false !== $result) {
            // 上传成功，记录日志
            $this->LogService->logRecorder($attachment['file_path'], '上传文件');
        }
        // 如果是安全资源，返回的资源地址修改为安全地址
        if ($is_safe) {
            $attachment['file_path'] = AttachmentHelper::generateSafeAttachmentPath($attachment['id']);
        }
        // 无论attachment是否成功 文件上传完成均返回文件信息数组
        return ['error_code' => 0,'error_msg' => '上传成功：新增文件','data' => $attachment];
    }

    /**
     * 通过资源ID获取资源地址
     * @param string $attachment_id 主键ID
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAttachmentPathById($attachment_id)
    {
        $attachment = $this->Attachment->getAttachmentById($attachment_id);
        if (empty($attachment)) {
            return '';
        }
        // 检查是否安全资源并生成资源Url  后续切换cdn修改此处代码即可
        if (!!$attachment['is_safe']) {
            $path = AttachmentHelper::generateSafeAttachmentPath($attachment['id']);
        } else {
            $path = $attachment['file_path'];
        }
        // 当前返回本地带域名Url 切换cdn后再行修改
        return app('request')->domain().$path;
    }
}
