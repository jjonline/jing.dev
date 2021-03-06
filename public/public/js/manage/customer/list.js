$(function () {
    var keyUpHandle; // 文本检索handler句柄
    var pageDataSearch; // dataTable的检索句柄
    var txtSearch       = $("#txt_search"); // 文本检索的输入框
    var searchBeginDate = $("#search_begin_date"); // 时间范围检索开始
    var searchEndDate   = $("#search_end_date");// 时间范围检索结束

    // 检索
    var adv_enable = $("#adv_enable");
    var adv_gender = $("#dept_id");
    var points_effect_begin = $("#points_effect_begin");
    var points_effect_end = $("#points_effect_end");
    var points_freeze_begin = $("#points_freeze_begin");
    var points_freeze_end = $("#points_freeze_end");
    var points_level_begin = $("#points_level_begin");
    var points_level_end = $("#points_level_end");
    var select_province = $("#select_province");
    var select_city = $("#select_city");
    var select_district = $("#select_district");
    var birthday_begin = $("#birthday_begin");
    var birthday_end = $("#birthday_end");

    /**
     * 文本检索和cookie记录检索值
     * 以及绑定检索输入框的自动提交事件
     */
    var targetSearch = utils.cookie("txtCustomerSearch");
    if (!utils.isEmpty(targetSearch)) {
        txtSearch.val(targetSearch);
        txtSearch.select();
    }
    txtSearch.on("keyup", function () {
        keyUpHandle && clearTimeout(keyUpHandle);
        keyUpHandle = setTimeout(function () {
            utils.cookie('txtCustomerSearch', txtSearch.val());
            refreshTable();
        }, 600);
    });

    // 高级检索省市县
    $('#select_distpicker').distpicker();

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
        adv_enable.val("").trigger("change");
        adv_gender.val("").trigger("change");
        points_effect_begin.val("");
        points_effect_end.val("");
        points_freeze_begin.val("");
        points_freeze_end.val("");
        points_level_begin.val("");
        points_level_end.val("");
        select_province.val("");
        select_city.val("");
        select_district.val("");
        birthday_begin.val("");
        birthday_end.val("");

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
    }).on("click",".enable",function () {
        // 启用禁用
        var data = $(this).parents("tr").data("json");
        var text = data.enable ? "确认禁用该网站会员么？" : "确认启用该网站会员么？";
        utils.ajaxConfirm(text,"/manage/customer/enable",{"id":data.id},function () {
            refreshTable();
        });
    }).on("change",".list-sort-input",function () {
        // 快速设置排序
        var id   = $(this).data("id");
        var sort = $(this).val();
        utils.ajaxConfirm("确认修改排序么？",'/manage/customer/sort',{"id":id,"sort":sort},function () {
            refreshTable();
        });
    }).on("click",".delete",function () {
        // 删除
        var id   = $(this).data("id");
        utils.ajaxConfirm("确认删除该网站会员么？删除后将无法找回",'/manage/customer/delete',{"id":id},function () {
            refreshTable();
        });
    }).on("click",'.edit',function () {
        // 编辑按钮打开编辑modal
        $("#id").val($(this).data("id")).prop("disabled",false);
        var editData = $(this).parents("tr").data("json"); // 从tr中读取出的待编辑的数据

        $("#SaveModalLabel").text("编辑会员");
        $(".btn-edit-submit").show();
        $(".btn-create-submit").hide();

        // todo 编辑模式需处理的逻辑
        // sample
        $("#real_name").val(editData.real_name);

        $("#SaveModal").modal("show");
        return false;
    });

    // 新增前台会员浮层
    $("#create").on("click",function () {
        $("#id").val("").prop("disabled",false);
        $("#SaveModalForm").get(0).reset();
        $("#SaveModalLabel").text("新增会员");

        $(".btn-edit-submit").hide();
        $(".btn-create-submit").show();

        $("#customer_name").val("").prop("disable", false);
        $("#real_name").val("").prop("disable", false);
        $("#reveal_name").val("").prop("disable", false);
        $("#password").val("").prop("disable", false);
        $("#mobile").val("").prop("disable", false);
        $("#email").val("").prop("disable", false);
        $("#birthday").val("").prop("disable", false);
        $("#id_card").val("").prop("disable", false);


        $("#province").val("").prop("disable", false).trigger("change");
        $("#city").val("").prop("disable", false).trigger("change");
        $("#district").val("").prop("disable", false).trigger("change");
        $("#location").val("").prop("disable", false);

        $("#job_organization").val("").prop("disable", false);
        $("#job_number").val("").prop("disable", false);
        $("#job_location").val("").prop("disable", false);

        $("#enable").prop("checked", true).trigger("chagne");
        $("#remark").val("").prop("disable", false);

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

        var customer_name = $("#customer_name");
        if (utils.isEmpty(customer_name.val())) {
            customer_name.focus();
            utils.toast("输入用户名");
            return false;
        }

        var real_name = $("#real_name");
        if (utils.isEmpty(real_name.val())) {
            real_name.focus();
            utils.toast("输入真实姓名");
            return false;
        }

        var password = $("#password");
        if (!utils.isPassWord(password.val())) {
            password.focus();
            utils.toast("输入密码，同时包含字母和数字，6至18位");
            return false;
        }

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
            pageLength: 50,
            lengthChange:true,
            AutoWidth: false,
            scrollX: true,
            fixedColumns: {
                leftColumns: 3,
                rightColumns: 1
            },
            ajax: {
                url: "/manage/customer/list.html",
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
                        end_date:searchEndDate.val(),
                        adv_enable:adv_enable.val(),
                        adv_gender:adv_gender.val(),
                        points_effect_begin:points_effect_begin.val(),
                        points_effect_end:points_effect_end.val(),
                        points_freeze_begin:points_freeze_begin.val(),
                        points_freeze_end:points_freeze_end.val(),
                        points_level_begin:points_level_begin.val(),
                        points_level_end:points_level_end.val(),
                        select_province:select_province.val(),
                        select_city:select_city.val(),
                        select_district:select_district.val(),
                        birthday_begin:birthday_begin.val(),
                        birthday_end:birthday_end.val()
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
                    if (json.data && json.data.length > 0) {
                        var items = json.data;
                        for (var n in items) {
                            var data = items[n];
                            items[n].operate = ""; // 操作按钮

                            /**
                             * 拥有编辑权限，则显示编辑按钮
                             */
                            if (has_edit_permission) {
                                items[n].operate += " <a href=\"/manage/article/edit?id="+data.id+"\" class=\"btn btn-xs btn-primary edit\" data-id=\""+data.id+"\"><i class=\"fa fa-pencil-square-o\"></i> 编辑</a>";
                            }
                            /**
                             * 调整用户积分权限
                             */
                            if (has_detail_permission) {
                                items[n].operate += " <a data-href=\"/manage/article/detail?id="+data.id+"\" class=\"btn btn-xs btn-success detail\" data-id=\""+data.id+"\"><i class=\"fa fa-search-plus\"></i> 详情</a>";
                            }
                            /**
                             * 分配管理员
                             */
                            if (has_allocation_permission) {
                                items[n].operate += " <a data-href=\"/manage/article/allocation?id="+data.id+"\" class=\"btn btn-xs bg-black allocation\" data-id=\""+data.id+"\"><i class=\"fa fa-search-plus\"></i> 分配</a>";
                            }
                            /**
                             * 调整用户积分权限
                             */
                            if (has_adjustment_permission) {
                                items[n].operate += " <a data-href=\"/manage/article/adjustment?id="+data.id+"\" class=\"btn btn-xs bg-fuchsia adjustment\" data-id=\""+data.id+"\"><i class=\"fa fa-sun-o\"></i> 调整积分</a>";
                            }

                            // 启用禁用
                            // 有权限变更则为按钮，无权限变更仅显示
                            if (has_enable_permission) {
                                if (data.enable) {
                                    items[n].enable = "<button class=\"btn btn-xs bg-olive enable\">启用</button>";
                                } else {
                                    items[n].enable = "<button class=\"btn btn-xs bg-teal enable\">禁用</button>";
                                }
                            } else {
                                if (data.enable) {
                                    items[n].enable = "<label class=\"label bg-olive\">启用</label>";
                                } else {
                                    items[n].enable = "<label class=\"label bg-teal\">禁用</label>";
                                }
                            }

                            // 性别
                            if (data.gender == "-1") {
                                items[n].gender = "未知";
                            } else if (data.gender == "0") {
                                items[n].gender = "女";
                            } else {
                                items[n].gender = "男";
                            }

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
            columns: utils.setColumns(js_columns),
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
