<?php
/**
 * 附件资源服务，处理文件上传业务逻辑
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-05 14:55
 * @file AttachmentService.php
 */

namespace app\common\service;

use app\common\helper\GenerateHelper;
use app\common\model\Attachment;
use app\common\storage\Storage;
use think\Exception;
use think\File;
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
    /**
     * @var Storage
     */
    public $Storage;

    public function __construct(
        Attachment $attachment,
        LogService $logService,
        Storage $storage
    ) {
        $this->LogService = $logService;
        $this->Attachment = $attachment;
        $this->Storage    = $storage;
    }

    /**
     * 上传文件
     * @param Request $request
     * @return array
     */
    public function uploadFile(Request $request)
    {
        try {
            /**
             * 上传文件的允许类型
             */
            $paramDir       =   $request->param('file_type');
            $allowedExt     =   array(
                'image' =>  ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
                'flash' =>  ['swf', 'flv'],
                'media' =>  ['swf', 'flv', 'mp4'],//媒体类型 仅允许该三种
                'file'  =>  ['csv','doc', 'docx', 'xls', 'xlsx', 'ppt', 'pdf', 'txt', 'zip', 'rar', 'gz', 'bz2','7z'],
                'music' =>  ['mp3','mp4','m4a','amr'],// 媒体语音音乐类型
            );

            /**
             * 处理文件上传域，系统默认为单个File
             * 文件域不为File，自主读取第一个文件域进行处理
             */
            $origin_file = $request->file('File');
            if (empty($origin_file)) {
                $file_obj_arr = $request->file();
                if (empty($file_obj_arr)) {
                    throw new Exception('未检测到上传的文件，本系统指定的上传文件域为：File', 500);
                }
                // 文件域不为File，自主读取第一个进行处理
                $origin_file  = array_shift($file_obj_arr);
            }

            /**
             * 获取上传文件的后缀
             */
            $file_ext = strtolower(pathinfo($origin_file->getInfo('name'), PATHINFO_EXTENSION));
            if (empty($paramDir)) {
                foreach ($allowedExt as $key => $value) {
                    if (in_array($file_ext, $value)) {
                        $paramDir = $key;
                        break;
                    }
                }
            }

            /**
             * 初步检查文件后缀要求是否通过
             */
            if (!isset($allowedExt[$paramDir])) {
                //上传的类型或参数错误
                throw new Exception('不允许上传的文件类型：'.$file_ext, 500);
            }

            /**
             * 检查用户级别的资源重复
             * ---
             * 若已上传过则直接返回
             * ---
             */
            $exist_attachment = $this->Attachment->getAttachmentByUserFileSha1($origin_file->hash('sha1'));
            if (!empty($exist_attachment)) {
                //限定了文件类型
                $extension = strtolower(pathinfo($exist_attachment['file_path'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExt[$paramDir])) {
                    throw new Exception('不允许上传的文件后缀：'.$extension, 500);
                }

                // 处理资源信息成前端可直接使用的信息数组
                $this->dealAttachmentToFrontend($exist_attachment);

                return ['error_code' => 0,'error_msg' => '上传成功：文件曾上传过','data' => $exist_attachment];
            }

            /**
             * 不同类型文件存储的根目录 如图片则是./Uploads/Image/
             */
            $saveDir = './uploads/'.$paramDir.'/'.date('Y').'/';
            $file    = $origin_file->validate(['ext' => $allowedExt[$paramDir]])
                     ->rule('sha1')
                     ->move($saveDir);
            if (!$file) {
                return ['error_code' => 500, 'error_msg' => $origin_file->getError()];
            }

            /**
             * 记录attachment信息至数据库
             */
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
            $is_safe                        = !empty($request->param('is_safe'));
            $attachment['is_safe']          = $is_safe ? 1 : 0;

            /**
             * 如果图片增加图片高宽尺寸信息
             */
            if ($paramDir == 'image') {
                try {
                    $Image                      = Image::open($file);
                    $attachment['image_width']  = $Image->width();
                    $attachment['image_height'] = $Image->height();
                } catch (\Throwable $e) {
                    // 图片读取出错，将该文件删除然后返回错误信息
                    $exception_file = $saveDir.$file->getSaveName();
                    is_file($exception_file) && unlink($exception_file);
                    return ['error_code' => 500, 'error_msg' => '上传失败：'.$e->getMessage()];
                }
            }
            $result = $this->Attachment->isUpdate(false)->data($attachment)->save();
            if (false !== $result) {
                // 存储上传记录ok，记录日志
                $this->LogService->logRecorder($attachment['file_path'], '上传文件');
            }

            // 同步资源到外部存储系统
            $this->storageAttachment($attachment);

            // 处理资源信息成前端可直接使用的信息数组
            $this->dealAttachmentToFrontend($attachment);

            // 无论attachment存储至Db是否成功 文件上传完成均返回文件信息数组
            return ['error_code' => 0, 'error_msg' => '上传成功：新增文件', 'data' => $attachment];
        } catch (\Throwable $e) {
            return ['error_code' => 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 通过资源ID获取前端可直接使用的资源一维数组
     * @param string $attachment_id 主键ID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAttachmentById($attachment_id)
    {
        $attachment = $this->Attachment->getAttachmentById($attachment_id);
        if (empty($attachment)) {
            return [];
        }

        // 处理资源成前端可使用
        $this->dealAttachmentToFrontend($attachment);

        return $attachment;
    }

    /**
     * 通过资源ID数组获取前端可直接使用的资源信息二维数组
     * @param array $attachment_ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAttachmentByIds($attachment_ids)
    {
        $attachments = $this->Attachment->getAttachmentByIds($attachment_ids);
        if (empty($attachments)) {
            return [];
        }

        // 循环处理资源成前台可使用的资源信息二维数组
        foreach ($attachments as $key => $value) {
            $this->dealAttachmentToFrontend($value);
            $attachments[$key] = $value;
        }

        return $attachments;
    }

    /**
     * 服务器内部文件移动处理，便于统一管理
     * @param string $local_file_path 服务器本地需加入资源管理的文件路径
     * @param string $relative_dir    拟移动到的文件目录，即uploads目录下的目录名称
     * @param int $is_safe            是否安全文件标记
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function saveLocalAttachment($local_file_path, $relative_dir = 'file', $is_safe = 0)
    {
        if (!is_file($local_file_path)) {
            return ['error_code' => 404, 'error_msg' => '待记录的文件信息不存在'];
        }
        $file      = new File($local_file_path);
        $file_sha1 = $file->hash('sha1');

        // 检查用户级别的资源重复
        $exist_attachment = $this->Attachment->getAttachmentByUserFileSha1($file_sha1);
        if (!empty($exist_attachment)) {
            // 处理资源成前端可使用
            $this->dealAttachmentToFrontend($exist_attachment);
            return ['error_code' => 0, 'error_msg' => '处理成功：已处理过', 'data' => $exist_attachment];
        }

        // 设置文件移动规则
        $upload_file_path = './uploads/'.$relative_dir.'/'.date('Y').'/'.substr($file_sha1, 0, 2).'/';
        $upload_file_name = $file_sha1.'.'.$file->getExtension();
        if (!is_dir($upload_file_path)) {
            mkdir($upload_file_path, 0755, true);
        }

        // 记录attachment
        $attachment                     = [];
        $attachment['id']               = GenerateHelper::uuid();
        $attachment['user_id']          = session('user_id') ? session('user_id') : 0;
        $attachment['file_origin_name'] = $file->getFilename();
        $attachment['file_name']        = $file->getFilename();
        $attachment['file_path']        = trim($upload_file_path.$upload_file_name, '.');
        $attachment['file_mime']        = $file->getMime();
        $attachment['file_size']        = $file->getSize();
        $attachment['file_sha1']        = $file->hash('sha1');
        $attachment['is_safe']          = $is_safe ? 1 : 0;

        // 图片情况下读取图片尺寸
        if (in_array($file->getExtension(), ['gif', 'jpg', 'jpeg', 'bmp', 'png'])) {
            try {
                $Image                      = Image::open($local_file_path);
                $attachment['image_width']  = $Image->width();
                $attachment['image_height'] = $Image->height();
            } catch (\Throwable $e) {
                return ['error_code' => 500,'error_msg' => '图片处理失败，保存文件失败'];
            }
        }
        if (!is_file($upload_file_path.$upload_file_name)) {
            copy($local_file_path, $upload_file_path.$upload_file_name);
        }

        // 记录文件信息
        $result = $this->Attachment->isUpdate(false)->data($attachment)->save();
        if (false !== $result) {
            // 上传成功，记录日志
            $this->LogService->logRecorder($attachment['file_path'], '服务器内部文件格式化存储');
        }

        // 同步资源到外部存储系统
        $this->storageAttachment($attachment);

        // 处理资源成前端可使用
        $this->dealAttachmentToFrontend($attachment);

        return ['error_code' => 0,'error_msg' => '处理成功：新记录','data' => $attachment];
    }

    /**
     * UEditor的配置项目
     * @return array
     */
    public function getUEditorConfig()
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

    /**
     * 依据配置文件存储附件资源到外部存储系统
     * ---
     * 不抛出异常、不终止上传进程，若出错则记录日志
     * ---
     * @param array $attachment 单条附件资源信息数组
     * @return bool
     */
    protected function storageAttachment($attachment)
    {
        try {
            return $this->Storage->put($attachment);
        } catch (\Throwable $e) {
            trace('File '.$attachment['file_path'].' Upload Error. Info = '.$e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * [引用参数形式]依据配置文件处理资源数组信息成前端可直接使用的信息
     * ----
     * 1、依据配置处理资源的访问实际网址，譬如：本地则添加系统域名、外部存储则替换为cdn域名
     * 2、依据资源是否安全资源标记处理资源授权参数和过期时间
     * 3、外部存储处理失败则降级为本地方案
     * ----
     * @param array $attachment 单条附件资源信息数组
     * @return bool
     */
    protected function dealAttachmentToFrontend(&$attachment)
    {
        $attachment['is_image'] = !empty($attachment['image_width']); // 是否图片标记
        try {
            $attachment['local_path'] = $attachment['file_path']; // 保留本地地址备用
            $attachment['file_path']  = $this->Storage->get($attachment);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
