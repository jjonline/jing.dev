<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-25 17:27
 * @file StaticResource.php
 */

namespace app\common\model;

use think\Db;
use think\Model;
use think\Request;

class StaticResource extends Model
{

    /**
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getResourceById($id)
    {
        $data = $this->find($id);
        return $data ? $data->toArray() : [];
    }

    /**
     * 获取已有素材的标签
     * @throws
     * @return []
     */
    public function getUserTagList()
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return [];
        }
        $tags = $this->field(['id','tag'])->where(['user_id' => $user_id])->order(['create_time' => 'DESC'])->group('tag')->select();
        if(empty($tags))
        {
            return [];
        }
        return $tags->toArray();
    }

    /**
     * 智能保存资源数据
     * @param array $data
     * @throws
     * @return []
     */
    public function smartSave($data = [])
    {
        if(empty($data) || empty($data['resource_md5']) || empty($data['user_id']))
        {
            return ['error_code' => -1,'error_msg' => '素材数据格式有误'];
        }
        $exist = $this->where(['user_id' => $data['user_id'],'resource_md5' => $data['resource_md5']])->find();
        if(!empty($exist))
        {
            unset($data['id']);
            $ret = $this->where(['id' => $exist['id']])->update($data);
        }else {
            $ret = $this->insert($data);
        }
        return false !== $ret ? ['error_code' => 0,'error_msg' => '保存成功'] : ['error_code' => -1,'error_msg' => '保存失败：写入数据异常'];
    }

    /**
     * 检索
     * @param Request $request
     * @throws
     * @return []
     */
    public function searchResource(Request $request)
    {
        $user_id = session('user_info.id');
        if(empty($user_id))
        {
            return ['error_code' => -1,'error_msg' => '请先登录'];
        }
        $dept1 = session('default_dept1');
        $type  = $request->get('type/s');
        $type  = $type == 'video' ? 'video' : 'picture';
        $query = $request->get('query/s');
        // 关键词模糊检索
        $data  = $this->where([
            'user_id'  => $user_id,
            'dept_id1' => $dept1['dept_id'],
            'type'     => $type,
        ])->order(['create_time' => 'DESC'])->limit(15);
        if(!empty($query))
        {
            $data->where('tag LIKE :query1 OR name LIKE :query2',[
                'query1' => '%'.$query.'%',
                'query2' => '%'.$query.'%',
            ]);
        }
        $data = $data->field(['name','url','type'])->select();
        return $data ?
            ['error_code' => 0,'error_msg' => 'success','data' => $data->toArray()] :
            ['error_code' => 0,'error_msg' => '暂无数据','data' => []];
    }

}
