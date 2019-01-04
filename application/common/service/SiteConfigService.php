<?php
/**
 * 站点配置服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-06-16 15:15
 * @file SiteConfigService.php
 */

namespace app\common\service;

use app\common\model\SiteConfig;
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

    public function __construct(
        SiteConfig $siteConfig,
        LogService $logService
    ) {
        $this->SiteConfig = $siteConfig;
        $this->LogService = $logService;
    }

    /**
     * 新增|编辑站点配置项
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(Request $request)
    {
        $site_config = $request->param('SiteConfig/a');
        if (empty($site_config['type'])) {
            return ['error_code' => 400,'error_msg' => '请选择配置项类型'];
        }
        if ($site_config['type'] == 'select') {
            if (empty($site_config['select_items'])) {
                return ['error_code' => 400,'error_msg' => '请按格式设置select选项值'];
            }
            $select_items = $this->parseVal($site_config['select_items']);
            if (empty($select_items)) {
                return ['error_code' => 400,'error_msg' => 'select选项值格式有误'];
            }
            $site_config['select_items'] = $select_items;
        } else {
            $site_config['select_items'] = null;
        }
        if (empty($site_config['key'])) {
            return ['error_code' => 400,'error_msg' => '配置项Key不得为空'];
        }
        if (empty($site_config['name'])) {
            return ['error_code' => 400,'error_msg' => '配置项名称不得为空'];
        }
        if (empty($site_config['description'])) {
            return ['error_code' => 400,'error_msg' => '配置项说明不得为空'];
        }
        if (empty($site_config['flag'])) {
            return ['error_code' => 400,'error_msg' => '分组flag不得为空'];
        }

        $is_edit    = !empty($site_config['id']);
        $exist_data = $this->SiteConfig->getSitConfigValueByKey($site_config['key']);
        if ($is_edit) {
            $repeat_data = $this->SiteConfig->getSiteConfigById($site_config['id']);
            if (empty($repeat_data)) {
                return ['error_code' => 400,'error_msg' => '拟编辑配置项不存在'];
            }
            if ($repeat_data['key'] != trim($site_config['key']) && !empty($exist_data)) {
                return ['error_code' => 400,'error_msg' => '配置项key已存在，配置项key不能重复'];
            }
        } else {
            if (!empty($exist_data)) {
                return ['error_code' => 400,'error_msg' => '配置项key已存在，配置项key不能重复'];
            }
        }
        $site_config['sort'] = intval($site_config['sort']) ? intval($site_config['sort']) : 0;

        // 新增或更新
        $effect_num = $this->SiteConfig->allowField(true)->isUpdate($is_edit)->save($site_config);
        if (false !== $effect_num) {
            $this->LogService->logRecorder([$site_config,$request->param()], $is_edit ? '编辑站点配置项' : '新增站点配置信息');
            return ['error_code' => 0,'error_msg' => '保存成功'];
        }
        return ['error_code' => 500,'error_msg' => '保存失败：写入数据出错'];
    }

    /**
     * 解析radio待选值
     * @param $val
     * @return array
     */
    protected function parseVal($val)
    {
        $_val = explode("\n", $val);
        if (!is_array($_val)) {
            return [];
        }
        $_json = [];
        foreach ($_val as $item) {
            $_item = explode('|', $item);
            if (is_array($_item) && count($_item) == 2) {
                $_radio = [
                    'value' => trim($_item[0]),
                    'name'  => trim($_item[1])
                ];
                $_json[] = $_radio;
            }
        }
        return $_json;
    }

    /**
     * 调整站点菜单排序
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sort(Request $request)
    {
        $id   = $request->post('id/i');
        $sort = intval($request->post('sort'));
        if ($sort <= 0) {
            return ['error_code' => 400,'error_msg' => '排序数字有误'];
        }
        $site_config = $this->SiteConfig->getSiteConfigById($id);
        if (empty($site_config)) {
            return ['error_code' => 400,'error_msg' => '拟编辑排序的数据不存在'];
        }
        $ret = $this->SiteConfig->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
        return $ret >= 0 ?
            ['error_code' => 0,'error_msg' => '排序调整成功'] :
            ['error_code' => 500,'error_msg' => '排序调整失败：系统异常'];
    }

    /**
     * 删除站点配置数据
     * @param $id
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $site_config = $this->SiteConfig->getSiteConfigById($id);
        if (empty($site_config)) {
            return ['error_code' => 400,'error_msg' => '拟删除的数据不存在'];
        }
        $this->SiteConfig->db()->where('id', $id)->delete();
        $this->LogService->logRecorder($site_config, '删除站点配置');
        return ['error_code' => 0,'error_msg' => '数据删除成功'];
    }
}
