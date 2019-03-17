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
     * 关键词自动存储读取
     * @param string $tag tag1|tag2 形式的多个关键词
     * @param bool $is_update 使用tag的文章、单独页等是否处于更新模式
     * @return string 1,3,5 形式的字符串
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function autoSaveTags($tag = '', $is_update = false)
    {
        if (empty($tag)) {
            return '';
        }
        $tags = ArrayHelper::uniqueAndTrimOneDimissionArray(explode('|', $tag));
        if (empty($tags)) {
            return '';
        }

        $result = [];
        foreach ($tags as $tag) {
            $exist_tag = $this->db()->field(true)->where('tag', $tag)->find();
            if (empty($exist_tag)) {
                $result[] = $this->db()->insertGetId([
                    'tag'     => $tag,
                    'quota'   => 1,
                    'user_id' => Session::get('user_info.id'),
                    'dept_id' => Session::get('user_info.dept_id'),
                ]);
            } else {
                // 新增文章等引用关键词时tag的引用次数+1 编辑情况不需要
                if (!$is_update) {
                    $this->db()->where('id', $exist_tag['id'])->setInc('quota'); // 引用+1
                }
                $result[] = $exist_tag['id'];
            }
        }

        return implode(',', $result);
    }
}
