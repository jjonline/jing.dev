<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-01-12 10:27
 * @file DepartmentService.php
 */

namespace app\manage\service;

use app\common\helpers\GenerateHelper;
use app\manage\model\Department;
use app\manage\model\UserDepartment;
use think\Db;
use think\facade\Cache;
use think\Request;

class DepartmentService
{

    /**
     * @var Department
     */
    public $Department;
    /**
     * @var UserDepartment
     */
    public $UserDepartment;

    public function __construct(Department $Department,UserDepartment $userDepartment)
    {
        $this->Department = $Department;
        $this->UserDepartment = $userDepartment;
    }

    /**
     * 通过顶级部门ID获取顶级部门信息，若该ID不是顶级部门返回空数组
     * @param $dept_id
     * @throws
     * @return []
     */
    public function getTopDeptById($dept_id)
    {
        $data = $this->Department->getDeptById($dept_id);
        if(empty($data) || $data['level'] != 1 || !empty($data['parent_id']))
        {
            return [];
        }
        return $data;
    }

    /**
     * 获取指定公司ID的业态部门列表
     * @param $dept_id1
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDep2ListByDept1Id($dept_id1)
    {
        $data = $this->Department->field(['id','name'])
              ->where('parent_id',$dept_id1)
              ->order('sort','ASC')
              ->select();
        return $data ? $data->toArray() : [];
    }

    /**
     * 保存部门数据
     * @param Request $request
     * @return []
     */
    public function saveData(Request $request)
    {
        $data = $request->post('Dept/a');
        if(empty($data['name']))
        {
            return ['error_code' => -1,'error_msg' => '核心参数缺失'];
        }
        // 组装最多2级的数据
        $Department = [];
        $Department['sort']   = intval($data['sort']) < 0 ? 0 : intval($data['sort']);
        $Department['remark'] = trim($data['remark']);
        $Department['name']   = trim($data['name']);
        if(!empty($data['parent_id']))
        {
            $parent = $this->getTopDeptById($data['parent_id']);
            if(empty($parent))
            {
                return ['error_code' => -1,'error_msg' => '所属公司数据有误'];
            }
            $Department['parent_id'] = $data['parent_id'];
            $Department['level']     = 2;
        }else {
            $Department['level']     = 1;
        }
        // 编辑模式
        if(isset($data['id']))
        {
            $exist = $this->Department->getDeptById($data['id']);
            if(empty($exist))
            {
                return ['error_code' => -1,'error_msg' => '拟编辑部门数据不存在'];
            }
            $ret = $this->Department->isUpdate(true)->save($Department,['id' => $data['id']]);
        }else {
            // 新增模式
            $Department['id'] = GenerateHelper::uuid();
            $ret = $this->Department->data($Department)->isUpdate(false)->save();
        }
        // 编辑菜单之后清空缓存
        Cache::clear();
        return $ret !== false ? ['error_code' => 0,'error_msg' => '保存成功'] : ['error_code' => -1,'error_msg' => '系统异常，保存失败'];
    }

    /**
     * 新增或编辑业态部门
     * @param Request $request
     * @return array
     */
    public function saveDept2(Request $request)
    {
        $data = $request->post('Dept/a');
        $default_dept1 = session('default_dept1');
        if(empty($data['name']))
        {
            return ['error_code' => -1,'error_msg' => '核心参数缺失'];
        }
        // 组装最多2级的数据
        $Department = [];
        $Department['sort']      = intval($data['sort']) < 0 ? 0 : intval($data['sort']);
        $Department['remark']    = trim($data['remark']);
        $Department['name']      = trim($data['name']);
        $Department['parent_id'] = $default_dept1['dept_id'];
        $Department['level']     = 2;
        // 编辑模式
        if(isset($data['id']))
        {
            $exist = $this->Department->getDeptById($data['id']);
            if(empty($exist))
            {
                return ['error_code' => -1,'error_msg' => '拟编辑部门数据不存在'];
            }
            $ret = $this->Department->isUpdate(true)->save($Department,['id' => $data['id']]);
        }else {
            // 新增模式
            $Department['id'] = GenerateHelper::uuid();
            $ret = $this->Department->data($Department)->isUpdate(false)->save();
        }
        // 编辑菜单之后清空缓存
        Cache::clear();
        return $ret !== false ?
               ['error_code' => 0,'error_msg' => '保存成功','data' => isset($exist) ? $exist : $Department] :
               ['error_code' => -1,'error_msg' => '系统异常，保存失败'];
    }

    /**
     * 删除业态--公司下的部门
     * @param Request $request
     * @return array
     * @throws \Think\Exception
     */
    public function deleteDept2(Request $request)
    {
        $dept = $this->Department->getDeptById($request->post('id'));
        if(empty($dept) || is_null($dept['parent_id']) || $dept['level'] != 2)
        {
            return ['error_code' => -1,'error_msg' => '业态不存在或不允许越权删除'];
        }
        // 检查部门是否有用户
        $count = $this->UserDepartment->getCountUserByDeptId2($dept['id']);
        if($count > 0)
        {
            return ['error_code' => -1,'error_msg' => '业态存在用户，请先删除该业态下的用户'];
        }
        // 硬删除部门数据
        $ret = $this->Department->where('id',$dept['id'])->delete();
        return $ret ? ['error_code' => 0,'error_msg' => '部门删除成功','data' => $dept] : ['error_code' => -1,'error_msg' => '删除失败：系统异常'];
    }
}