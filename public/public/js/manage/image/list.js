$(function () {
    var keyUpHandle; // 文本检索handler句柄
    var pageDataSearch; // dataTable的检索句柄
    var txtSearch       = $("#txt_search"); // 文本检索的输入框
    var searchBeginDate = $("#search_begin_date"); // 时间范围检索开始
    var searchEndDate   = $("#search_end_date");// 时间范围检索结束

    var update_time_begin = $("#update_time_begin");
    var update_time_end = $("#update_time_end");
    var adv_enable = $("#adv_enable");
    /**
     * 文本检索和cookie记录检索值
     * 以及绑定检索输入框的自动提交事件
     */
    var targetSearch = utils.cookie("txtImageSearch");
    if (!utils.isEmpty(targetSearch)) {
        txtSearch.val(targetSearch);
        txtSearch.select();
    }
    txtSearch.on("keyup", function () {
        keyUpHandle && clearTimeout(keyUpHandle);
        keyUpHandle = setTimeout(function () {
            utils.cookie('txtImageSearch', txtSearch.val());
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
        adv_enable.val("").trigger("change");
        update_time_begin.val();
        update_time_end.val();

        refreshTable();
        return false;
    });

    // 带上传进度条的文件上传
    utils.bindAjaxUploader("cover_image_file",{
        // url:'',//上传文件后端Url，留空则为/manage/upload/upload?origin=ajax
        allow_extension: ['jpg','jpeg', 'png'],//null不限制、需限制时使用数组 ['jpg','jpeg']
        extraData: {'is_safe':0}, //上传额外附带的key-value
        multiple:true,//是否允许选择多个文件，默认允许多个
        success:function (data) {
            if(data['error_code'] == 0)
            {
                $('#cover_img').remove();
                $('.cover-image-file-container').prepend('<div id="cover_img" class="upload-preview"><img src="'+data.data.file_path+'"></div>');
                $('#cover_image_id').val(data.data.id);
            }else{
                utils.alert(data.error_msg ? data.error_msg : '未知错误');
            }
        },//上传成功的回调函数
        error:function () {
            utils.alert("网络或服务器异常，文件上传失败！");
        }//上传失败的回调函数
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
        var text = data.enable ? "确认禁用该轮播图么？" : "确认启用该轮播图么？";
        utils.ajaxConfirm(text,"/manage/image/enable",{"id":data.id},function () {
            refreshTable();
        });
    }).on("change",".list-sort-input",function () {
        // 快速设置排序
        var id   = $(this).data("id");
        var sort = $(this).val();
        utils.ajaxConfirm("确认修改排序么？",'/manage/image/sort',{"id":id,"sort":sort},function () {
            refreshTable();
        });
    }).on("click",".delete",function () {
        // 删除
        var id   = $(this).data("id");
        utils.ajaxConfirm("确认删除该轮播图么？删除后将无法找回，若无需前台显示建议选禁用功能",'/manage/image/delete',{"id":id},function () {
            refreshTable();
        });
    }).on('click','.edit',function () {
        // 编辑模式
        var data = $(this).parents('tr').data('json');
        $("#id").val(data.id).prop("disabled",false);
        $("#cover_image_id").val(data.cover_id);
        $("#SaveModalLabel").text("编辑轮播图");
        $("#title").val(data.title);
        $("#tag").val(data.tag);
        $("#url").val(data.url);
        $("#sort").val(data.sort);
        $("#remark").val(data.remark);
        $("#enable").prop("checked",!!data.enable).trigger("change");

        $('#cover_img').remove();
        $('.cover-image-file-container').prepend('<div id="cover_img" class="upload-preview"><img src="'+data.cover+'"></div>');

        $(".btn-edit-submit").show();
        $(".btn-create-submit").hide();

        $("#SaveModal").modal("show");
        return false;
    });

    // 新增
    $("#create").on('click',function () {
        $("#id").val('').prop("disabled",true);
        $("#cover_image_id").val('');
        $("#SaveModalForm").get(0).reset();
        $("#SaveModalLabel").text("新增轮播图");

        $('#cover_img').remove();

        $(".btn-edit-submit").hide();
        $(".btn-create-submit").show();

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

        var tag = $("#tag");
        if (utils.isEmpty(tag.val())) {
            tag.focus();
            utils.toast("请输入分组tag");
            return false;
        }
        var title = $("#title");
        if (utils.isEmpty(title.val())) {
            title.focus();
            utils.toast("请输入轮播图标题");
            return false;
        }
        var cover_image_id = $("#cover_image_id");
        if (utils.isEmpty(cover_image_id.val())) {
            cover_image_id.focus();
            utils.toast("请上传轮播图");
            return false;
        }

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

        var tag = $("#tag");
        if (utils.isEmpty(tag.val())) {
            tag.focus();
            utils.toast("请输入分组tag");
            return false;
        }
        var title = $("#title");
        if (utils.isEmpty(title.val())) {
            title.focus();
            utils.toast("请输入轮播图标题");
            return false;
        }
        var cover_image_id = $("#cover_image_id");
        if (utils.isEmpty(cover_image_id.val())) {
            cover_image_id.focus();
            utils.toast("请上传轮播图");
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
                url: "/manage/image/list.html",
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
                        update_time_begin:update_time_begin.val(),
                        update_time_end:update_time_end.val()
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
                            /**
                             * 拥有编辑权限，则显示编辑按钮
                             */
                            if (has_edit_permission) {
                                items[n].operate += " <a data-href=\"/manage/image/edit?id="+data.id+"\" class=\"btn btn-xs btn-primary edit\" data-id=\""+data.id+"\"><i class=\"fa fa-pencil-square-o\"></i> 编辑</a>";
                            }
                            /**
                             * 拥有删除权限，则显示删除按钮
                             */
                            if (has_delete_permission) {
                                items[n].operate += " <a data-href=\"/manage/image/delete?id="+data.id+"\" class=\"btn btn-xs btn-danger delete\" data-id=\""+data.id+"\"><i class=\"fa fa-trash\"></i> 删除</a>";
                            }
                            items[n].cover = '<a href="'+items[n].cover+'" target="_blank"><i class="fa fa-image"></i></a>';
                            // 状态、连接
                            if (data.enable) {
                                items[n].enable = "<button class=\"btn btn-xs bg-olive enable\">启用</button>";
                            } else {
                                items[n].enable = "<button class=\"btn btn-xs bg-teal enable\">禁用</button>";
                            }
                            if (data.url) {
                                items[n].url = '<a href="'+items[n].url+'" target="_blank"><i class="fa fa-link"></i></a>';
                            } else {
                                items[n].url = "-";
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
            columns: [
                {
                    data: function (row,type) {
                        if(type === "display") {
                            return "<input type=\"checkbox\" id=\""+row.id+"\" class=\"check_item\" value=\""+row.id+"\">";
                        }
                        return "";
                    }
                },
                {data:"tag"},
                {data:"cover",className:"text-center"},
                {data:"title"},
                {data:"url",className:"text-center"},
                {data:"enable",className:"text-center"},
                {data:"sort",className:"text-center"},
                {data:"remark",className:"text-center"},
                {data:"create_time",className:"text-center"},
                {data:"update_time",className:"text-center"},
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
