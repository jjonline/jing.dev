$(function () {


    /**
     * 绑定select2事件
     */
    $(".select2").select2({language: "zh-CN"});

    /**
     * 绑定button模拟的radio
     */
    $('.js-select-container').on('click','button',function () {
        $(this).parents('.js-select-container').find('button').removeClass('btn-primary');
        //$('.js-select-container button').removeClass('btn-primary');
        $(this).addClass('btn-primary');
    });
    /**
     * 绑定bootstrapSwitch事件
     */
    $('.js-switch-container input').bootstrapSwitch();

    /**
     * 绑定datatable字段筛选器
     * @param node_id 点击选择字段的按钮
     * @param dataTableHandler  dataTable API handler
     * @param columns [] 可筛选的所有字段名称数组，按从左至右的顺序，不传则从#table thead>tr>th去读取
     */
    utils.bindColumnSelector = function (node_id,dataTableHandler,columns) {
        // 所有字段
        var column           = columns || getColumns();
        var local_all_key    = app_info.module +'_'+ app_info.controller +'_'+ app_info.action;
        var local_select_key = local_all_key+ '_selected';
        // 将所有字段列表塞入本地
        utils.localData(local_all_key,JSON.stringify(column));

        /**
         * 生成checkbox选择框页面
         * @returns {string}
         */
        function initMessage() {
            // 已勾选字段
            var selected_columns = !utils.isEmpty(utils.localData(local_select_key)) ? JSON.parse(utils.localData(local_select_key)) : column;
            utils.localData(local_select_key,JSON.stringify(selected_columns));

            // 生成选择字段的html
            var message   = '<div class="row">';
            $.each(column,function (i,n) {
                var checked = '';
                $.each(selected_columns,function (ii,nn) {
                    if(n == nn)
                    {
                        checked = ' checked="checked"';
                    }
                });
                message += '<div class="form-group col-sm-12 col-md-4">' +
                    '    <div class="checkbox columns-checkbox">' +
                    '      <label><input type="checkbox" value="'+i+'"'+checked+' data-columns="'+n+'"> ' + n +'</label>'+
                    '    </div>' +
                    '</div>';
            });
            message += '</div>';
            return message;
        }


        // 绑定选择字段按钮事件
        $('#' + node_id).on('click',function () {
            bootbox.dialog({
                message: initMessage(),
                title: '选择显示的列名',
                onEscape: true,
                backdrop: true,
                buttons: false
            });
            // 勾选字段动作触发
            $('.columns-checkbox').on('change','input',function () {
                var is_check           = $(this).prop('checked');
                var check_columns_name = $(this).data('columns');
                if(is_check)
                {
                    addColumns(check_columns_name);
                    $(".columns-checkbox input").prop('disabled',false);
                }else {
                    var selected_col = JSON.parse(utils.localData(local_select_key));
                    if(selected_col.length <= 2)
                    {
                        $(this).prop('checked',true);
                        return false;
                    }
                    lessColumns(check_columns_name);
                }
                // event
                manageColumns();
            });
        });

        /**
         * 新增勾选的字段
         * @param column_name
         */
        function addColumns(column_name) {
            var selected_col = JSON.parse(utils.localData(local_select_key));
            var is_selected  = false;
            for(i in selected_col)
            {
                if(selected_col[i] == column_name)
                {
                    is_selected = true;
                    break;
                }
            }
            if(!is_selected)
            {
                selected_col.push(column_name);
            }
            utils.localData(local_select_key,JSON.stringify(selected_col));
        }

        /**
         * 减去显示的字段
         * @param column_name
         */
        function lessColumns(column_name) {
            var selected_col_orin = JSON.parse(utils.localData(local_select_key));
            var selected_col = [];
            for(i in selected_col_orin)
            {
                if(selected_col_orin[i] != column_name)
                {
                    selected_col.push(selected_col_orin[i]);
                }
            }
            utils.localData(local_select_key,JSON.stringify(selected_col));
        }

        /**
         * 读取所有列数据
         * @returns {Array}
         */
        function getColumns()
        {
            var th_list = $('#table thead>tr>th');
            var arr     = [];
            $.each(th_list,function (i,n) {
                arr.push($(n).text());
            });
            return arr;
        }

        /**
         * 变动列表字段
         */
        function manageColumns() {
            var selected_columns_local = JSON.parse(utils.localData(local_select_key));
            for(i in column)
            {
                var is_visible = false;
                for(j in selected_columns_local)
                {
                    if(selected_columns_local[j] == column[i])
                    {
                        is_visible = true;
                    }
                }
                dataTableHandler.column(i).visible(is_visible);
            }

        }

        // init
        manageColumns();
    };

    /**
     * tooltip
     */
    $("[data-toggle='tooltip']").tooltip();

    /**
     * 隐藏侧边栏
     */
    utils.hideNav = function () {
        $('body').addClass('sidebar-collapse');
    };
    /**
     * 显示侧边栏
     */
    utils.showNav = function () {
        $('body').removeClass('sidebar-collapse');
    };

});