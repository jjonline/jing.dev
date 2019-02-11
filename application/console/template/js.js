$(function () {
    var keyUpHandle; // 文本检索handler句柄
    var pageDataSearch; // dataTable的检索句柄
    var table           = $(".table"); // dataTable的表格容器
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
    $("#refresh_table").click(function () {
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

    /**
     * +++++++++++++++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++++++++++++++
     * dataTable列绑定各种事件
     * +++++++++++++++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++++++++++++++
     */
    table.on("init.dt",function () {
        // 定制显示的字段
        utils.bindColumnSelector("table-columns",pageDataSearch);
        // 重新初始化tooltips
        $(".tooltips").tooltip({container: "body"});
    }).on("dblclick","tr",function () {
        // tr行记录双击事件
        var data = $(this).data("json");
        if (data) {
            var td_class = data.DT_RowClass;
            if($(this).hasClass("selected"))
            {
                $(".check_all").prop("checked",false);
                $("." + td_class).find('.check_item').prop("checked",false).trigger("change");
            }else {
                $("." + td_class).find('.check_item').prop("checked",true).trigger("change");
            }
        }
    }).on("change",".check_item",function () {
        // 全选取消全选的触发动作
        var tr       = $(this).parents("tr");
        var data     = tr.data("json");
        var td_class = "DT_class" + data.id;
        var node     = $("." + td_class);
        if($(this).prop("checked"))
        {
            node.addClass("selected");
        } else {
            $(".check_all").prop("checked",false);
            node.find(".check_item").prop("checked",false);
            node.removeClass("selected");
        }
    }).on("click",".check_all",function () {
        // 全选和取消全选
        if($(this).prop("checked"))
        {
            $(".check_item").prop("checked",true).trigger("change");
        }else {
            $(".check_item").prop("checked",false).trigger("change");
        }
    }).on("click",'.edit',function () {
        // 编辑按钮打开编辑modal
        $("#id").val($(this).data("id")).prop("disabled",false);
        var editData = $(this).data("json"); // 从tr中读取出的待编辑的数据

        $("#SaveModalLabel").text("编辑前台图文");
        $(".btn-edit-submit").show();
        $(".btn-create-submit").hide();

        // todo 编辑模式需处理的逻辑
        // sample
        $("#real_name").val(editData.real_name);

        $("#SaveModal").modal("show");
        return false;
    });

    // 新增会员
    $("#create").on('click',function () {
        $("#id").val($(this).data("id")).prop("disabled",false);
        $("#SaveModalForm").get(0).reset();
        $("#SaveModalLabel").text("新增前台图文");

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
                    utils.toast(data.error_msg, 3000,function () {
                        $("#SaveModal").modal("hide");
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
                    utils.toast(data.error_msg, 3000,function () {
                        $("#SaveModal").modal("hide");
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
            pageLength: 100,
            lengthChange: false,
            AutoWidth: false,
            scrollX: true,
            fixedColumns: {
                leftColumns: 3,
                rightColumns: 1
            },
            ajax: {
                url: "/manage/__CONTROLLER_UNDER_SCORE__/list",
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
                {data:"article_cat_name",className:"text-center"},
                {data:"article_author_name",className:"text-center"},
                {data:"content_type",className:"text-center"},
                {data:"sort",className:"text-center"},
                {data:"awesome",className:"text-center"},
                {data:"click",className:"text-center"},
                {data:"is_home",className:"text-center"},
                {data:"is_top",className:"text-center"},
                {data:"allow_comment",className:"text-center"},
                {data:"display_time",className:"text-center"},
                {data:"create_time",className:"text-center"},
                {data:"update_time",className:"text-center"},
                {data:"enable",className:"text-center"},
                {data:"operate",className:"text-center"}
            ],
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
});
