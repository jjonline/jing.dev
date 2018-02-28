<?php
/**
 * 部门管理服务
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-02-27 18:08
 * @file DepartmentService.php
 */

namespace app\common\service;

use app\common\helper\TreeHelper;
use app\common\model\Department;
use think\Request;

class DepartmentService
{
    /**
     * @var Department
     */
    public $Department;

    public function __construct(Department $department)
    {
        $this->Department = $department;
    }

    public function save(Request $request)
    {

    }

    public function sort(Request $request)
    {

    }

    public function delete(Request $request)
    {

    }

    /**
     * 获取部门树状列表
     * --
     * 按层级显示竖向数、通过部门名称前加标识符来区分
     * --
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDeptTreeList()
    {
        $dept = $this->Department->getDeptList();
        foreach ($dept as $key => $value)
        {
            $dept[$key]['name_format1'] = $value['name'];
            $dept[$key]['name_format2'] = $value['name'];
            if($value['level'] > 1)
            {
                $dept[$key]['name_format1']   = str_repeat('&nbsp;&nbsp;├&nbsp;&nbsp;',$value['level'] - 1).$value['name'];
                $dept[$key]['name_format2']   = str_repeat('&nbsp;',floor(pow(($value['level'] - 1),1.8) * 2)).'└─'.$value['name'];
            }
        }
        return TreeHelper::vTree($dept);
    }
}
