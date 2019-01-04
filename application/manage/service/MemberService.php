<?php
/**
 * 前台会员管理服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-29 15:18
 * @file MemberService.php
 */

namespace app\manage\service;

use app\common\helper\FilterValidHelper;
use app\common\helper\GenerateHelper;
use app\common\model\Member;
use app\common\service\LogService;
use app\common\service\UserLogService;
use think\Db;
use think\facade\Config;
use think\Request;

class MemberService
{
    /**
     * @var Member
     */
    public $Member;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(
        Member $member,
        LogService $logService
    ) {
        $this->Member         = $member;
        $this->LogService     = $logService;
    }

    public function insert(Request $request, $act_user_info)
    {
        $menu_auth = $act_user_info['menu_auth'];
        if (!in_array($menu_auth['permissions'], ['super','leader'])) {
            return ['error_code' => 500,'error_msg' => '您没有操作权限，仅允许管理员和部门领导进行编辑'];
        }
        $member = $request->post('Member/a');
        if (empty($member) || empty($member['id']) || empty($member['real_name']) || empty($member['user_name']) || empty($member['password'])) {
            return ['error_code' => 500,'error_msg' => '参数有误'];
        }
    }

    /**
     * 后台管理员编辑修改前台用户信息
     * @param Request $request
     * @param $act_user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update(Request $request, $act_user_info)
    {
        $menu_auth = $act_user_info['menu_auth'];
        if (!in_array($menu_auth['permissions'], ['super','leader'])) {
            return ['error_code' => 500,'error_msg' => '您没有操作权限，仅允许管理员和部门领导进行编辑'];
        }
        $member = $request->post('Member/a');
        if (empty($member) || empty($member['id'])) {
            return ['error_code' => 500,'error_msg' => '参数有误'];
        }
        $exist_member = $this->Member->getMemberInfoById($member['id']);
        if (empty($exist_member)) {
            return ['error_code' => 500,'error_msg' => '拟编辑用户不存在'];
        }
        $_member = [];
        // 用户名
        if ($exist_member['user_name'] != $member['user_name']) {
            $repeat_member = $this->Member->getMemberInfoByUserName($member['user_name']);
            if ($repeat_member) {
                return ['error_code' => 500,'error_msg' => '用户名['.$member['user_name'].']已存在'];
            }
            $_member['user_name'] = $member['user_name'];
        }
        // 手机号
        if ($exist_member['mobile'] != $member['mobile']) {
            if (!FilterValidHelper::is_phone_valid($member['mobile'])) {
                return ['error_code' => 500,'error_msg' => '新手机号格式有误'];
            }
            $repeat_member = $this->Member->getMemberInfoByMobile($member['mobile']);
            if ($repeat_member) {
                return ['error_code' => 500,'error_msg' => '手机号['.$member['mobile'].']已存在'];
            }
            $_member['mobile'] = $member['mobile'];
        }
        // 邮箱
        if ($exist_member['email'] != $member['email']) {
            $repeat_member = $this->Member->getMemberInfoByEmail($member['email']);
            if ($repeat_member) {
                return ['error_code' => 500,'error_msg' => '邮箱['.$member['email'].']已存在'];
            }
            $_member['email'] = $member['email'];
        }
        // 修改密码
        if (!empty($member['password'])) {
            if (!FilterValidHelper::is_password_valid($member['password'])) {
                return ['error_code' => 500,'error_msg' => '新密码格式有误，6至18位同时包含字母和数字'];
            }
            $_member['password'] = $this->generateUserPassword($member['password']);
        }
        // 真实姓名
        if (!empty($member['real_name']) && $exist_member['real_name'] != $member['real_name']) {
            $_member['real_name'] = $member['real_name'];
        }
        // 联系电话、座机、办公电话等
        if (!empty($member['telephone']) && $exist_member['telephone'] != $member['telephone']) {
            $_member['telephone'] = $member['telephone'];
        }
        $_member['remark']    = empty($member['remark']) ? '' : $member['remark'];
        $_member['gender']    = isset($member['gender']) && in_array($member['gender'], [-1,0,1]) ? $member['gender'] : -1;
        $_member['auth_code'] = GenerateHelper::makeNonceStr(8);
        $_member['enable']    = empty($member['enable']) ? 0 : 1;

        $_member['province'] = $member['province'];
        $_member['city']     = $member['city'];
        $_member['district'] = $member['district'];
        $_member['address']  = $member['address'];
        $_member['id']       = $member['id'];//更新ID

        // dump($_member);

        Db::startTrans();
        try {
            $this->Member->db()->failException(true)->lock(true)->where('id', $member['id'])->find();
            // 开始更新
            $this->Member->isUpdate(true)->save($_member);
            $this->LogService->logRecorder([$_member,$member], '编辑前台用户信息');

            Db::commit();
            return ['error_code' => 0,'error_msg' => '编辑成功'];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['error_code' => 505,'error_msg' => '编辑失败：'.$e->getMessage()];
        }
    }

    /**
     * 启用|禁用前台用户
     * @param Request $request
     * @param $act_user_info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function enableToggle(Request $request, $act_user_info)
    {
        $member_id = $request->post('id');
        if (empty($member_id) || empty($act_user_info)) {
            return ['error_code' => 400,'error_msg' => '参数缺失'];
        }
        $member    = $this->Member->getMemberInfoById($member_id);
        $menu_auth = $act_user_info['menu_auth'];
        if (!in_array($menu_auth['permissions'], ['super','leader'])) {
            return ['error_code' => 500,'error_msg' => '您没有操作权限，仅允许管理员和部门领导操作'];
        }

        // 启用或禁用用户写入
        $_enable           = [];
        $_enable['id']     = $member['id'];
        $_enable['enable'] = $member['enable'] ? 0 : 1;
        $result            = $this->Member->isUpdate(true)->save($_enable);
        if (false !== $result) {
            $this->LogService->logRecorder($member, '启用或禁用前台用户');
            return ['error_code' => 0,'error_msg' => $member['enable'] ? '禁用完成' : '启用完成'];
        }
        return ['error_code' => 500,'error_msg' => '操作失败：数据库异常'];
    }

    /**
     * 生成密码密文内容
     * @param  $pwd_text string 密码明文
     * @return string
     */
    protected function generateUserPassword($pwd_text)
    {
        return password_hash(Config::get('local.pwd_key').trim($pwd_text), PASSWORD_BCRYPT);
    }

    /**
     * 检查用户密码
     * @param string $pwd_text 用户密码明文
     * @param string $pwd_hash 保存的密码hash
     * @return bool
     */
    protected function checkUserPassword($pwd_text, $pwd_hash)
    {
        return password_verify(Config::get('local.pwd_key').trim($pwd_text), $pwd_hash);
    }
}
