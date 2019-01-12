$(function () {
    var keyUpHandle; // 文本检索handler句柄
    var pageDataSearch; // dataTable的检索句柄
    var table           = $(".table"); // dataTable的表格容器
    var txtSearch       = $("#txtSearch"); // 文本检索的输入框
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
    $(".clear-begin-data").click(function () {
        $("#search_begin_date").val("");
        refreshTable();
    });
    /**
     * 清理检索的结束时间
     */
    $(".clear-end-data").click(function () {
        $("#search_end_date").val("");
        refreshTable();
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
    initDataTable({
        /**
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         * 数据源url
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         */
        url : "/manage/__CONTROLLER_UNDER_SCORE__/lists",
        /**
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         * 额外塞入请求的key-value对象
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         */
        data : {
            begin_date: searchBeginDate.val(),
            end_date: searchEndDate.val()
        },
        /**
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         * ajax返回的原始json字符串处理方法
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         */
        dataFilter : function () {},
        /**
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         * dataTable转换后的数据对象处理方法
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         */
        dataSrc : function (json) {
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
            }
        },
        /**
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         * 字段映射map
         * +++++++++++++++++++++++++++++++++++++++++++
         * +++++++++++++++++++++++++++++++++++++++++++
         */
        columns : [
            {
                data: function (row,type) {
                    if(type === "display") {
                        return "<input type=\"checkbox\" id=\""+row.id+"\" class=\"check_item\" value=\""+row.id+"\">";
                    }
                    return "";
                }
            },

            // todo

            {data:"operate",className:"text-center"}
        ]
    });

    /**
     * +++++++++++++++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++++++++++++++
     * dataTable列绑定各种事件
     * +++++++++++++++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++++++++++++++
     */
    table.on("dblclick","tr",function () {
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

        $("#SaveModalLabel").text("编辑__LIST_NAME__");
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
     * @param setting
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     * +++++++++++++++++++++++++++++++
     */
    function initDataTable(setting) {
        var _setting = $.extend({
            /**
             * dataTable的请求Url
             */
            url: "",
            /**
             * 额外塞入dataTable ajax请求的数据参数对 key=>value形式
             */
            data: {},
            /**
             * dataTable返回值过滤方法，即对ajax返回值数据执行过滤的方法回调
             * 1、参数为返回值的json对象字面量形式，类型为字符串
             * 2、需JSON.parse后处理
             * 3、最后返回JSON.stringify处理的字符串
             */
            dataFilter: function (data) {
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
             * dataTable渲染返回值json之前的数据源处理方法
             * 1、在列表渲染之前调用，可以用来改变处理一些值，例如map映射、状态标记转换为可识读文字 等
             * 1、参数为ajax返回值转换后的原始json对象，即服务器返回的经过dataFilter处理之后的字符串转换后的json对象
             * 2、处理完毕之后最后数据列即可，一般是参数对象中的key为data的列值
             */
            dataSrc: function (json) {
                if (json.data && json.data.length > 0) {
                    for (var n in json.data) {

                        // todo 执行每一行列数据处理的具体逻辑

                    }
                }
            },
            /**
             * 指定ajax返回的json对象该如何来对应上html中的td列
             * 一般第一列是一个checkbox，参考格式如下：
             * ---
             * [
             *  {data: function (row,type,val,meta)
             *      {
             *          if(type == "display")
             *          {
             *              return '<input type="checkbox" id="check_'+row.id+'" class="check_item" value="'+row.id+'">';
             *          }
             *          return '';
             *      }
             *  },
             *  {data: 'id'},
             *  {data: 'operate',className:'text-center'}
             *  ]
             * ---
             */
            columns:[
                {
                    data: function (row,type) {
                        if(type === "display") {
                            return "<input type=\"checkbox\" id=\""+row.id+"\" class=\"check_item\" value=\""+row.id+"\">";
                        }
                        return "";
                    }
                }
            ],
            /**
             * 定义dataTable的一些语言，基本不用传参
             */
            language:{
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
        },setting);

        /**
         * 统一调用dataTable初始化
         * @type {jQuery|*}
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
                url: _setting.url,
                type: "POST",
                data: function (data) {
                    return $.extend({}, data, _setting.data);
                },
                // ajax数据返回后、被使用前对结构进行变更，tr标签添加ID、data属性
                dataFilter:function (data) {
                    if (_setting.dataFilter) {
                        _setting.dataFilter(data);
                    }
                },
                dataSrc: function (json) {
                    if (_setting.dataSrc) {
                        _setting.dataSrc(json);
                    }
                }
            },
            columns: _setting.columns,
            language: _setting.language
        });
    }
});
