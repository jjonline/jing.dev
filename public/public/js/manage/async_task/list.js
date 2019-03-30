$(function () {
    var txtSearch           = $('#txt_search');
    var dept_id             = $('#select_dept');
    var user_id             = $("#user_id");
    var task_status         = $('#task_status');
    var create_time_begin   = $('#create_time_begin');
    var create_time_end     = $('#create_time_end');
    var delivery_time_begin = $('#delivery_time_begin');
    var delivery_time_end   = $('#delivery_time_end');
    var finish_time_begin   = $('#finish_time_begin');
    var finish_time_end     = $('#finish_time_end');
    var keyUpHandle;
    var targetSearch = utils.cookie('txtUserSearch');
    if (!utils.isEmpty(targetSearch)) {
        txtSearch.val(targetSearch);
        txtSearch.select();
    }

    var bindSearchEvents = function () {
        $("#task_status button").on("click", function () {
            $(this).parent().children("button.btn-primary").removeClass("btn-primary").removeClass('active').addClass('btn-default');
            $(this).removeClass('btn-default').addClass("btn-primary").addClass('active');
            refreshTable();
        });

        txtSearch.on("keyup", function () {
            keyUpHandle && clearTimeout(keyUpHandle);
            keyUpHandle = setTimeout(function () {
                utils.cookie('txtUserSearch', txtSearch.val());
                refreshTable();
            }, 600);
        });

        //高级查询记录模式下的日期插件使用
        $(".advanced_search_date").datetimepicker({
            container:'#SearchModal',
            minView: "hour", //选择日期后，不会再跳转去选择时分秒
            language:  'zh-CN',
            format: 'yyyy-mm-dd hh:ii:ss',
            todayBtn:  1,
            autoclose: 1
        });
        //清理创建时间开始时间
        $('.clear-create-time-begin-data').click(function () {
            $('#create_time_begin').val('');
        });
        //清理创建时间结束时间
        $('.clear-create-time-end-data').click(function () {
            $('#create_time_end').val('');
        });
        //清理任务结束时间开始时间
        $('.clear-finish-time-begin-data').click(function () {
            $('#finish_time_begin').val('');
        });
        //清理任务结束结束时间
        $('.clear-finish-time-end-data').click(function () {
            $('#finish_time_end').val('');
        });
        //清理任务结束时间开始时间
        $('.clear-delivery-time-begin-data').click(function () {
            $('#delivery_time_begin').val('');
        });
        //清理任务结束结束时间
        $('.clear-delivery-time-end-data').click(function () {
            $('#delivery_time_end').val('');
        });

        /**
         * 手动刷新表格
         */
        $('#table-refresh').click(function () {
            pageDataSearch.ajax.reload(null, false);
        });
        /**
         * 绑定选择字段事件
         */
        $('#table').on('init.dt',function () {
            utils.bindColumnSelector('table-columns',pageDataSearch);
            $('.tooltips').tooltip({container: 'body'});
        });
        $('#chat_record_table').on('init.dt',function () {
            $('.tooltips').tooltip({container: '#ChatRecordModal'});
        });

    };
    var pageDataSearch;
    var refreshTable = function () {
        pageDataSearch.ajax.reload(null, false);
    };
    var initTable = function () {
        pageDataSearch = $('#table').DataTable({
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
            scrollX:true,
            ajax: {
                url: '/manage/async_task/list',
                type: 'GET',
                data: function (d) {
                    return $.extend({}, d, {
                        dept_id: dept_id.val(),
                        user_id: user_id.val(),
                        keyword: txtSearch.val(),
                        task_status:task_status.val(),
                        create_time_begin: create_time_begin.val(),
                        create_time_end: create_time_end.val(),
                        delivery_time_begin:delivery_time_begin.val(),
                        delivery_time_end:delivery_time_end.val(),
                        finish_time_begin:finish_time_begin.val(),
                        finish_time_end:finish_time_end.val(),
                        status: $("#task_status button.active").data("status"),
                        task_type: $("#task_type").val()
                    });
                },
                dataSrc: function (json) {
                    if (json.data && json.data.length > 0) {
                        for (var n in json.data) {
                            //操作
                            json.data[n].operate = '';
                            json.data[n].operate +=' <a href="javascript:;" class="btn btn-xs btn-success detail" data-title="'+json.data[n].title+'" data-href="/manage/async_task/detail"  data-id="'+json.data[n].id+'"><i class="fa fa-search-plus"></i> 详情</a>';
                            if(json.data[n].task_status == 0)
                            {
                                json.data[n].task_status = '<small class="label bg-orange">未执行</small>';
                            }else if(json.data[n].task_status == 1)
                            {
                                json.data[n].task_status = '<small class="label btn-primary">正在执行</small>';
                            }else if(json.data[n].task_status == 2)
                            {
                                json.data[n].task_status = '<small class="label bg-olive">执行成功</small>';
                            }else if(json.data[n].task_status == 3)
                            {
                                json.data[n].task_status = '<small class="label btn-danger">执行失败</small>';
                            }
                        }
                    }
                    return json.data;
                }
            },
            columns: [
                {data: 'real_name'},
                {data: 'title'},
                {data: 'task_status',className:"text-center"},
                {data: 'delivery_time',className:"text-center"},
                {data: 'finish_time',className:"text-center"},
                {data: 'create_time',className:"text-center"},
                {data: 'dept_name'},
                {data: 'operate',className:"text-center"}
            ],
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
    };
    var initPage = function () {
        initTable();
        bindSearchEvents();
    };
    initPage();

    $('#table').on('click','.detail',function () {
        var that = $(this);
        var title = that.data('title') + '详情';
        utils.showLoading('请求中...');
        //ajax请求详情数据
        $.ajax({
            url: $(this).data('href'),
            type: 'GET',
            data: {id:that.data('id')},
            success: function (data) {

                utils.hideLoading();
                if(data.error_code == 0){

                    if(data.data.task_status == 0)
                    {
                        data.data.task_status = '<small class="label bg-orange">未执行</small>';
                    }else if(data.data.task_status == 1)
                    {
                        data.data.task_status = '<small class="label btn-primary">正在执行</small>';
                    }else if(data.data.task_status == 2)
                    {
                        data.data.task_status = '<small class="label bg-olive">执行成功</small>';
                    }else if(data.data.task_status == 3)
                    {
                        data.data.task_status = '<small class="label btn-danger">执行失败</small>';
                    }
                    $('.detail_real_name').text(data.data.real_name);
                    $('.detail_title').text(data.data.title);
                    $('.detail_task_status').html(data.data.task_status);
                    $('.detail_delivery_time').text(data.data.delivery_time);
                    $('.detail_finish_time').text(data.data.finish_time);
                    $('.detail_create_time').text(data.data.create_time);
                    $('.detail_dept_name').text(data.data.dept_name);
                    $('.detail_result').html(data.data.result);
                    $('#DetailModal').modal('show');
                    $('#DetailModalLabel').text(title);
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
            },
            error:function () {
                utils.hideLoading();
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
    });
    //点击高级查询
    $('.search-btn').click(function () {
        $('#SearchModal').modal('show');
    });
    //查询（高级查询）
    $('.btn-search').click(function () {
        $('#SearchModal').modal('hide');
        refreshTable();
    });
    //重置（高级查询）
    $('.btn-reset').click(function () {
        $('#select_dept').val('0').trigger('change');
        $('#task_status').val('').trigger('change');
        $('#user_id').val('').trigger('change');
        create_time_begin.val('');
        create_time_end.val('');
        delivery_time_begin.val('');
        delivery_time_end.val('');
        finish_time_begin.val('');
        finish_time_end.val('');
        refreshTable();
        return false;
    });
});