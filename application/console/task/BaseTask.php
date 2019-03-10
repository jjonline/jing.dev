<?php
/**
 * 异步任务基类
 * ---
 * 异步任务执行的入口方法：execute
 * 所有异步任务均需实现该抽象类
 * ---
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-26 13:27
 * @file BaseTask.php
 */

namespace app\console\task;

use think\Db;

class BaseTask
{
    /**
     * @var string 固定的任务标题，继承类必须重写
     */
    public $title   = '';
    /**
     * @var array 异步任务的执行日志数组
     */
    public $result  = [];
    /**
     * @var string 项目根目录
     */
    protected $root_path;
    /**
     * @var string 导出文件的临时存储目录，该文件夹下的文件会不定时清理
     */
    protected $temp_path;


    public function __construct()
    {
        $this->root_path = app()->getRootPath().'manage/';
        $this->temp_path = $this->root_path.'_temp/'.date('Ym').'/'.substr(uniqid(), 0, 2).'/';
        if (!is_dir($this->temp_path)) {
            mkdir($this->temp_path, 0777, true);
        }
    }

    /**
     * 异步任务执行的入口方法
     * @param array $param
     */
    public function execute(array $param)
    {
    }

    /**
     * 标记任务已开始
     * @param $async_task_id
     * @return bool
     */
    protected function start($async_task_id)
    {
        if (!empty($async_task_id)) {
            try {
                $this->log('开始执行');
                $ret = Db::name('async_task')->update([
                    'title'         => $this->title,// 回写任务标题
                    'id'            => $async_task_id,
                    'task_status'   => 1,
                    'delivery_time' => date('Y-m-d H:i:s')
                ]);
                return !!$ret;
            } catch (\Throwable $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 任务执行成功并完毕
     * @param string $async_task_id 执行任务的ID
     * @param string $result        执行任务的结果字符串
     * @return bool
     */
    protected function finishSuccess($async_task_id, $result = '')
    {
        if (!empty($async_task_id)) {
            try {
                $this->log('执行成功');
                $ret = Db::name('async_task')->update([
                    'title'         => $this->title,// 回写任务标题
                    'id'            => $async_task_id,
                    'task_status'   => 2,
                    'result'        => empty($result) ? implode("\n", $this->log()) : $result,
                    'finish_time'   => date('Y-m-d H:i:s')
                ]);
                return !!$ret;
            } catch (\Throwable $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 任务执行失败并结束
     * @param string $async_task_id 执行任务的ID
     * @param string $result        执行任务的结果字符串
     * @return bool
     */
    protected function finishFail($async_task_id, $result = '')
    {
        if (!empty($async_task_id)) {
            try {
                $this->log('执行失败');
                $ret = Db::name('async_task')->update([
                    'title'         => $this->title,// 回写任务标题
                    'id'            => $async_task_id,
                    'task_status'   => 3,
                    'result'        => empty($result) ? implode("\n", $this->log()) : $result,
                    'finish_time'   => date('Y-m-d H:i:s')
                ]);
                return !!$ret;
            } catch (\Throwable $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 设置导出任务生成的导出文件地址
     * @param string $async_task_id     任务ID
     * @param string $export_file_path  导出任务生成的文件完整绝对路径
     * @return bool
     */
    protected function saveExportFilePath($async_task_id, $export_file_path)
    {
        $file_path = '/'.str_replace($this->root_path, '', $export_file_path);
        if (!empty($async_task_id)) {
            try {
                $ret = Db::name('async_task')->update([
                    'id'            => $async_task_id,
                    'export_path'   => $file_path
                ]);
                return !!$ret;
            } catch (\Throwable $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 生成一个随机的无文件后缀的文件名
     * @return string
     */
    protected function generateNoSuffixRandomFileName()
    {
        return uniqid().'_'.date('YmdHis');
    }

    /**
     * 生成导出文件的下载html文字连接
     * @param string $file_path 导出文件的存储完整路径
     * @return array
     */
    protected function generateExportLink($file_path)
    {
        $link = '/'.str_replace($this->root_path, '', $file_path);
        $link = '<a href="'.$link.'" target="_blank"><i class="fa fa-download"></i> 点此下载导出的文件</a>';
        return $this->log($link);
    }

    /**
     * 统一记录和处理异步任务过程中的日志，用于回写result字段
     * @param string $log
     * @return array
     */
    protected function log($log = '')
    {
        if (!empty($log)) {
            $date_time      = date_create();
            $this->result[] = '['.$date_time->format('m-d H:i:s.u').'] '.$log;
        }
        return $this->result;
    }
}
