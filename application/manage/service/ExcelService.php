<?php
/**
 * Excel导入服务，不支持数据导出功能
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-18 15:36
 * @file ExcelService.php
 */

namespace app\manage\service;

use app\common\helpers\GenerateHelper;
use app\common\model\PhoneData;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Response;

class ExcelService
{

    /**
     * 导出Excel批量导入所使用的模板文件
     * @param string $source_name 不带后缀的模板文件名称
     * @param string $export_name 不带后缀的下载的模板文件的命名
     * @throws
     */
    public function exportTemplate($source_name,$export_name)
    {

        $source = __DIR__.'/template/'.trim($source_name,'/').'.xls';
        if(!file_exists($source))
        {
            return false;
        }
        $Excel = IOFactory::createReaderForFile($source)->load($source);

        // $export_name = mb_convert_encoding($export_name,'GBK');
        $Excel->getProperties()->setCreator($export_name)
            ->setLastModifiedBy($export_name)
            ->setTitle($export_name)
            ->setSubject($export_name)
            ->setDescription($export_name);

        ob_start();

        ob_clean();

        $response = new Response();
        $response->header([
            'Content-Type' => 'applicatoin/octet-stream',
            'Content-Disposition' => 'attachment;filename="'.$export_name.'.xls"',
            'Cache-Control' => 'max-age=0'
        ]);
        $response->expires(strtotime('-30 days'));
        $writer = IOFactory::createWriter($Excel, 'Xls');
        $writer->save('php://output');

        return $response;
    }

    /**
     * 解析批量导入的Excel文件中的账号数据
     * @param string $file excel文件保存完整路径
     * @return []
     */
    public function importAccountData($file)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态后导入'];
        }
        try{
            $Excel = IOFactory::createReaderForFile($file)->load($file);
            $data  = $Excel->getActiveSheet()->toArray(null, true, false, false);
            if(is_array($data) && count($data) > 2)
            {
                // 处理批量导入的数据
                $data = array_slice($data,2);
                $accountData = [];
                $accounts    = [];//导入新账户的一维数组
                foreach ($data as $key => $value)
                {
                    if(empty($value[0]))
                    {
                        return ['error_code' => -1,'error_msg' => '解析Excel文件出错，请严格按模板格式录入批量数据'];
                    }
                    $_account = [];
                    $_account['account']   = trim($value[0]);
                    $_account['nick_name'] = !empty($value[1]) ? trim($value[1]) : '';
                    $_account['sex']       = $this->getSexFromContext($value[2]);
                    $_account['remark']    = !empty($value[3]) ? trim($value[3]) : '';
                    $_account['dept_id1']  = $dept1['dept_id'];
                    $_account['dept_id2']  = $dept2['dept_id'];
                    $_account['id']        = GenerateHelper::uuid();
                    $_account['user_id']   = $user_id;

                    $accounts[]    = trim($value[0]);
                    $accountData[] = $_account;
                }

                $PhoneDataModel = new PhoneData();
                $ret = $PhoneDataModel->batchInsert($accountData,$accounts,$user_id);
                return $ret ? ['error_code' => 0,'error_msg' => '导入成功'] : ['error_code' => -2,'error_msg' => '写入数据库失败'];
            }
            return ['error_code' => -3,'error_msg' => '解析Excel文件出错，请严格按模板格式录入批量数据'];
        }catch (\Throwable $e){
            return ['error_code' => -4,'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 依据文字转换为数据库存储的性别数据
     * @param $context
     * @return int
     */
    private function getSexFromContext($context)
    {
        if(empty($context))
        {
            return 0;
        }
        if(trim($context) == '男')
        {
            return 1;
        }
        if(trim($context) == '女')
        {
            return 2;
        }
        // 其他情况
        return 0;
    }
}
