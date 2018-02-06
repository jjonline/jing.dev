<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-17 21:47:59
 * @file DataController.php
 */

namespace app\manage\controller;


use app\common\model\UserLog;
use app\manage\model\search\PhoneDataSearch;
use app\manage\service\ExcelService;
use app\manage\service\PhoneDataService;
use app\manage\service\UserDepartmentService;
use think\Request;
use think\Response;

class DataController extends BaseController
{

    /**
     * 待加手机号导入\分配\使用详情情况统计页面
     * @param Request $request
     * @param PhoneDataService $phoneDataService
     */
    public function indexAction(Request $request ,
                                UserDepartmentService $userDepartmentService,
                                PhoneDataService $phoneDataService)
    {
        $this->title            = '待加手机号管理 - '.config('local.site_name');
        $this->content_title    = '待加手机号管理';
        $this->content_subtitle = '待加手机号批量导入、分配和使用详情';
        $this->breadcrumb       = [
            ['label' => '待加手机号','url' => url('data/index')],
            ['label' => '待加手机号管理','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        // 待加手机号分配
        $dept1 = session('default_dept1');
        $dept2_with_users = $userDepartmentService->getDeptWithUserTree($dept1['dept_id']);

        $this->assign('dept2_with_users',$dept2_with_users);

        return $this->fetch();
    }

    /**
     * ajax提交分配待添加手机号
     * @param Request $request
     */
    public function allocationAction(Request $request,PhoneDataService $phoneDataService)
    {
        if(!$request->isAjax())
        {
            return $this->asJson(['error_code' => -1,'error_msg' => 'method Err.']);
        }
        $result = $phoneDataService->allocationPhoneData($request);
        if($result['error_code'] === 0)
        {
            // 分配成功，记录日志
            $this->UserLogService->insertUserLog($this->User['id'],UserLog::ALLOCATION_PHONE_DATA,[
                'users' => $request->post('users/a'),
                'type' => $request->post('type'),
                'num' => $request->post('num'),
            ]);
        }
        return $this->asJson($result);
    }

    /**
     * 拟加好友列表
     */
    public function listAction(Request $request , PhoneDataSearch $phoneDataSearch)
    {
        if($request->isAjax())
        {
            return $phoneDataSearch->search($request);
        }
        $this->title            = '待加手机号列表 - '.config('local.site_name');
        $this->content_title    = '待加手机号列表';
        $this->content_subtitle = '加好友用到的一些账号数据管理';
        $this->breadcrumb       = [
            ['label' => '待加手机号列表','url' => url('data/index')],
            ['label' => '待加手机号列表管理','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 下载导入模板
     */
    public function downloadTemplateAction(ExcelService $excelService)
    {
        ob_start();
        $response = new Response();
        $response->header([
            'Content-Type'        => 'application/csv',
            'Content-Disposition' => 'attachment;filename=手机号数据批量导入模板.csv',
            'Cache-Control'       => 'max-age=0'
        ]);
        $response->expires(strtotime('-30 days'));
        ob_clean();
        $csv_file = fopen('php://output', 'a');
        fwrite($csv_file,mb_convert_encoding('手机号','gb2312').'----'.mb_convert_encoding('名称','gb2312'));
        ob_flush();
        fclose($csv_file);
        flush();
        return $response;
        // return $excelService->exportTemplate('accountTemplate','手机号数据批量导入模板');
    }

    /**
     * 上传并导入批量账号数据
     * @param Request $request
     * @param ExcelService $excelService
     */
    public function batchImportAction(Request $request,PhoneDataService $phoneDataService)
    {
        $file = $request->file('csvFile');
        $file_info = $file->validate(['ext' =>'csv'])->move('./upload/safe/phone_data/',$file->hash('md5'));
        if($file_info)
        {
            $excel_file = './upload/safe/phone_data/'.$file_info->getSaveName();
            // 记录上传文件日志
            $this->UserLogService->insertUserLog($this->User['id'],UserLog::UPLOAD_ACCOUNT_FILE,['file' => $excel_file]);
            $result = $phoneDataService->importPhoneData($excel_file);
            // 批量导入成功，记录批量导入动作日志
            if($result['error_code'] == 0)
            {
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::IMPORT_ACCOUNT_DATA,['file' => $excel_file]);
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '批量导入出错：文件解析失败！']);
    }

}
