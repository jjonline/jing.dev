<?php
/**
 * Service基类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-09-01 11:44
 * @file BaseService.php
 */

namespace app\common\service;

use app\common\model\Menu;
use think\Exception;
use think\Validate;
use Throwable;

class BaseService
{
    /**
     * 统一使用 Validate 检查变量组，檢查不通過拋出異常
     * @param array $_variables 请求体提交过来的变量组关联数组
     *  例如：['vendor_id' => '#@#@#@','id' => 1]
     * @param array $_rule 符合laravel Validator规则的rule检查数组
     *  例如：['vendor_id' => 'required|integer','id' => 'required|string|max:200']
     * @param array $columns_map 待检查变量的中文称呼map
     *  例如：['vendor_id' => '商戶ID','id' => '集點商品ID']
     * @param integer $_code 自定义检查不通过抛出异常的code码，默认0
     * @throws Exception
     */
    public function checkRequestVariablesOrFail(array $_variables, array $_rule, array $columns_map = [], $_code = 0)
    {
        $validate = Validate::make($_rule, [], $columns_map);

        if (!$validate->check($_variables)) {
            throw new Exception($validate->getError(), $_code);
        }
    }

    /**
     * 检查是否super超管
     * @param array $act_user
     * @return bool
     */
    public function isSuperUser(array $act_user)
    {
        // 根用户具备所有权限
        if (!empty($act_user['is_root'])) {
            return true;
        }

        if (empty($act_user['permissions'])) {
            return false;
        }

        if (Menu::PERMISSION_SUPER == $act_user['permissions']) {
            return true;
        }

        return false;
    }

    /**
     * 是否全部数据权限（超管）否则抛异常
     * @param array $act_user
     * @throws Exception
     */
    public function isSuperPermissionOrFail(array $act_user)
    {
        if (!$this->isSuperUser($act_user)) {
            throw new Exception('您不是超管或没有全部数据权限');
        }
    }

    /**
     * 异常转换为json数组格式
     * @param Throwable $exception 抛出的异常对象
     * @return array
     */
    public function exception2Array(Throwable $exception)
    {
        return [
            'error_code' => empty($exception->getCode()) ? 500 : $exception->getCode(),
            'error_msg'  => empty($exception->getMessage()) ? '系统异常' : $exception->getMessage(),
            'data'       => null,
        ];
    }

    /**
     * 响应json数组结构
     * @param string     $msg  响应描述
     * @param null|array $data 可选的响应data数据
     * @param int        $code 可选的响应错误码，默认成功响应即0
     * @return array
     */
    public function success2Array($msg, $data = null, $code = 0)
    {
        return [
            'error_code' => $code,
            'error_msg'  => $msg,
            'data'       => $data,
        ];
    }
}
