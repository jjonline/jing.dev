<?php
/**
 * 网站会员服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-05 16:48:00
 * @file CustomerService.php
 */

namespace app\manage\service;

use app\common\helper\ArrayHelper;
use app\common\helper\FilterValidHelper;
use app\common\model\SiteConfig;
use app\common\validate\CustomerValidate;
use app\manage\model\Customer;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class CustomerService
{
    /**
     * @var Customer
     */
    public $Customer;
    /**
     * @var SiteConfig
     */
    public $SiteConfig;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Customer $customer, SiteConfig $siteConfig, LogService $logService)
    {
        $this->Customer   = $customer;
        $this->SiteConfig = $siteConfig;
        $this->LogService = $logService;
    }

    /**
     * 网站会员新增
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        try {
            $customer = $request->post('Customer/a');

            $customerValid = new CustomerValidate();

            if (!$customerValid->check($customer)) {
                throw new Exception($customerValid->getError(), 401);
            }

            // 用户重复检查
            $exist_customer = $this->Customer->getCustomerInfoByUserName($customer['customer_name']);
            if (!empty($exist_customer)) {
                throw new Exception('用户名'.$customer['customer_name'].'已存在');
            }

            // 设置有手机号、邮箱的
            if (!empty($customer['mobile'])) {
                $exist_customer = $this->Customer->getCustomerInfoByMobile($customer['mobile']);
                if (!empty($exist_customer)) {
                    throw new Exception('手机号'.$customer['mobile'].'已存在');
                }
            }
            if (!empty($customer['email'])) {
                $exist_customer = $this->Customer->getCustomerInfoByEmail($customer['email']);
                if (!empty($exist_customer)) {
                    throw new Exception('邮箱'.$customer['mobile'].'已存在');
                }
            }

            // 身份证号
            if (!empty($customer['id_card'])) {
                if (!FilterValidHelper::is_citizen_id_valid($customer['id_card'])) {
                    throw new Exception('身份证号'.$customer['id_card'].'格式有误');
                }
            }

            // 出生年与日为空转换为null
            if (empty($customer['birthday'])) {
                $customer['birthday'] = null;
            }

            // 启用禁用
            $customer['enable'] = empty($_customer['enable']) ? 0 : 1;

            $effect_rows = $this->Customer
                ->allowField([
                    'customer_name',
                    'real_name',
                    'reveal_name',
                    'mobile',
                    'email',
                    'gender',
                    'birthday',
                    'age',
                    'province',
                    'city',
                    'district',
                    'location',
                    'job_organization',
                    'job_number',
                    'job_location',
                    'remark',
                    'motto',
                    'dept_id',
                    'user_id',
                    'enable',
                    'figure_id',
                ]) // insert时允许从表单获取的字段
                ->isUpdate(false)
                ->save($customer);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }

            // 记录日志
            $this->LogService->logRecorder($customer, "新增会员");
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 网站会员快速排序
     * @param Request $request
     * @return array
     */
    public function sort(Request $request)
    {
        try {
            $id   = $request->post('id/i');
            $sort = intval($request->post('sort'));
            if ($sort <= 0) {
                throw new Exception('排序数字有误');
            }
            $customer = $this->Customer->getDataById($id);
            if (empty($customer)) {
                throw new Exception('拟编辑排序的网站会员数据不存在');
            }
            $effect_rows = $this->Customer->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $customer,
                "网站会员快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 启用|禁用
     * @param Request $request
     * @return array
     */
    public function enable(Request $request)
    {
        try {
            $id = $request->post('id/i');
            $_customer = $this->Customer->getDataById($id);
            if (empty($_customer)) {
                throw new Exception('拟启用或禁用的用户数据不存在');
            }

            $effect_rows = $this->Customer->db()->where('id', $id)->update([
                'enable' => $_customer['enable'] ? 0 : 1
            ]);
            if (false == $effect_rows) {
                throw new Exception($_customer['enable'] ? '禁用失败：系统异常' : '启用失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $_customer,
                $_customer['enable'] ? '禁用用户' : '启用用户'
            );
            return [
                'error_code' => 0,
                'error_msg'  => $_customer['enable'] ? '已禁用用户' : '已启用用户',
                'data'       => null
            ];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * 获取前台会员配置
     * @return array
     */
    public function getCustomerLevelConfig()
    {
        try {
            // seed已填充该键名的记录，此处不再累赘检查是否存在该键名的记录
            $config = $this->SiteConfig->getSiteConfigByKey(SiteConfig::CUSTOMER_LEVEL_CONFIG);
            if (empty($config['value'])) {
                return [];
            }
            return json_decode($config['value'], true); // 转换为数组返回
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 保存等级设置
     * @param Request $request
     * @return array
     */
    public function configSave(Request $request)
    {
        try {
            $siteConfig = $this->SiteConfig->getSiteConfigByKey(SiteConfig::CUSTOMER_LEVEL_CONFIG);
            if (empty($siteConfig)) {
                throw new Exception('站点配项未设置会员等级配置项目：'.SiteConfig::CUSTOMER_LEVEL_CONFIG);
            }

            $_config = $request->post('Config/a');
            if (empty($_config['name']) || empty($_config['begin']) || empty($_config['end'])) {
                throw new Exception('参数格式有误');
            }
            $name  = ArrayHelper::uniqueAndTrimOneDimissionArray($_config['name']); // 等级名称去重去除false等价值
            $begin = $_config['begin'];
            $end   = $_config['end'];

            if (count($name) != count($begin) || count($name) != count($end)) {
                throw new Exception('等级参数格式设置有误');
            }

            // 处理积分值，确保是整数
            foreach ($begin as $key => $value) {
                $begin[$key] = intval($value);
                $end[$key]   = intval($end[$key]);
            }

            // 检查开始值、结束值是否严格衔接
            foreach ($end as $key => $value) {
                if (isset($begin[$key + 1])) {
                    if ($value != $begin[$key + 1]) {
                        throw new Exception('上一级的结束值不等于下一级的开始值');
                    }
                }
            }

            // 处理配置
            $config = [];
            foreach ($name as $key => $value) {
                $temporary          = [];
                $temporary['name']  = $value;
                $temporary['begin'] = $begin[$key];
                $temporary['end']   = $end[$key];
                $config[]           = $temporary;
            }

            $effect_rows = $this->SiteConfig->isUpdate(true)
                ->save([
                    'id'    => $siteConfig['id'],
                    'value' => json_encode($config, JSON_UNESCAPED_UNICODE)
                ]);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$_config, $config],
                "会员等级配置"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功', 'data' => null];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }
}
