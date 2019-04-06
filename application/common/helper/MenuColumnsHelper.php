<?php
/**
 * 菜单自定义字段处理方法
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2019-04-06 17:17
 * @file MenuColumnsHelper.php
 */

namespace app\common\helper;

class MenuColumnsHelper
{
    /**
     * 列表页面自定义显示字段html+js结构生成
     * @param array $show_columns
     * @return array [html_array,js_array]
     */
    public static function toFrontendStructure($show_columns)
    {
        if (empty($show_columns)) {
            return [[],[]];
        }

        $html = [];
        $js   = [];

        foreach ($show_columns as $key => $column) {
            // 可排序
            $sorted = $column['sorted'] ? ' data-orderable="true"' : '';
            // 字段居中
            $align  = $column['align'] ? ' class="text-center"' : '';
            // table结构html表头
            $html[] = '<th data-priority="'.($key+1).'"'.$sorted.$align.'>'.$column['name'].'</th>';

            // js字段名，菜单设置时有强效验，这里绝对能解析出:tableName\columnsName[\aliasName]
            $_column       = explode('.', $column['columns']);
            $columns_alias = empty($_column[2]) ? $_column[1] : $_column[2]; // 有设置别名则使用别名，没有别名则就是字段名

            // js字段设置数组
            $_js = [];
            $_js['data'] = $columns_alias;
            if ($column['align']) {
                $_js['className'] = 'text-center';
            }
            $js[] = $_js;
        }

        return [$html, $js];
    }

    /**
     * 列表页面自定义显示字段后端字段名和可排序字段生成
     * @param array $show_columns
     * @return array
     */
    public static function toBackendStructure($show_columns)
    {
        if (empty($show_columns)) {
            return [];
        }

        $columns   = []; // 转成查询的字段和别名
        $orderAble = []; // 收集可排序字段数组
        foreach ($show_columns as $key => $column) {
            // js字段名，菜单设置时有强效验，这里绝对能解析出:tableName\columnsName[\aliasName]
            $_column = explode('.', $column['columns']);
            if ($column['sorted']) {
                // 处理成: customer.user_name结构
                $orderAble[] = $_column[0].'.'.(empty($_column[2]) ? $_column[1] : $_column[2]);
            }
            // 若有别名处理成 customer.user_name as customer_name 无别名处理成：customer.user_name
            $columns[]   = $_column[0].'.'.(empty($_column[2]) ? $_column[1] : $_column[1].' as '.$_column[2]);
        }

        return [$columns, $orderAble];
    }
}
