<?php
/**
 * 无需登录和权限效验的公共控制器
 * ----
 * 主要用作公用的一些ajax请求后端
 * ----
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-03-10 15:35
 * @file CommonController.php
 */

namespace app\manage\controller;

use app\common\controller\BasicController;
use app\common\helper\AttachmentHelper;
use app\common\helper\FilterValidHelper;
use app\common\helper\StringHelper;
use app\common\service\AttachmentService;
use app\common\service\DepartmentService;
use app\common\service\UtilService;
use app\manage\service\UserService;
use think\Container;
use think\facade\Config;
use think\facade\Session;

class CommonController extends BasicController
{
    /**
     * @var []
     */
    protected $UserInfo;
    /**
     * @var DepartmentService
     */
    protected $DepartmentService;

    /**
     * 获取用户已登录信息
     * ---
     * 用户已登录则能拿到UserInfo
     * ---
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getUserInfo()
    {
        if (!empty($this->UserInfo)) {
            return $this->UserInfo;
        }
        // 登录状态下初始化用户信息
        $UserService = Container::get('app\common\service\UserService');
        if ($UserService->isUserLogin()) {
            $this->DepartmentService     = Container::get('app\common\service\DepartmentService');
            // 初始化User属性
            $this->UserInfo              = Session::get('user_info');
            // 会员可操作的部门列表信息
            $this->UserInfo['dept_auth'] = $this->DepartmentService->getAuthDeptInfoByDeptId(
                $this->UserInfo['dept_id'],
                $this->UserInfo['is_leader']
            );

            return $this->UserInfo;
        }
        return [];
    }

    /**
     * 显示安全资源方法
     * @param AttachmentService $attachmentService
     * @return object|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function attachmentAction(AttachmentService $attachmentService)
    {
        $expire_in  = $this->request->param('expire_in/i');
        $access_key = $this->request->param('access_key');
        if ($expire_in < time()) {
            return xml([
                'Code'    => '404',
                'Key'     => 'Expired',
                'Message' => 'Link has expired.',
            ], 200, [], ['root_node' => 'Error']);
        }
        $attachment_id = AttachmentHelper::transferDecrypt($access_key, Config::get('local.auth_key'));
        if (empty($attachment_id)) {
            return xml([
                'Code'    => '500',
                'Key'     => 'NoSuchKey',
                'Message' => 'The specified key does not exist.',
            ], 200, [], ['root_node' => 'Error']);
        }
        $attachment  = $attachmentService->Attachment->getAttachmentById($attachment_id);
        if (empty($attachment) || !file_exists('.'.$attachment['file_path'])) {
            return xml([
                'Code'    => '404',
                'Key'     => 'Expired',
                'Message' => 'Link has expired or File Not Found.',
            ], 200, [], ['root_node' => 'Error']);
        }
        $filename = realpath('.'.$attachment['file_path']);
        ob_start();
        readfile($filename);
        return response(ob_get_clean(), 200, ['Content-Length' => $attachment['file_size']])
            ->contentType($attachment['file_mime']);
    }

    /**
     * 资源ID转换为安全或正常的资源src
     * @param AttachmentService $attachmentService
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function attachAction(AttachmentService $attachmentService)
    {
        $attachment_id = $this->request->get('id');
        $file_path     = $attachmentService->getAttachmentPathById($attachment_id);
        if (empty($file_path)) {
            $this->redirect('/public/images/no.png');
        }
        $this->redirect($file_path);
    }

    /**
     * ajax请求将中文转换为拼音
     * @return \think\Response
     */
    public function chineseToPinyinAction()
    {
        $chinese = $this->request->param('chinese');
        if (empty($chinese)) {
            return $this->renderJson('待转换中文不得为空', 404);
        }
        $pinyin = StringHelper::convertToPinyin($chinese);
        return $this->renderJson('success', 0, $pinyin);
    }

    /**
     * 获取用户列表
     * @param UserService $userService
     * @return mixed|\think\Response
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserListAction(UserService $userService)
    {
        if ($this->request->isAjax()) {
            if ($this->getUserInfo()) {
                $keyword = $this->request->param('query');
                $result  = $userService->searchUserList($keyword, $this->UserInfo);
                return $this->asJson($result);
            }
            return $this->renderJson('未登录，请先登录', -1);
        }
    }

    /**
     * 手机号查询相关地域信息
     * @param UtilService $utilService
     * @return mixed|\think\Response
     */
    public function getAreaInfoByMobileAction(UtilService $utilService)
    {
        $mobile = $this->request->param('mobile');
        if (!FilterValidHelper::is_phone_valid($mobile)) {
            return $this->renderJson('手机号格式有误', 500);
        }
        $result = $utilService->getAreaInfoByMobile($mobile);
        return $this->asJson($result);
    }
}
