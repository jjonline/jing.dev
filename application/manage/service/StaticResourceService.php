<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-25 17:57
 * @file StaticResourceService.php
 */

namespace app\manage\service;


use app\common\helpers\GenerateHelper;
use app\common\model\StaticResource;
use think\File;
use think\Request;

class StaticResourceService
{
    /**
     * @var StaticResource
     */
    public $StaticResource;

    public function __construct(StaticResource $staticResource)
    {
        $this->StaticResource = $staticResource;
    }

    /**
     * 上传保存资源
     * @param Request $request
     * @return []
     */
    public function saveData(Request $request)
    {
        // 检查当前部门数据
        $user_id = session('user_info.id');
        $dept1   = session('default_dept1');
        $dept2   = session('default_dept2');
        if(empty($dept2))
        {
            return ['error_code' => -1,'error_msg' => '请选择业态'];
        }
        $data = $request->post();
        if(empty($data['tag']) || empty($data['name']) || empty($data['file']))
        {
            return ['error_code' => -2,'error_msg' => '素材信息不完整'];
        }
        $file = new File('.'.$data['file']);
        if(!$file->isFile())
        {
            return ['error_code' => -1,'error_msg' => '文件不存在或已丢失'];
        }

        $resource_info = [];
        $resource_info['resource_md5'] = $file->hash('md5');
        $resource_info['url']  = $request->domain().'/'.trim($data['file'],'/');
        $resource_info['name'] = trim($data['name']);
        $resource_info['dir']  = './'.trim($data['file'],'/');
        $resource_info['tag']  = trim($data['tag']);
        $resource_info['dept_id1'] = $dept1['dept_id'];
        $resource_info['dept_id2'] = $dept2['dept_id'];
        $resource_info['user_id']  = $user_id;
        $resource_info['id']   = GenerateHelper::uuid();
        $resource_info['type'] = pathinfo($resource_info['url'],PATHINFO_EXTENSION) == 'mp4' ? 'video' : 'picture';

        // 上传资源到cdn


        // TODO

        return $this->StaticResource->smartSave($resource_info);
    }

    /**
     * 删除素材数据
     * @param Request $request
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deleteResource(Request $request)
    {
        $resource = $this->StaticResource->getResourceById($request->post('id'));
        if(empty($resource))
        {
            return ['error_code' => -1,'error_msg' => '素材数据不存在或已删除'];
        }

        // 删除cdn、本地存储测资源

        //TODO

        // 删除素材库记录
        $ret = $this->StaticResource->where('id',$resource['id'])->delete();
        return $ret ? ['error_code' => 0,'error_msg' => '素材数据已删除','data' => $resource] : ['error_code' => -3,'error_msg' => '删除失败：数据库异常'];
    }

}
