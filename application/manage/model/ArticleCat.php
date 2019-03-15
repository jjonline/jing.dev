<?php
/**
 * 文章分类模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-15 21:32:00
 * @file ArticleCat.php
 */

namespace app\manage\model;

use app\common\helper\ArrayHelper;
use app\common\helper\TreeHelper;
use think\Model;

class ArticleCat extends Model
{
    /**
     * 主键查询
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataById($id)
    {
        if (empty($id)) {
            return [];
        }
        $result = $this->where('id', $id)->find();
        return empty($result) ? [] : $result->toArray();
    }

    /**
     * 读取全部文章分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticleCatList()
    {
        $result = $this->field(true)->select();
        return $result->isEmpty() ? [] : $result->toArray();
    }

    /**
     * 读取全部文章分类列表并分组
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticleCatListTree()
    {
        $data  = $this->getArticleCatList();
        $group = ArrayHelper::group($data, 'level');
        $cats  = ArrayHelper::sortMultiTree($data, $group[1], 'id', 'parent_id');
        $cats  = TreeHelper::vTree($cats);

        // 处理tree形式的分类名称
        $begin_level = 0;
        foreach ($cats as $key => $value) {
            $cats[$key]['name_format1'] = $value['name'];
            $cats[$key]['name_format2'] = $value['name'];
            if ($value['level'] > 1) {
                $cats[$key]['name_format1'] = str_repeat(
                    '&nbsp;&nbsp;├&nbsp;&nbsp;',
                    $value['level'] - $begin_level
                ). $cats[$key]['name'];

                $cats[$key]['name_format2'] = str_repeat(
                    '&nbsp;',
                    floor(pow(($value['level'] - 1), 2.5) * 2)
                ) . '└─' . $cats[$key]['name'];
            }
        }
        return $cats;
    }

    /**
     * 父分类ID获取子分类列表
     * @param $parent_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticleCatInfoByParentId($parent_id)
    {
        if (empty($parent_id)) {
            return [];
        }
        $dept = $this->where('parent_id', $parent_id)->select();
        return !$dept->isEmpty() ? $dept->toArray() : [];
    }

    /**
     * 父分类ID获取所有该分类下的子分类ID
     * @param $parent_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getChildArticleCatByParentId($parent_id)
    {
        if (empty($parent_id)) {
            return [];
        }
        $dept_ids = [];
        $children_dept = $this->getArticleCatInfoByParentId($parent_id);
        if (!empty($children_dept)) {
            foreach ($children_dept as $key => $value) {
                $dept_ids[] = $value['id'];
                $children   = $this->getChildArticleCatByParentId($value['id']);
                $dept_ids   = array_merge($dept_ids, $children);
            }
        }
        return $dept_ids;
    }
}
