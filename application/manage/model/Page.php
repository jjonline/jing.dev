<?php
/**
 * 网站单页模型
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-18 21:54:00
 * @file Page.php
 */

namespace app\manage\model;

use think\Model;

class Page extends Model
{
    protected $json = ['config', 'setting'];
    /**
     * @var integer 文字类型
     */
    const CONTENT_TEXT  = 1;
    /**
     * @var integer 图片类型
     */
    const CONTENT_IMAGE = 2;
    /**
     * @var integer 视频类型
     */
    const CONTENT_VIDEO = 3;
    /**
     * @var array 区块类型映射map
     */
    public $content_type_map = [
        self::CONTENT_TEXT  => '文字',
        self::CONTENT_IMAGE => '图片',
        self::CONTENT_VIDEO => '视频',
    ];
    /**
     * @var string 单页面全局缓存tag
     */
    const CACHE_TAG = 'page.cache.tag';

    /**
     * 区块类型标记转可识读文字
     * @param integer $type
     * @return string
     */
    public function getPageConfigTypeReadable($type)
    {
        if (isset($this->content_type_map[$type])) {
            return $this->content_type_map[$type];
        }
        return '';
    }

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
     * 单页唯一标识查找单页数据
     * @param $flag
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataByFlag($flag)
    {
        if (empty($flag)) {
            return [];
        }
        $result = $this->where('flag', $flag)->find();
        return empty($result) ? [] : $result->toArray();
    }

    /**
     * 页面ID查找页面完整数据
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFullPageById($id)
    {
        if (empty($id)) {
            return [];
        }
        $result = $this->db()->alias("page")
            ->leftJoin('attachment attachment', 'attachment.id = page.sample_id')
            ->field([
                'page.*',
                'attachment.file_path'
            ])
            ->where('page.id', $id)
            ->find();
        return empty($result) ? [] : $result->toArray();
    }
}
