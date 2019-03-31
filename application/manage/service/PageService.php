<?php
/**
 * 网站单页服务类
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-03-18 21:54:00
 * @file PageService.php
 */

namespace app\manage\service;

use app\common\helper\ArrayHelper;
use app\manage\model\Page;
use app\common\service\LogService;
use think\Exception;
use think\Request;

class PageService
{
    /**
     * @var Page
     */
    public $Page;
    /**
     * @var LogService
     */
    public $LogService;

    public function __construct(Page $page, LogService $logService)
    {
        $this->Page = $page;
        $this->LogService = $logService;
    }

    /**
     * 单页新增|编辑config的设置参数
     * @param Request $request
     * @return array
     */
    public function save(Request $request)
    {
        try {
            $_config = $request->post('Config/a');

            /**
             * 单页面的配置参数json结构
             */
            $config = [
                'use_title'     => false,
                'use_cover'     => false,
                'cover_options' => [
                    'width'  => 0,
                    'height' => 0,
                ],
                'use_keywords'     => false,
                'use_description'  => false,
                'use_template'     => false,
                'template_options' => [
                    [
                        'name'     => '',
                        'template' => '',
                    ]
                ],
                'use_content'     => false,
                'content_options' => [
                    [
                        'id'      => '',
                        'type'    => 1,
                        'name'    => '',
                        'explain' => '',
                        'length'  => 1,
                        'width'   => 1,
                        'height'  => 1,
                    ]
                ],
            ];

            // 页面标识
            if (empty($_config['flag']) || mb_strlen($_config['flag']) > 32) {
                throw new Exception('页面标识不得为空或大于32字符');
            }

            // 使用标题\关键词\描述
            $config['use_title']       = !empty($_config['use_title']);
            $config['use_cover']       = !empty($_config['use_cover']);
            $config['use_keywords']    = !empty($_config['use_keywords']);
            $config['use_description'] = !empty($_config['use_description']);
            $config['use_cover']       = !empty($_config['use_cover']);
            $config['use_template']    = !empty($_config['use_template']);
            $config['use_content']     = !empty($_config['use_content']);

            // 页面封面图设置之后的宽高处理
            if (!empty($_config['use_cover'])) {
                if (empty($_config['cover_options_width'])) {
                    throw new Exception('封面图宽度不得为空');
                }
                if (empty($_config['cover_options_height'])) {
                    throw new Exception('封面图高度不得为空');
                }
                $config['user_cover'] = true;
                $config['cover_options']['width']  = intval($_config['cover_options_width']);
                $config['cover_options']['height'] = intval($_config['cover_options_height']);
            } else {
                // 未启用页面封面图，清空格式化数据
                $config['cover_options'] = [];
            }

            // 启用了模板选项
            if (!empty($_config['use_template'])) {
                $template_name = ArrayHelper::uniqueAndTrimOneDimissionArray($_config['template_options_name']);
                $template_template = ArrayHelper::uniqueAndTrimOneDimissionArray($_config['template_options_template']);
                if (empty($template_name) || empty($template_template)) {
                    throw new Exception('请完善待选模板下拉项中文名称和模板文件名配置');
                }
                if (count($template_name) != count($template_template)) {
                    throw new Exception('请完善待选模板下拉项中文名称和模板文件名存在重复项');
                }
                $template_options = [];
                foreach ($template_name as $key => $value) {
                    $_template_options['name']     = $value;
                    $_template_options['template'] = $template_template[$key];
                    // 多个选项
                    $template_options[] = $_template_options;
                }

                // 赋值
                $config['use_template'] = true;
                $config['template_options'] = $template_options;
            } else {
                // 未启用模板选项，清空格式化数据
                $config['template_options'] = [];
            }

            // 启用了正文区块
            if (!empty($_config['use_content'])) {
                $_contents = $request->post('Content/a');
                if (empty($_contents)) {
                    throw new Exception('请完善待正文区块选项');
                }
                $content_ids      = ArrayHelper::uniqueAndTrimOneDimissionArray($_contents['id']); // ID单页面内唯一
                $content_names    = $_contents['name'];
                $content_types    = $_contents['type'];
                $content_lengths  = $_contents['length'];
                $content_widths   = $_contents['width'];
                $content_heights  = $_contents['height'];
                $content_explains = $_contents['explain'];

                if (count($content_ids) != count($content_names)
                    || count($content_ids) != count($content_types)
                    || count($content_ids) != count($content_lengths)
                    || count($content_ids) != count($content_widths)
                    || count($content_ids) != count($content_heights)
                    || count($content_ids) != count($content_explains)
                ) {
                    throw new Exception('区块ID有重复或区块配置项中有缺失选项');
                }

                if (empty($content_ids)) {
                    throw new Exception('请完善区块ID');
                }

                $content_options = [];
                foreach ($content_ids as $key => $value) {
                    $_content_options['id'] = $value;
                    if (empty($content_names[$key])) {
                        throw new Exception($value.'对应的区块名称不得为空');
                    }
                    $_content_options['name'] = trim($content_names[$key]);

                    // 区块类
                    if (empty($content_types[$key])) {
                        throw new Exception($value.'对应的区块类型不得为空');
                    }
                    $_content_options['length'] = 0;
                    $_content_options['width']  = 0;
                    $_content_options['height'] = 0;
                    $_content_options['type']   = $content_types[$key];
                    switch ($content_types[$key]) {
                        case 1:
                            if (empty($content_lengths[$key]) || intval($content_lengths[$key]) <= 0) {
                                throw new Exception($value.'对应的文字最大长度必须是正整数');
                            }
                            $_content_options['length'] = intval($content_lengths[$key]);
                            break;
                        case 2:
                            if (empty($content_widths[$key]) || intval($content_widths[$key]) <= 0) {
                                throw new Exception($value.'对应的封面图宽度必须是正整数');
                            }
                            if (empty($content_heights[$key]) || intval($content_heights[$key]) <= 0) {
                                throw new Exception($value.'对应的封面图高度必须是正整数');
                            }
                            $_content_options['width']  = intval($content_widths[$key]);
                            $_content_options['height'] = intval($content_heights[$key]);
                            break;
                        case 3:
                            break;
                    }
                    // 区块填写说明
                    if (empty($content_explains[$key])) {
                        throw new Exception($value.'对应的填写说明不得为空');
                    }
                    $_content_options['explain'] = trim($content_explains[$key]);

                    // 多个区块数组
                    $content_options[] = $_content_options;
                }

                // 构造正文区块
                $config['content_options'] = $content_options;
            }

            /**
             * 构造单页面数据
             */
            $page = [
                'flag'      => $_config['flag'],
                'sample_id' => $_config['sample_id'] ?? '', // 配置样例图
                'enable'    => empty($_config['enable']) ? 0 : 1,
                'sort'      => intval($_config['sort']) < 0 ? 0 : intval($_config['sort']),
                'config'    => $config, // json格式的单页面配置参数
            ];

            $is_edit     = !empty($_config['id']);
            $repeat_page = $this->Page->getDataByFlag($_config['flag']);
            if ($is_edit) {
                // 编辑模式
                $exist_data = $this->Page->getDataById($_config['id']);
                if (empty($exist_data)) {
                    throw new Exception('拟编辑的网站单页数据不存在');
                }
                // 修改的新flag标识已存在
                if (!empty($repeat_page) && $repeat_page['flag'] != $exist_data['flag']) {
                    throw new Exception('编辑修改的单页面标识已存在');
                }

                // 补充编辑模式主键
                $page['id'] = $_config['id'];
            } else {
                // 新增模式
                if (!empty($repeat_page)) {
                    throw new Exception('单页面标识已存在');
                }
            }

            // 新增|编辑数据至db
            $effect_rows = $this->Page->isUpdate($is_edit)->save($page);
            if (false === $effect_rows) {
                throw new Exception('系统异常：保存数据失败');
            }
            // 记录日志
            $this->LogService->logRecorder(
                [$page,$_config],
                ($is_edit ? "编辑" : "新增")."网站单页"
            );
            return ['error_code' => 0, 'error_msg' => '保存成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 提交单页面设置数据
     * @param Request $request
     * @return array
     */
    public function setting(Request $request)
    {
        try {
            $request->post('Setting/a');

            return [];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 页面id读取页面完整数据，返回纯数组没有stdClass
     * @param $id
     * @return array
     */
    public function getPageById($id)
    {
        try {
            $page = $this->Page->getFullPageById($id);
            if (empty($page)) {
                return [];
            }
            $page['config']  = ArrayHelper::toArray($page['config']);
            $page['setting'] = ArrayHelper::toArray($page['setting']);

            // 处理配置中类型为可识读
            if (!empty($page['config']['content_options'])) {
                $options = $page['config']['content_options'];
                foreach ($options as $key => $value) {
                    $options[$key]['type_readable'] = $this->Page->getPageConfigTypeReadable($value['type']);
                }
                $page['config']['content_options'] = $options;
            }

            return $page;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 网站单页快速排序
     * @param Request $request
     * @return array
     */
    public function sort(Request $request)
    {
        try {
            $id   = $request->post('id/i');
            $sort = intval($request->post('sort'));
            if ($sort <= 0) {
                throw new Exception('排序数字有误');
            }
            $page = $this->Page->getDataById($id);
            if (empty($page)) {
                throw new Exception('拟编辑排序的网站单页数据不存在');
            }
            $effect_rows = $this->Page->isUpdate(true)->save(['sort' => intval($sort)], ['id' => $id]);
            if (false == $effect_rows) {
                throw new Exception('排序调整失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $page,
                "网站单页快速排序"
            );
            return ['error_code' => 0, 'error_msg' => '排序调整成功'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * 删除网站单页
     * @param Request $request
     * @return array
     */
    public function delete(Request $request)
    {
        try {
            $id   = $request->post('id/i');
            $page = $this->Page->getDataById($id);
            if (empty($page)) {
                throw new Exception('拟删除的网站单页数据不存在');
            }

            $effect_rows = $this->Page->db()->where('id', $id)->delete();
            if (false === $effect_rows) {
                throw new Exception('删除操作失败：系统异常');
            }
            // 记录日志
            $this->LogService->logRecorder(
                $page,
                "删除网站单页"
            );
            return ['error_code' => 0, 'error_msg' => '已删除'];
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode() ?: 500, 'error_msg' => $e->getMessage()];
        }
    }
}
