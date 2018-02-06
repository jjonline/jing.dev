<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-22 10:17
 * @file ResourceController.php
 */

namespace app\manage\controller;


use app\common\model\UserLog;
use app\manage\model\search\StaticResourceSearch;
use app\manage\service\StaticResourceService;
use think\Request;

class ResourceController extends BaseController
{

    /**
     * 素材数据列表
     * @param Request $request
     * @return mixed
     */
    public function ListAction(Request $request,StaticResourceSearch $staticResourceSearch)
    {
        if($request->isAjax())
        {
            return $staticResourceSearch->search($request);
        }
        $this->title            = '素材数据 - '.config('local.site_name');
        $this->content_title    = '素材数据';
        $this->content_subtitle = '素材数据列表';
        $this->breadcrumb       = [
            ['label' => '素材数据','url' => url('resource/list')],
            ['label' => '素材数据列表','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        return $this->fetch();
    }

    /**
     * 新建素材
     * @param Request $request
     * @return mixed
     */
    public function CreateAction(Request $request,StaticResourceService $staticResourceService)
    {
        if($request->isAjax())
        {
            return $staticResourceService->saveData($request);
        }
        $this->title            = '新建素材数据 - '.config('local.site_name');
        $this->content_title    = '素材数据';
        $this->content_subtitle = '新建素材数据';
        $this->breadcrumb       = [
            ['label' => '素材数据','url' => url('resource/list')],
            ['label' => '新建素材数据','url'  => ''],
        ];
        $this->load_layout_css = false;
        $this->load_layout_js  = true;

        $tags = $staticResourceService->StaticResource->getUserTagList();
        $this->assign('tags',$tags);

        return $this->fetch();
    }

    /**
     * 删除素材数据，同步删除cdn或本地存储
     * @param Request $request
     * @param StaticResourceService $staticResourceService
     * @throws
     */
    public function DeleteAction(Request $request,StaticResourceService $staticResourceService)
    {
        if($request->isAjax() && $request->isPost())
        {
            $result = $staticResourceService->deleteResource($request);
            if($result['error_code'] === 0)
            {
                // 成功，记录日志
                $this->UserLogService->insertUserLog($this->User['id'],UserLog::DELETE_RESOURCE_DATA,
                    array_merge($request->post(),$result['data'])
                );
            }
            return $this->asJson($result);
        }
        return $this->asJson(['error_code' => -1,'error_msg' => '参数有误']);
    }
}
