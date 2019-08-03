<?php
/**
 * 关键词模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-16 17:57:00
 * @file Tag.php
 */

namespace app\manage\model;

use app\common\helper\ArrayHelper;
use think\facade\Session;
use think\Model;

class Tag extends Model
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
     * tag列表查询tag数组
     * @param string $ids 逗号分隔的tag_id列表
     * @return array
     */
    public function getTagListByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $result = $this->where('id', 'IN', $ids)->column('tag');
        if (empty($result)) {
            return [];
        }
        return $result;
    }

    /**
     * Tag唯一关键词查找
     * @param string $tag
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataByTag($tag)
    {
        if (empty($tag)) {
            return [];
        }
        $result = $this->where('tag', $tag)->find();
        return empty($result) ? [] : $result->toArray();
    }

    /**
     * 关键词自动存储读取
     * @param string $tag tag1|tag2 形式的多个关键词
     * @param string $origin_tag_ids 原来的关键词ID半角逗号分隔的字符串，没有留空
     * @return string 1,3,5 形式的字符串
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function autoSaveTags($tag = '', $origin_tag_ids = '')
    {
        if (empty($tag)) {
            return '';
        }
        $tags = ArrayHelper::filterArrayThenUnique(explode('|', $tag));
        if (empty($tags)) {
            return '';
        }

        // 原始tag_id情况
        $origin_tags = ArrayHelper::filterArrayThenUnique(explode(',', $origin_tag_ids));

        $result = [];
        foreach ($tags as $tag) {
            $exist_tag = $this->db()->field(true)->where('tag', $tag)->find();
            if (empty($exist_tag)) {
                $result[] = $this->db()->insertGetId([
                    'tag'     => $tag,
                    'user_id' => Session::get('user_info.id'),
                    'dept_id' => Session::get('user_info.dept_id'),
                ]);
            } else {
                $result[] = $exist_tag['id'];
            }
        }

        // tag引用次数修改
        $added_ids  = array_diff($result, $origin_tags); // 新增的tag
        $delete_ids = array_diff($origin_tags, $result); // 编辑模式时被删除的tag

        // 新添加的引用全部+1
        $this->setIncTagsQuota($added_ids);

        // 被清理掉的引用全部-1
        $this->setDecTagsQuota($delete_ids);

        return implode(',', $result);
    }

    /**
     * 设置多个关键词的引用减1
     * @param mixed $tag_ids 1,2,34形式的半角逗号分隔的关键词或一维数组形式的[1,2,34]
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setDecTagsQuota($tag_ids)
    {
        if (empty($tag_ids)) {
            return false;
        }
        if (is_string($tag_ids)) {
            $tags = ArrayHelper::filterArrayThenUnique(explode(',', $tag_ids));
        } else {
            $tags = $tag_ids;
        }
        if (empty($tags)) {
            return false;
        }
        $result = $this->where('id', 'IN', $tags)->select();
        if ($result->isEmpty()) {
            return false;
        }
        foreach ($result as $tag) {
            // 未减到0的情况下减去1个引用
            if ($tag['quota'] >= 1) {
                $this->db()->where('id', $tag['id'])->setDec('quota', 1);
            }
        }
        return true;
    }

    /**
     * 设置多个关键词的引用加1
     * @param mixed $tag_ids 1,2,34形式的半角逗号分隔的关键词或一维数组形式的[1,2,34]
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setIncTagsQuota($tag_ids)
    {
        if (empty($tag_ids)) {
            return false;
        }
        if (is_string($tag_ids)) {
            $tags = ArrayHelper::filterArrayThenUnique(explode(',', $tag_ids));
        } else {
            $tags = $tag_ids;
        }
        if (empty($tags)) {
            return false;
        }
        $result = $this->where('id', 'IN', $tags)->select();
        if ($result->isEmpty()) {
            return false;
        }
        foreach ($result as $tag) {
            $this->db()->where('id', $tag['id'])->setInc('quota', 1);
        }
        return true;
    }
}
