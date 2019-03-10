$(function () {


    /**
     * 绑定select2事件
     */
    $(".select2").select2({language: "zh-CN"});

    /**
     * 绑定button模拟的radio
     */
    $(".js-select-container").on("click","button",function () {
        $(this).parents(".js-select-container").find("button").removeClass("btn-primary");
        //$('.js-select-container button').removeClass('btn-primary');
        $(this).addClass("btn-primary");
    });
    /**
     * 绑定bootstrapSwitch事件
     */
    $(".js-switch-container input").bootstrapSwitch();
    /**
     * tooltip
     */
    $("[data-toggle='tooltip']").tooltip();

    /**
     * 隐藏侧边栏
     */
    utils.hideNav = function () {
        $("body").addClass('sidebar-collapse');
    };
    /**
     * 显示侧边栏
     */
    utils.showNav = function () {
        $("body").removeClass('sidebar-collapse');
    };

    /**
     * 绑定datatable字段筛选器
     * @param node_id 点击选择字段的按钮
     * @param dataTableHandler  dataTable API handler
     * @param columns [] 可筛选的所有字段名称数组，按从左至右的顺序，不传则从#table thead>tr>th去读取
     */
    utils.bindColumnSelector = function (node_id,dataTableHandler,columns) {
        // 所有字段
        var column           = columns || getColumns();
        var local_all_key    = app_info.module +"_"+ app_info.controller +"_"+ app_info.action;
        var local_select_key = local_all_key+ "_selected";
        // 将所有字段列表塞入本地
        utils.localData(local_all_key,JSON.stringify(column));
        // 已勾选字段init
        var selected_columns = !utils.isEmpty(utils.localData(local_select_key)) ? JSON.parse(utils.localData(local_select_key)) : column;
        utils.localData(local_select_key,JSON.stringify(selected_columns));

        /**
         * 生成checkbox选择框页面
         * @returns {string}
         */
        function initMessage() {
            // 已勾选字段
            var selected_columns = !utils.isEmpty(utils.localData(local_select_key)) ? JSON.parse(utils.localData(local_select_key)) : column;
            // utils.localData(local_select_key,JSON.stringify(selected_columns));

            // 生成选择字段的html
            var message   = '<div class="row">';
            $.each(column,function (i,n) {
                var checked = "";
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
            $(".columns-checkbox").on("change","input",function () {
                // console.log('B' + new Date().getTime());
                var is_check           = $(this).prop("checked");
                var check_columns_name = $(this).data("columns");
                var index              = $(this).val(); // 字段位置的排序索引，从0开始
                if(is_check)
                {
                    addColumns(check_columns_name);
                    $(".columns-checkbox input").prop("disabled",false);
                    manageColumns(index,true);
                }else {
                    var selected_col = JSON.parse(utils.localData(local_select_key));
                    if(selected_col.length <= 2)
                    {
                        $(this).prop("checked",true);
                        return false;
                    }
                    lessColumns(check_columns_name);
                    manageColumns(index,false);
                }
                // console.log('E' + new Date().getTime());
            });
        });

        /**
         * 新增勾选的字段
         * @param column_name
         */
        function addColumns(column_name) {
            var selected_col = JSON.parse(utils.localData(local_select_key));
            var is_selected  = false;
            for(var i in selected_col)
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
            for(var i in selected_col_orin)
            {
                if(selected_col_orin[i] != column_name)
                {
                    selected_col.push(selected_col_orin[i]);
                }
            }
            utils.localData(local_select_key,JSON.stringify(selected_col));
        }

        /**
         * 显示或隐藏单个字段
         * @param i 需显示或隐藏的字段索引数字
         * @param is_visible bool true显示false隐藏
         */
        function manageColumns(i,is_visible) {
            dataTableHandler.column(i).visible(is_visible);
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
                var head = $(n).text();
                if(utils.isEmpty($(n).text()))
                {
                    head = '-';
                }
                arr.push(head);
            });
            return arr;
        }

        /**
         * 初始化字段
         */
        function initColumns() {
            var selected_columns_local = JSON.parse(utils.localData(local_select_key));
            for(var i in column)
            {
                var is_visible = false;
                for(var j in selected_columns_local)
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
        initColumns();
    };

    /**
     * 通用ajax分页操作记录函数绑定
     * @param title  modal标题
     * @param name  表名
     * @param id   业务Id
     */
    utils.bindOperationRecord = function(title,name,id) {
        function record_page(href) {
            utils.showLoading('请求中..');
            $.ajax({
                url:href,
                type:'GET',
                success:function (data) {
                    utils.hideLoading();
                    $('#_Operation_Modal_timeline_').children().remove();
                    $('#_Operation_Modal_timeline_').show();
                    $('.tips').remove();
                    $('#_Operation_Modal_Page_').children().remove();
                    var li = '';
                    var items = data.data.data;
                    if(!utils.isEmpty(items)){
                        $('#_Operation_Modal_Page_').html(data.data.paginate);
                        for(var i =0;i<items.length;i++)
                        {
                            li += ' <li class="time-label"> <span class="bg-green">' +
                                '                            '+items[i].create_time+'' +
                                '                        </span>' +
                                '                    </li>' +
                                '                    <li>' +
                                '                        <i class="fa fa-send bg-blue"></i>' +
                                '                        <div class="timeline-item">' +
                                '                            <span class="time"><i class="fa fa-clock-o"></i> '+items[i].create_time+'</span>' +
                                '                            <h3 class="timeline-header"><strong href="#">操作人：'+items[i].creator_name+'('+items[i].creator_dept_name+')</strong></h3>'+
                                '                            <div class="timeline-body">' +
                                '                                <p>操作说明：'+items[i].title+'</p>' +
                                '                                <p>操作描述：'+items[i].desc+'</p>' +
                                '                            </div>'+
                                '                            <div class="timeline-footer">' +
                                '                            </div>' +
                                '                        </div>' +
                                '                    </li>';
                        }
                        $('#_Operation_Modal_timeline_').append(li);
                    }else
                    {
                        var tips = "<h2 class='text-red tips'>暂无数据</h2>"
                        $('#_Operation_Modal_timeline_').before(tips).hide();
                    }
                },
                error:function () {
                    utils.hideLoading();
                    utils.alert('网络或服务器异常，请稍后再试');
                }
            });
        }
        var html = ['<div class="modal fade" id="_Operation_Modal_" role="dialog" data-backdrop="static" aria-hidden="true">' +
        '    <div class="modal-dialog modal-lg">' +
        '        <div class="modal-content">' +
        '            <div class="modal-header">' +
        '                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
        '                <h4 class="modal-title" id="_Operation_Modal_Label_">'+title+'</h4>' +
        '            </div>' +
        '            <div class="modal-body">' +
        '                <ul class="timeline" id = "_Operation_Modal_timeline_">' +
        '                </ul>' +
        '            </div>' +
        '            <div class="text-center" id="_Operation_Modal_Page_">' +
        '            </div>' +
        '            <div class="modal-footer">' +
        '                <div class="cols-sm-12 text-center">' +
        '                    <button type="button" class="btn btn-info text-center" data-dismiss="modal">关闭</button>' +
        '                </div>' +
        '            </div>' +
        '        </div>' +
        '    </div>' +
        '</div>'].join('');
        if(!$('#_Operation_Modal_').html())
        {
            $('body').append(html);
            setTimeout(function () {
                $('#_Operation_Modal_Page_').on('click','a',function () {
                    record_page($(this).attr('href'));
                    return false;
                });
            },20);
        }
        $('#_Operation_Modal_Label_').text(title);
        $('#_Operation_Modal_').modal('show');
        record_page('/manage/operation/record?id='+id+'&name='+name);
    };

    /**
     * 通用检索客服方法浮层
     * @param option object
     * {
     *  title:搜索框的标题
     *  placeholder:搜素框的提示语
     *  is_large:boolean,//大浮层还是小浮层，默认大
     *  url:检索url，默认/manage/common/getUserList即可
     * }
     */
    utils.bindSearchUser = function (option) {
        var options = $.extend({
            title:'检索用户',//模型层标题
            is_large:true,//模型层标题
            placeholder:'输入用户名、姓名、手机号搜索用户',//输入框placeholder
            url:'/manage/common/getUserList',//检索请求的url
            select:function (data) {} //勾选用户后的回调函数，参数为所选用户的object
        },option);
        var search_table = '<script type="text/html" id="_User_Search_Container_">' +
            '    <table class="table table-bordered table-hover" id="_User_Search_Table_" style="width:100%;">' +
            '        <thead>' +
            '        <tr>' +
            '            <th data-priority="1">用户名</th>' +
            '            <th data-priority="2">姓名</th>' +
            '            <th data-priority="4">性别</th>' +
            '            <th data-priority="3">手机</th>' +
            '            <th data-priority="5">部门</th>' +
            '            <th style="text-align: center;padding-right: 0;">操作</th>' +
            '        </tr>' +
            '        </thead>' +
            '        <tbody>' +
            '        <% for(var n = 0; n< users.length; n++) {' +
            '        var user = users[n];' +
            '        %>' +
            '        <tr data-json=\'<%=JSON.stringify(user)%>\'>' +
            '            <td><%=user.user_name%></td>' +
            '            <td><%=user.real_name%></td>' +
            '            <td><%=(user.gender == 1 ? \'男\' : (user.gender == 0 ? \'女\' : \'未知\'))%></td>' +
            '            <td><%=user.mobile%></td>' +
            '            <td><%=user.dept_name%></td>' +
            '            <td style="text-align: center;"><button class="btn btn-xs btn-info select"><i class="fa fa-plus" title="点击选择"></i></button></td>' +
            '        </tr>' +
            '        <% } %>' +
            '        </tbody>' +
            '    </table>' +
            '</script>';
        var search_modal = '<div class="modal fade" id="_UserSearchModal_" role="dialog" data-backdrop="static" aria-hidden="true">' +
            '    <div class="modal-dialog modal-lg">' +
            '        <div class="modal-content">' +
            '            <div class="modal-header">' +
            '                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '                <h4 class="modal-title" id="_UserSearchModalLabel_">检索客户</h4>' +
            '            </div>' +
            '            <div class="modal-body">' +
            '                <div class="row">' +
            '                    <div class="col-md-12">' +
            '                        <div class="row">' +
            '                            <div class="col-sm-12">' +
            '                                <div class="input-group search-control">' +
            '                                    <input type="text" class="form-control" id="_UserSearchInput_" placeholder="" value="" autocomplete="off" />' +
            '                                    <span class="input-group-addon">' +
            '                                        <i class="fa fa-search"></i> 搜索' +
            '                                    </span>' +
            '                                </div>' +
            '                            </div>' +
            '                        </div>' +
            '                    </div>' +
            '                    <div class="col-md-12" id="_UserSearchContainer_"></div>' +
            '                </div>' +
            '            </div>' +
            '        </div>' +
            '    </div>' +
            '</div>';
        if(!$('#_UserSearchModal_').html())
        {
            $('body').append(search_modal).append(search_table);
        }
        var keyUpHandle;
        var MessageTable;
        if(!options.is_large)
        {
            $('#_UserSearchModal_').find('.modal-dialog').removeClass('modal-lg');
        }else {
            $('#_UserSearchModal_').find('.modal-dialog').addClass('modal-lg');
        }
        // 设置模型层标题
        $('#_UserSearchModalLabel_').text(options.title);
        $('#_UserSearchInput_').attr('placeholder',options.placeholder);
        // 绑定事件
        function searchUser()
        {
            var key  = $("#_UserSearchInput_").val();
            keyUpHandle && clearTimeout(keyUpHandle);
            keyUpHandle = setTimeout(function () {
                $('#_UserSearchContainer_').html('<div class="search-context">加载中...</div>');
                $.ajax({
                    type: "GET",
                    url: options.url,
                    data: {query: key},
                    success: function (data) {
                        if(data.error_code == 0)
                        {
                            $('#_UserSearchContainer_').html('');
                            MessageTable && MessageTable.destroy();
                            if(data.data)
                            {
                                var dataHTML = tmpl("_User_Search_Container_", {
                                    users: data.data
                                });

                                $("#_UserSearchContainer_").html(dataHTML);
                                MessageTable = $('#_User_Search_Table_').DataTable({
                                    paging: false,
                                    searching: false,
                                    ordering: false,
                                    info: false,
                                    language: {
                                        emptyTable: '<div class="search-context">没有查询到数据，在上方修改关键字后再试</div>'
                                    }
                                });
                            }
                        }else {
                            $("#ProductModalContainer").html('<div class="search-context">'+(data.error_msg ? data.error_msg : '未知错误')+'</div>');
                        }
                    },
                    error: function () {
                        $("#ProductModalContainer").html('<div class="search-context">服务器异常，请稍后再试</div>');
                    }
                });
            }, 200);
        }
        // 延迟执行
        setTimeout(function () {
            $('#_UserSearchModal_').modal('show');
            searchUser();
            $('#_UserSearchInput_').select();
        },200);
        // 绑定搜索客户键盘事件
        $("#_UserSearchModal_").on("keyup", "#_UserSearchInput_", function () {
            searchUser();
        }).on("click", ".input-group-addon", function () {
            searchUser();
        }).on('click','.select',function () {
            // 选择某个用户动作
            var data = $(this).parents('tr').data('json');
            options.select(data);
            $('#_UserSearchModal_').modal('hide');
        });

    };

    /**
     * 初始化badge
     */
    function initBadge() {
        var badgeItems = $('.is-badge');
        var badge = [];
        $.each(badgeItems,function (i,n) {
            var tag = $(n).parents('a').attr('id');
            badge.push(tag);
        });
        if(utils.isEmpty(badge)) {
            return false;
        }
        $.post('/manage/statistics/badge',{'tags':badge},function (data) {
            if(data.error_code == 0)
            {
                // 返回数据结构 {Dashboard:1,Order_Manage_List:1}
                var result = data.data;
                $.each(result,function (id,number) {
                    if(number != 0)
                    {
                        $('#' + id).find('.badge-container').show();
                        $('#' + id).find('.is-badge').text(number);
                    }
                });
            }
        });
    }
    initBadge();

});