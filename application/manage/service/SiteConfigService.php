<?php
/**
 * 站点配置值设置
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-12-31 21:16
 * @file SiteConfigService.php
 */

namespace app\manage\service;

use app\common\helper\ArrayHelper;
use app\common\model\SiteConfig;
use app\common\service\LogService;
use think\facade\Cache;
use think\Request;

class SiteConfigService
{
    /**
     * @var SiteConfig
     */
    public $SiteConfig;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(SiteConfig $siteConfig, LogService $logService)
    {
        $this->LogService = $logService;
        $this->SiteConfig = $siteConfig;
    }

    /**
     * 获取并处理所有站点配置项目
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSiteConfigListNotHide()
    {
        $_result = $this->SiteConfig->getSiteConfigListNotHide();
        foreach ($_result as $key => $value) {
            $_result[$key]['value'] = $value['value'] ?: $value['default']; // 没有值的结果集将默认值赋值
            if ($value['type'] == 'select' && !empty($value['select_items'])) {
                // select下拉框值转换成数组
                $_result[$key]['select_items'] = json_decode(json_encode($value['select_items']), true);
            }
        }
        $result  = ArrayHelper::group($_result, 'flag');
        return $result;
    }

    /**
     * 按分组保存配置
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $data = $request->post();
            foreach ($data as $key => $value) {
                $config = $this->SiteConfig->getSiteConfigByKey($key);
                if (!empty($config)) {
                    $this->SiteConfig->db()->data(['value' => $value])->where('id', $config['id'])->update();
                    $this->LogService->logRecorder([$config, $value], '修改站点配置');
                }
            }

            // 清理配置缓存
            Cache::clear($this->SiteConfig->ConfigCacheTag);

            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => 500, 'error_msg' => $e->getMessage()];
        }
    }
}
