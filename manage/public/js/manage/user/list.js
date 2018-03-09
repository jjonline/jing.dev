$(function () {
    var searchBeginDate = $("#search_begin_date");
    var searchEndDate = $("#search_end_date");
    var selectDept = $('#select_dept');
    var txtSearch = $('#txt_search');
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

        // 选择所辖部门
        selectDept.on('change',function () {
            refreshTable();
        });

        txtSearch.on("keyup", function () {
            keyUpHandle && clearTimeout(keyUpHandle);
            keyUpHandle = setTimeout(function () {
                utils.cookie('txtUserSearch', txtSearch.val());
                refreshTable();
            }, 600);
        });

        // 绑定DateTimePicker时间筛选组件动作
        utils.bindDateTimePicker($(".search_date"));
        // 日历时间组件绑定时间出发数据重载
        $(".search_date").datetimepicker().on('changeDate', function () {
            refreshTable();
        });
        //清理开始时间
        $('.clear-begin-data').click(function () {
            $('#search_begin_date').val('');
            refreshTable();
        });
        //清理结束时间
        $('.clear-end-data').click(function () {
            $('#search_end_date').val('');
            refreshTable();
        });
    };

    var pageDataSearch;

    var refreshTable = function () {
        pageDataSearch.ajax.reload(null, false);
    };

    var initTable = function () {
        pageDataSearch = $('#table').DataTable({
            serverSide: true,
            responsive: true,
            paging: true,
            searching: false,
            info: true,
            ordering: true,
            processing: true,
            pageLength: 100,
            lengthChange: false,
            AutoWidth: false,
            ajax: {
                url: '/manage/user/list',
                type: 'GET',
                data: function (d) {
                    return $.extend({}, d, {
                        dept_id: selectDept.val(),
                        keyword: txtSearch.val(),
                        begin_date: searchBeginDate.val(),
                        end_date: searchEndDate.val(),
                        status: $("#task_status button.active").data("status"),
                        task_type: $("#task_type").val()
                    });
                },
                dataSrc: function (json) {
                    if (json.data && json.data.length > 0) {
                        var user_id = utils.cookie('user_id');
                        for (var n in json.data) {
                            json.data[n].operate = '';
                            // 启用|禁用账号按钮
                            if(user_id != json.data[n].id)
                            {
                                if(json.data[n].enable == 1)
                                {
                                    json.data[n].operate += ' <a href="javascript:;" data-href="/manage/user/enableToggle?id='+json.data[n].id+'" class="btn btn-xs btn-danger enableToggle" data-id="'+json.data[n].id+'" data-enable="'+ json.data[n].enable +'"><i class="fa fa-toggle-off"></i> 禁用</a>';
                                }else{
                                    json.data[n].operate += ' <a href="javascript:;" data-href="/manage/user/enableToggle?id='+json.data[n].id+'" class="btn btn-xs btn-success enableToggle" data-id="'+json.data[n].id+'" data-enable="'+ json.data[n].enable +'"><i class="fa fa-toggle-on"></i> 启用</a>';
                                }
                            }else {
                                json.data[n].operate += '<span class="label bg-maroon"> 您自己 </span>';
                            }
                            // 账号状态
                            if(json.data[n].enable == 1)
                            {
                                json.data[n].enable = '<span class="label bg-olive">正常</span>';
                            }else {
                                json.data[n].enable = '<span class="label bg-orange">禁用</span>';
                            }
                        }
                    }
                    return json.data;
                }
            },
            columns: [
                {data: 'user_name'},
                {data: 'dept_name'},
                {data: 'role_name'},
                {data: 'real_name'},
                {data: 'mobile'},
                {data: 'email'},
                {data: 'create_time'},
                {data: 'remark'},
                {data: 'enable'},
                {data: 'operate'}
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

    // 启用禁用
    $('#table').on('click','.enableToggle',function () {
        var id   = $(this).data('id');
        var url  = $(this).data('href');
        var text = $(this).data('enable') == 1 ? '确认禁用该会员么？' : '确认启用该会员么？';
        utils.ajaxConfirm(text,url,{'id':id},function () {
            refreshTable();
        });
        return false;
    });



});