<?php
/**
 * 公用无权限限制ajax控制器
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-13 13:59
 * @file CommonController.php
 */

namespace app\manage\controller;

use app\common\helpers\StringHelper;
use app\common\model\ChatData;
use app\common\model\MessageData;
use app\common\model\PhoneData;
use app\common\model\SnsFriends;
use app\manage\model\Department;
use app\manage\model\Role;
use app\manage\model\User;
use app\manage\service\DeviceService;
use app\manage\service\StaticResourceService;
use app\manage\service\UserDepartmentService;
use app\manage\service\UserService;
use think\Db;
use think\facade\Cookie;
use think\Request;

class CommonController extends BaseController
{
    /**
     * 切换公司
     * @param Request $request
     * @param UserDepartmentService $userDepartmentService
     */
    public function switchDept1Action(Request $request , UserDepartmentService $userDepartmentService)
    {
        $_dept_id1 = $request->param('dept_id1');
        $dept_id1  = '';
        if($request->isAjax() && session('user_id'))
        {
            $dept1 = $userDepartmentService->getUserDept1List(session('user_id'));
            foreach ($dept1 as $item) {
                if($_dept_id1 == $item['dept_id'])
                {
                    $dept_id1 = $_dept_id1;
                }
            }
            if(!empty($dept_id1))
            {
                Cookie::set('default_dept1',$dept_id1,['expire' => 3600 * 24 * 365]);
            }
            return $this->asJson(['error_code' => 0,'error_msg' => 'success']);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => 'fail']);
    }

    /**
     * 切换业态|部门
     * @param Request $request
     * @param UserDepartmentService $userDepartmentService
     */
    public function switchDept2Action(Request $request , UserDepartmentService $userDepartmentService)
    {
        $_dept_id2 = $request->param('dept_id2');
        $dept_id2  = '';
        if($request->isAjax() && session('user_id'))
        {
            $dept2 = $userDepartmentService->getUserDept2List(session('user_id'));
            foreach ($dept2 as $item) {
                if($_dept_id2 == $item['dept_id'])
                {
                    $dept_id2 = $_dept_id2;
                }
            }
            if(!empty($dept_id2))
            {
                Cookie::set('default_dept2',$dept_id2,['expire' => 3600 * 24 * 365]);
            }else {
                Cookie::set('default_dept2',null);//删除业态cookie
            }
            return $this->asJson(['error_code' => 0,'error_msg' => 'success']);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => 'fail']);
    }

    /**
     * 读取业态列表
     * @param Request $request
     */
    public function getChildDeptAction(Request $request , Department $department)
    {
        $data = $department->getDepartmentLevel2ListByDept1ID($request->param('dept_id1'));
        return $data ? ['error_code' => 0,'error_msg' => 'success','data' => $data] : ['error_code' => -1,'errcode_msg' => '暂无数据'];
    }

    /**
     * 转换中文姓名为用户名拼音
     * @param Request $request
     */
    public function convertUserNameToPinyinAction(Request $request)
    {
        $pinyin = StringHelper::convertToPinyin($request->param('name'));
        return ['error_code' => 0,'error_msg' => 'success','data' => $pinyin];
    }

    /**
     * 管理员ajax读取拟加好友手机号统计数据
     * @param Request $request
     * @param PhoneData $phoneData
     */
    public function getPhoneDataStatisticalAction(Request $request,PhoneData $phoneData)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        $dept1   = session('default_dept1');
        $all_total           = $phoneData->getTotalCountByDeptId1($dept1['dept_id']);//手机号数据总数
        $allocation_total    = $phoneData->getTotalAllocationByDeptId1($dept1['dept_id']);//已分配手机号总数
        $un_allocation_total = $all_total - $allocation_total;//未分配手机号总数
        $used_total          = $phoneData->getTotalUsedByDeptId1($dept1['dept_id']);//已分配且已使用手机号总数
        $un_used_total       = $allocation_total - $used_total;//已分配但未使用手机号总数

        $data = [
            [
                [
                    'name' => '总数',
                    'value'=> $all_total
                ],
                [
                    'name' => '已分配',
                    'value'=> $allocation_total
                ],
                [
                    'name' => '未分配',
                    'value'=> $un_allocation_total
                ],
                [
                    'name' => '已使用',
                    'value'=> $used_total,//$used_total
                ],
                [
                    'name' => '未使用',
                    'value'=> $un_used_total//$un_used_total
                ]
            ],
            [
                [
                    'name' => '已使用',
                    'value'=> $used_total,//$used_total
                ],
                [
                    'name' => '未使用',
                    'value'=> $un_used_total//$un_used_total
                ]
            ],
            [
                [
                    'name' => '已分配',
                    'value'=> $allocation_total
                ],
                [
                    'name' => '未分配',
                    'value'=> $un_allocation_total
                ]
            ]
        ];
        return ['error_code' => 0,'error_msg' => 'success','data' => $data];
    }

    /**
     * ajax检索拉取话术库
     * @param Request $request
     */
    public function GetMessageListAction(Request $request,MessageData $messageData)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        return $this->asJson($messageData->searchMessageData($request));
    }

    /**
     * ajax检索拉取好友库
     * @param Request $request
     * @param SnsFriends $snsFriends
     * @return
     */
    public function GetFriendsAction(Request $request,SnsFriends $snsFriends)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        return $this->asJson($snsFriends->searchFriendsData($request));
    }

    /**
     * ajax获取某设备的编号情况
     * @param Request $request
     * @param SnsFriends $snsFriends
     * @return mixed
     */
    public function GetFriendsNoInfoAction(Request $request,SnsFriends $snsFriends)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        return $this->asJson([
            'error_code' => 0,
            'error_msg' => 'ok',
            'data' => $snsFriends->getNextAccountNoNumberByDeviceID($request->get('device_id')),
        ]);
    }

    /**
     * ajax按编号范围拉取好友数据
     * ---
     * @device_id
     * @start
     * @end
     * ---
     * @param Request $request
     * @param SnsFriends $snsFriends
     */
    public function GetFriendsByNumNoAction(Request $request,SnsFriends $snsFriends)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        return $this->asJson($snsFriends->GetFriendsByNumNo($request));
    }

    /**
     * ajax检索拉取互撩内容
     * @param Request $request
     * @param ChatData $chatData
     */
    public function GetChatAction(Request $request,ChatData $chatData)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        return $this->asJson($chatData->searchChatsData($request));
    }

    /**
     * ajax检索素材
     * @param Request $request
     * @param StaticResourceService $staticResourceService
     * @return mixed
     */
    public function getResourceDataAction(Request $request,StaticResourceService $staticResourceService)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        return $this->asJson($staticResourceService->StaticResource->searchResource($request));
    }

    /**
     * 文件上传接口
     * @param Request $request
     * @return mixed
     */
    public function UploadAction(Request $request)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        // 本地存储目录
        $save_path = './upload/resource/'.date('Ym').'/';
        $file      = $request->file('FileUpload');
        $file_info = $file->validate([
            'ext' =>'png,jpg,jpeg,png,bmp,mp4'//仅允许图片、mp4视频
        ])->move($save_path,$file->hash('md5'));
        if($file_info)
        {
            $file_dir = ltrim($save_path,'.') . $file_info->getSaveName();
            return $this->asJson([
                'error_code' => 0,
                'error_msg'  => '上传成功',
                'data' => [
                    'dir' => $file_dir,
                    'url' => $request->domain().$file_dir,
                    'ext' => $file_info->getExtension() == 'mp4' ? 'video' : 'image'
                ]
            ]);
        }
        return $this->asJson(['error_code' => -2,'error_msg' => $file->getError()]);
    }

    /**
     * 查询公司管理员设备额度
     * @param UserService $userService
     * @return $this|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeviceQuotaInfoAction(UserService $userService)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return $this->asJson(['error_code' => -1,'error_msg' => '请先登录']);
        }
        $dept1 = session('default_dept1');
        $dept_id1 = $dept1['dept_id'];
        $result = $userService->getDept1UserQuotaInfo($user_id,$dept_id1);
        return $this->asJson($result);
    }

}
