$(function () {
    var keyUpHandle; // 文本检索handler句柄
    var pageDataSearch; // dataTable的检索句柄
    var txtSearch       = $("#txt_search"); // 文本检索的输入框
    var searchBeginDate = $("#search_begin_date"); // 时间范围检索开始
    var searchEndDate   = $("#search_end_date");// 时间范围检索结束


    /**
     * 文本检索和cookie记录检索值
     * 以及绑定检索输入框的自动提交事件
     */
    var targetSearch = utils.cookie("txt__CONTROLLER__Search");
    if (!utils.isEmpty(targetSearch)) {
        txtSearch.val(targetSearch);
        txtSearch.select();
    }
    txtSearch.on("keyup", function () {
        keyUpHandle && clearTimeout(keyUpHandle);
        keyUpHandle = setTimeout(function () {
            utils.cookie('txt__CONTROLLER__Search', txtSearch.val());
            refreshTable();
        }, 600);
    });
    /**
     * 绑定DateTimePicker时间筛选组件动作
     */
    utils.bindDateTimePicker($(".search_date"));
    /**
     * 清理检索的开始时间
     */
    $(".date").on("click",".clear-begin-data", function () {
        $(this).parents(".date").find(".search_date").val("");
    }).on("click",".clear-end-data", function () {
        $(this).parents(".date").find(".search_date").val("");
    });
    /**
     * 刷新dataTable表格
     * ---
     * 1、默认触发dataTable的表格当前页自动刷新
     * 2、若给参数且复制true或true等价值则是刷新dataTable并且回到第一页
     * ---
     * @param isReset boolean 是否重置表格为第一页
     */
    function refreshTable(isReset) {
        pageDataSearch.ajax.reload(null, !!isReset);
    }
    /**
     * 手动刷新表格
     */
    $("#refresh_table_btn").click(function () {
        pageDataSearch.ajax.reload(null, false);
    });
    /**
     * 点击高级查询按钮点击动作，打开高级查询modal
     */
    $("#adv_search_btn").click(function () {
        $("#SearchModal").modal("show");
    });
    /**
     * 高级查询modal上的按钮，执行查询
     * ---
     * 查询（高级查询）
     */
    $("#exec_search").click(function () {
        $("#SearchModal").modal("hide");
        refreshTable();
    });
    /**
     * 高级查询modal上的按钮，执行查询重置
     * ---
     * 重置（高级查询）
     */
    $("#exec_reset").click(function () {
        searchBeginDate.val("");
        searchEndDate.val("");

        // todo 清除高级查询modal上的输入框值等内容，一般先在顶部定义各个输入框的对象，此处直接用

        refreshTable();
        return false;
    });


    /**
     * 调用初始化dataTable封装方法
     */
    initDataTable();
    // initTableHeaderManageBtn(); // 添加表头管理按钮并绑定事件

    /**
     * +++++++++++++++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++++++++++++++
     * dataTable列绑定各种事件
     * +++++++++++++++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++++++++++++++
     */
    $(".table").on("init.dt",function () {
        // 定制显示的字段
        utils.bindColumnSelector("table-columns",pageDataSearch);
        // 重新初始化tooltips
        $(".tooltips").tooltip({container: "body"});
    }).on("dblclick","tr",function () {
        // tr行记录双击事件
        var data = $(this).data("json");
        if (data) {
            var td_class = data.DT_RowClass;
            if ($(this).hasClass("selected")) {
                $(".check_all").prop("checked",false);
                $("." + td_class).find('.check_item').prop("checked",false).trigger("change");
            } else {
                $("." + td_class).find('.check_item').prop("checked",true).trigger("change");
            }
        }
    }).on("change",".check_item",function () {
        // 全选取消全选的触发动作
        var tr       = $(this).parents("tr");
        var data     = tr.data("json");
        var td_class = "DT_class" + data.id;
        var node     = $("." + td_class);
        if ($(this).prop("checked")) {
            node.addClass("selected");
            // 启用表头管理按钮
            toggleHeaderBtn(true);
        } else {
            $(".check_all").prop("checked",false);
            node.find(".check_item").prop("checked",false);
            node.removeClass("selected");
            // 检查是否取消了全部checkbox后禁用管理按钮
            var check_inputs = $(".check_item");
            var isCancelAll  = true;
            $.each(check_inputs, function (i,n) {
                if ($(n).prop("checked")) {
                    isCancelAll = false;
                }
            });
            // 禁用表头管理按钮 -- 依据是否取消了全部取反
            toggleHeaderBtn(!isCancelAll);
        }
    }).on("click",".check_all",function () {
        // 全选和取消全选
        if($(this).prop("checked"))
        {
            $(".check_item").prop("checked",true).trigger("change");
            // 启用表头管理按钮
            toggleHeaderBtn(true);
        }else {
            $(".check_item").prop("checked",false).trigger("change");
            // 禁用表头管理按钮
            toggleHeaderBtn(false);
        }
    }).on("click",".enable",function () {
        // 启用禁用
        var data = $(this).parents("tr").data("json");
        var text = data.enable ? "确认禁用该__LIST_NAME__么？" : "确认启用该__LIST_NAME__么？";
        utils.ajaxConfirm(text,"/manage/__CONTROLLER_UNDER_SCORE__/enable",{"id":data.id},function () {
            refreshTable();
        });
    }).on("change",".list-sort-input",function () {
        // 快速设置排序
        var id   = $(this).data("id");
        var sort = $(this).val();
        utils.ajaxConfirm("确认修改排序么？",'/manage/__CONTROLLER_UNDER_SCORE__/sort',{"id":id,"sort":sort},function () {
            refreshTable();
        });
    }).on("click",".delete",function () {
        // 删除
        var id   = $(this).data("id");
        utils.ajaxConfirm("确认删除该__LIST_NAME__么？删除后将无法找回",'/manage/__CONTROLLER_UNDER_SCORE__/delete',{"id":id},function () {
            refreshTable();
        });
    }).on("click",'.edit',function () {
        // 编辑按钮打开编辑modal
        $("#id").val($(this).data("id")).prop("disabled",false);
        var editData = $(this).parents("tr").data("json"); // 从tr中读取出的待编辑的数据

        $("#SaveModalLabel").text("编辑__LIST_NAME__");
        $(".btn-edit-submit").show();
        $(".btn-create-submit").hide();

        // todo 编辑模式需处理的逻辑
        // sample
        $("#real_name").val(editData.real_name);

        $("#SaveModal").modal("show");
        return false;
    });

    // 新增
    $("#create").on('click',function () {
        $("#id").val('').prop("disabled",false);
        $("#SaveModalForm").get(0).reset();
        $("#SaveModalLabel").text("新增__LIST_NAME__");

        $(".btn-edit-submit").hide();
        $(".btn-create-submit").show();

        // todo 新增模式需处理的逻辑

        $("#SaveModal").modal("show");
        return false;
    });

    /**
     * ++++编辑记录++++
     * ——————————
     * 提交编辑ajax动作
     * ——————————
     */
    $(".btn-edit-submit").on("click",function () {
        var that = this;
        var form = $("#SaveModalForm");

        /**
         * sample
         */
        var real_time = $("#real_name");
        if (utils.isEmpty(real_time.val())) {
            real_time.focus();
            utils.toast("输入真实姓名");
            return false;
        }

        // todo 边界效验逻辑

        var data = form.serializeArray();
        $(that).prop("disabled",true).text("提交中...");
        utils.showLoading("提交中，请稍后...");
        $.ajax({
            url: form.data("edit"),
            type: "POST",
            data: data,
            success: function (data) {
                utils.hideLoading();
                if (data.error_code === 0) {
                    $("#SaveModal").modal("hide");
                    utils.toast(data.error_msg, 3000,function () {
                        refreshTable();
                    });
                } else {
                    utils.alert(data.error_msg ? data.error_msg : "未知错误");
                }
                $(that).prop("disabled",false).text("提交");
            },
            error:function () {
                utils.hideLoading();
                $(that).prop("disabled",false).text("提交");
                utils.alert("网络或服务器异常，请稍后再试");
            }
        });
        return false;
    });

    /**
     * ++++新增记录++++
     * ——————————
     * 提交新增ajax动作
     * ——————————
     */
    $(".btn-create-submit").on("click",function () {
        var that = this;
        var form = $("#SaveModalForm");

        /**
         * sample
         */
        var real_time = $("#real_name");
        if (utils.isEmpty(real_time.val())) {
            real_time.focus();
            utils.toast("输入真实姓名");
            return false;
        }

        // todo 边界效验逻辑

        var data = form.serializeArray();
        $(that).prop("disabled",true).text("提交中...");
        utils.showLoading("提交中，请稍后...");
        $.ajax({
            url: form.data("create"),
            type: "POST",
            data: data,
            success: function (data) {
                utils.hideLoading();
                if (data.error_code === 0) {
                    $("#SaveModal").modal("hide");
                    utils.toast(data.error_msg, 3000,function () {
                        refreshTable();
                    });
                } else {
                    utils.alert(data.error_msg ? data.error_msg : "未知错误");
                }
                $(that).prop("disabled",false).text("提交");
            },
            error:function () {
                utils.hideLoading();
                $(that).prop("disabled",false).text("提交");
                utils.alert("网络或服务器异常，请稍后再试");
            }
        });
        return false;
    });



    /**
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * 初始化dataTable方法封装
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     */
    function initDataTable() {
        /**
         * 统一调用dataTable初始化
         */
        pageDataSearch = $("#table").DataTable({
            serverSide: true,
            responsive: false,
            paging: true,
            searching: false,
            info: true,
            ordering: true,
            processing: true,
            pageLength: 50,
            lengthChange:true,
            AutoWidth: false,
            scrollX: true,
            fixedColumns: {
                leftColumns: 3,
                rightColumns: 1
            },
            ajax: {
                url: "/manage/__CONTROLLER_UNDER_SCORE__/list.html",
                type: "POST",
                /**
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * dataTable额外塞入请求体的键值对
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * +++++++++++++++++++++++++++++++++++++++++++
                 */
                data: function (data) {
                    return $.extend({}, data, {
                        keyword:txtSearch.val(),
                        begin_date:searchBeginDate.val(),
                        end_date:searchEndDate.val()
                        // todo 额外塞入请求体的数据获取方法
                    });
                },
                /**
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * ajax数据返回后、被使用前对结构进行变更，tr标签添加ID、data属性
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * +++++++++++++++++++++++++++++++++++++++++++
                 */
                dataFilter:function (data) {
                    try {
                        var json = JSON.parse(data);
                        for (var n in json.data) {
                            json.data[n].DT_RowClass = "DT_class" + json.data[n].id;
                            json.data[n].DT_RowId    = "DT_" + json.data[n].id;
                            json.data[n].DT_RowAttr  = {"data-id":json.data[n].id,"data-json":JSON.stringify(json.data[n])};

                            // todo 若需添加额外的filter方法，在此添加

                        }
                        return JSON.stringify(json);
                    } catch (e) {
                        return data;
                    }
                },
                /**
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * 接收到JSON数据后的数据转化方法
                 * +++++++++++++++++++++++++++++++++++++++++++
                 * +++++++++++++++++++++++++++++++++++++++++++
                 */
                dataSrc: function (json) {
                    // reset checkAll
                    $(".check_all").prop('checked',false);

                    // 启用|禁用表头管理按钮
                    toggleHeaderBtn(false);

                    if (json.data && json.data.length > 0) {
                        var items = json.data;
                        for (var n in items) {
                            var data = items[n];
                            items[n].operate = ""; // 操作按钮
                            /**
                             * 拥有编辑权限，则显示编辑按钮
                             */
                            if (has_edit_permission) {
                                items[n].operate += " <a data-href=\"/manage/__CONTROLLER_UNDER_SCORE__/edit?id="+data.id+"\" class=\"btn btn-xs btn-primary edit\" data-id=\""+data.id+"\"><i class=\"fa fa-pencil-square-o\"></i> 编辑</a>";
                            }

                            // 拥有删除权限，则显示删除按钮
                            if (has_delete_permission) {
                                items[n].operate += " <a data-href=\"/manage/__CONTROLLER_UNDER_SCORE__/delete?id="+data.id+"\" class=\"btn btn-xs btn-danger delete\" data-id=\""+data.id+"\"><i class=\"fa fa-trash\"></i> 删除</a>";
                            }

                            // 启用禁用按钮
                            if (data.enable) {
                                items[n].enable = "<button class=\"btn btn-xs bg-olive enable\">启用</button>";
                            } else {
                                items[n].enable = "<button class=\"btn btn-xs bg-teal enable\">禁用</button>";
                            }
                            // 快速排序输入框
                            items[n].sort ="<div class=\"layui-input-inline\">" +
                                "<input type=\"text\" class=\"list-sort-input\" data-id=\""+data.id+"\" value=\""+data.sort+"\">" +
                                "</div>";
                        }
                        return items;
                    }
                    return json.data;
                }
            },
            /**
             * +++++++++++++++++++++++++++++++++++++++++++
             * +++++++++++++++++++++++++++++++++++++++++++
             * 字段映射map
             * +++++++++++++++++++++++++++++++++++++++++++
             * +++++++++++++++++++++++++++++++++++++++++++
             */
            columns: [
                {
                    data: function (row,type) {
                        if(type === "display") {
                            return "<input type=\"checkbox\" id=\""+row.id+"\" class=\"check_item\" value=\""+row.id+"\">";
                        }
                        return "";
                    }
                },
                {data:"id"},
                {data:"title"},
                {data:"operate",className:"text-center"}
            ],
            // columns: utils.setColumns(js_columns), // 自定义字段的情况
            /**
             * 定义dataTable的一些语言，基本无需改动
             */
            language: {
                "sProcessing": "<i class=\"fa fa-refresh fa-spin\"></i> 载入中...",
                "sLengthMenu": "显示 _MENU_ 项结果",
                "sZeroRecords": "没有匹配结果",
                "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                "sInfoPostFix": "",
                "sSearch": "搜索:",
                "sUrl": "",
                "sEmptyTable": "数据为空",
                "sLoadingRecords": "载入中...",
                "sInfoThousands": ",",
                "oPaginate": {
                    "sFirst": "首页",
                    "sPrevious": "上页",
                    "sNext": "下页",
                    "sLast": "末页"
                },
                "oAria": {
                    "sSortAscending": ": 以升序排列此列",
                    "sSortDescending": ": 以降序排列此列"
                }
            }
        });
    }

    /**
     * 初始化datatable一页多少条后方的全选功能实现按钮
     * ---
     * 1、列表页面html结构中id为table_header_manage的容器完善按钮html代码
     * 2、方法体中变量html给予按钮样式代码
     * 3、bindTableHeaderEvent方法中为初始化的按钮绑定各种事件
     * ---
     */
    function initTableHeaderManageBtn() {
        // 从html页面读取id为table_header_manage的内部html元素执行判断是否需要初始化表头批量控制按钮
        var tableHeaderHtml = $("#table_header_manage");
        if (tableHeaderHtml.html()) {
            var target = $("#table_length").parents(".row").children(".col-sm-6:last");

            var html = '<div id="tableHeaderBtn" class="pull-right">'+ tableHeaderHtml.html() +'</div>';
            // 延迟300毫秒执行
            setTimeout(function () {
                target.html(html);
                bindTableHeaderEvent();
            }, 300);
            tableHeaderHtml.empty().remove();// 清理html中书写的按钮元素
        }
    }

    /**
     * 为table表头塞入的各个管理按钮添加事件
     */
    function bindTableHeaderEvent() {

        // 批量删除
        $("#tableHeaderBtn .table_manage_delete").on("click", function () {
            var checked_data = getInMultiCheck();
            if (checked_data[0].length <= 0) {
                utils.toast("请先勾选需批量操作的数据列");
                return false;
            }
            // 批量提交确认并提交
            utils.ajaxConfirm(
                "确认批量删除所勾选的__LIST_NAME__吗？",
                '/manage/__CONTROLLER_UNDER_SCORE__/delete',
                {'multi_id': checked_data[0]},
                function () {
                    refreshTable(false);
            });
        });

        // 启用|禁用表头管理按钮
        toggleHeaderBtn(false);
    }

    /**
     * 获取已选中列表批量主键id数组和每一列的数据对象
     * @returns {[[], []]}
     */
    function getInMultiCheck() {
        var checkItems = $(".DTFC_LeftWrapper .check_item");

        // 读取主键id数组和每一列的数据对象
        var multi_id = [];
        var multi_data = [];
        $.each(checkItems, function (i,n) {
            if ($(n).prop("checked")) {
                multi_id.push($(n).val());
                multi_data.push($(n).parents('tr').data('json'));
            }
        });

        return [multi_id, multi_data];
    }

    /**
     * 启用禁用表头批量按钮
     * @param enable bool真启用假禁用
     */
    function toggleHeaderBtn(enable) {
        var btnItems = $("#tableHeaderBtn button");
        if (enable) {
            btnItems.prop("disabled", false);
        } else {
            btnItems.prop("disabled", true);
        }
    }
});
