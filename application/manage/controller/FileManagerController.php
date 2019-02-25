<?php
/**
 * KindEditor后端服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-05 10:49
 * @file FileManagerController.php
 */

namespace app\manage\controller;

use app\common\controller\BaseController;

class FileManagerController extends BaseController
{
    private $Order;//排序规则
    private $RootPath = './uploads/';//上传文件根目录 相对于入口文件
    private $RootUrl  = '/uploads/';//上传目录的url形式

    /**
     * KindEditor图片|文件浏览后端服务
     * @return \think\response\Json
     */
    public function fileManagerAction()
    {
        // 浏览文件类型标识符
        $paramDir    =   $this->request->param('dir');
        // 排序规则 NAME(文件名) 、SIZE(文件大小)、 TYPE(文件类型)
        $this->Order =   strtolower($this->request->param('order', 'name'));
        $paramPath   =   $this->request->param('path', '');//浏览的目录层级
        if (!in_array($paramDir, array('', 'image', 'flash', 'media', 'file','music'))) {
            return json(['error' => 1, 'message' => 'Invalid Directory name.']);
        }
        //指定不同类型浏览目录的相对目录位置 === 添加了文件类型
        $this->RootPath       = $this->RootPath.$paramDir.'/';
        //指定不同类型浏览目录的Url位置 === 添加了文件类型
        $this->RootUrl        = $this->RootUrl.$paramDir.'/';
        //设定各目录参数
        if (!$paramPath) { //浏览上传根目录
            $current_path     = realpath($this->RootPath).'/';
            $current_url      = $this->RootUrl;
            $current_dir_path = '';
            $moveup_dir_path  = '';
        } else { //浏览上传根目录下的子目录
            $current_path     = realpath($this->RootPath).'/'.$paramPath;//当前目录的相对目录形式
            $current_url      = $this->RootUrl.$paramPath;//当前目录的url形式
            $current_dir_path = $paramPath;//当前目录名称
            $moveup_dir_path  = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);//当前目录的上一级目录
        }
        //不允许移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            return json(['error' => 1, 'message' => 'Access is not allowed.']);
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            return json(['error' => 1, 'message' => 'Parameter is not valid.']);
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            return json(['error' => 1, 'message' => 'Directory does not exist.']);
        }
        //遍历目录取得文件信息
        $file_list = array();
        $ext_arr   = array('gif', 'jpg', 'jpeg', 'png', 'bmp');//图片扩展名
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') {
                    continue;
                }
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir']   = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir']   = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext                  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename']     = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime']     = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        //进行结果排序
        usort($file_list, function ($a, $b) {
            if ($a['is_dir'] && !$b['is_dir']) {
                return -1;
            } elseif (!$a['is_dir'] && $b['is_dir']) {
                return 1;
            } else {
                if ($this->Order == 'size') {
                    if ($a['filesize'] > $b['filesize']) {
                        return 1;
                    } elseif ($a['filesize'] < $b['filesize']) {
                        return -1;
                    } else {
                        return 0;
                    }
                } elseif ($this->Order == 'type') {
                    return strcmp($a['filetype'], $b['filetype']);
                } else {
                    return strcmp($a['filename'], $b['filename']);
                }
            }
        });
        $result                     = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path']  = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url']      = $current_url;
        //文件数
        $result['total_count']      = count($file_list);
        //文件列表数组
        $result['file_list']        = $file_list;
        return json($result);
    }

    /**
     * KindEditor文件上传后端服务
     * @return \think\response\Json
     */
    public function uploadFileAction()
    {
        if (!$this->request->isPost()) {
            return json(['error' => 1, 'message' => '403 Forbiden']);
        }
        //检查上传文件的允许类型
        $paramDir       =   $this->request->param('dir');//上传文件类型标识符 也是保存文件的类型目录
        $allowedExt     =   array(
            'image' =>  array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' =>  array('swf', 'flv'),
            'media' =>  array('swf', 'flv', 'mp4'),//媒体类型 仅允许该三种
            'file'  =>  array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pdf', 'txt', 'zip', 'rar', 'gz', 'bz2','7z'),
            'music' =>  array('mp3','mp4'),//音乐类型仅允许mp3、mp4两种
        );
        if (!isset($allowedExt[$paramDir])) {
            //上传的类型或参数错误
            return json(['error' => 1, 'message' => '不允许上传的文件类型']);
        }
        //检查通过 开始处理上传的文件
        $saveDir        =    './uploads/'.$paramDir.'/';//不同类型文件存储的根目录 如图片则是./Uploads/Image/
        //检查文件夹权限
        if (!is_dir($saveDir)) {
            mkdir($saveDir);
        }
        /**
         * 1、检测允许上传的文件后缀
         * 2、检测允许上传的文件大小--暂不限制
         */
        $file_ext = $allowedExt[$paramDir];
        $file     = $this->request->file('imgFile');
        ## 自定义上传文件名 按hash来命名文件也达到了去重的作用
        $fileInfo = $file->validate(['ext' => $file_ext])
                  ->rule('sha1')
                  ->move($saveDir);
        if (!$fileInfo) {
            return json(['error' => 1, 'message' => $file->getError()]);
        }
        //上传成功，记录日志
        $this->logRecorder(trim($saveDir.$fileInfo->getSaveName(), '.'), '上传文件');
        return json(['error' => 0, 'message' => '上传成功','url'=>trim($saveDir.$fileInfo->getSaveName(), '.')]);
    }
}
