$(function () {
    var searchBeginDate = $("#search_begin_date");
    var searchEndDate = $("#search_end_date");
    var selectProject = $('#select_project');
    var txtSearch = $('#txt_search');
    var keyUpHandle;

    var targetSearch = utils.cookie('txtDepartmentSearch');
    if (!utils.isEmpty(targetSearch)) {
        txtSearch.val(targetSearch);
        txtSearch.select();
    }


    var bindSearchEvents = function () {
        $("#user_container button").on("click", function () {
            $(this).parent().children("button.btn-primary").removeClass("btn-primary").removeClass('active').addClass('btn-default');
            $(this).removeClass('btn-default').addClass("btn-primary").addClass('active');
            refreshTable();
        });

        txtSearch.on("keyup", function () {
            keyUpHandle && clearTimeout(keyUpHandle);
            keyUpHandle = setTimeout(function () {
                utils.cookie('txtDepartmentSearch', txtSearch.val());
                refreshTable();
            }, 600);
        });
    };

    var pageDataSearch;

    var refreshTable = function () {
        pageDataSearch.ajax.reload(null, false);
    };

    var initTable = function () {
        pageDataSearch = $('#mainTable').DataTable({
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
                url: '/user/list',
                type: 'GET',
                data: function (d) {
                    return $.extend({}, d, {
                        project_id: selectProject.val(),
                        keyword: txtSearch.val(),
                        begin_date: searchBeginDate.val(),
                        end_date: searchEndDate.val(),
                        enabled: $("#user_container button.active").data("status"),
                        deleted: $("#deleted_container button.active").data("status")
                    });
                },
                dataSrc: function (json) {
                    if (json.data && json.data.length > 0) {
                        for (var n in json.data) {
                            json.data[n].operate = '<a href="/user/edit?id='+json.data[n].id+'" class="btn btn-xs btn-primary delete" data-id="'+json.data[n].id+'"><i class="fa fa-pencil-square-o"></i> 编辑</a> ';
                            if(json.data[n].enabled == 1)
                            {
                                json.data[n].enabled = '<label class="badge bg-green">启用中</label>';
                                json.data[n].operate += ' <a href="javascript:;" class="btn btn-xs btn-danger toggleEnabled" data-id="'+json.data[n].id+'" data-enabled="0"><i class="fa fa-toggle-off"></i> 禁用</a> ';
                            }else{
                                json.data[n].enabled = '<label class="badge bg-red">已禁用</label>';
                                json.data[n].operate += '<a href="javascript:;" class="btn btn-xs btn-default toggleEnabled" data-id="'+json.data[n].id+'" data-enabled="1"><i class="fa fa-toggle-on"></i> 启用</a> ';
                            }
                            if(json.data[n].role_name == '公司管理员')
                            {
                                json.data[n].operate += ' <a href="javascript:;" data-href="/user/quota?id='+json.data[n].id+'" class="btn btn-xs btn-warning quota" data-quota="'+json.data[n].device_quota+'" data-id="'+json.data[n].id+'"><i class="fa fa-quora"></i> 分配额度</a>';
                            }
                        }
                    }
                    return json.data;
                }
            },
            columns: [
                {data: 'username'},
                {data: 'real_name'},
                {data: 'phone'},
                {data: 'dept_name'},
                {data: 'device_quota'},
                {data: 'role_name'},
                {data: 'create_time'},
                {data: 'enabled'},
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

    // 绑定启用禁用事件
    $('#mainTable').on('click','.toggleEnabled',function () {
        var data = {'enabled':$(this).data('enabled'),'id':$(this).data('id')};
        var text = '启用';
        if(data.enabled == 0)
        {
            text = '禁用';
        }
        bootbox.dialog({
            message: '确认要'+text+'该用户么？',
            title: '操作确认',
            onEscape: true,
            backdrop: true,
            buttons: {
                cancel:{
                    label: '取消',
                    className: "btn btn-default",
                    callback: function () {}
                },
                ok: {
                    label: '确定',
                    className: "btn btn-info",
                    callback: function () {
                        $.ajax({
                            url: '/user/toggleenabled',
                            type: 'POST',
                            data: data,
                            success: function (data) {
                                if(data.error_code == 0){
                                    utils.alert('操作成功',function () {
                                        refreshTable();
                                    });
                                }else{
                                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                                }
                            },
                            error:function () {
                                utils.alert('网络或服务器异常，请稍后再试');
                            }
                        });
                    }
                }
            }
        });
    // 为公司管理员分配额度
    }).on('click','.quota',function () {
        var user_id = $(this).data('id');
        var request_url = $(this).data('href');
        var quota = $(this).data('quota');

        bootbox.dialog({
            message: '<form class="">' +
            '           <div class="form-group">' +
            '                <label for="quota" id="quota-label">可绑定设备额度</label>' +
            '                <input type="number" id="quota" class="form-control" placeholder="设定可绑定设备额度">' +
            '           </div>' +
            '<p>账号当前已分配：<strong>'+quota+'</strong></p>' +
            '         </form>',
            title: '操作确认',
            onEscape: true,
            backdrop: true,
            buttons: {
                success: {
                    label: '提交',
                    className: "btn btn-info",
                    callback: function () {
                        var device_quota = $('#quota').val();
                        if(utils.isEmpty(device_quota))
                        {
                            $('#quota').focus();
                            return false;
                        }
                        $.ajax({
                            url: request_url,
                            type: 'POST',
                            data: {'user_id':user_id,'device_quota':device_quota},
                            success: function (data) {
                                if(data.error_code == 0){
                                    refreshTable();
                                    utils.alert('可绑定设备额度分配成功',function () {
                                        refreshTable();
                                    });
                                }else{
                                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                                }
                            },
                            error:function () {
                                utils.alert('网络或服务器异常，请稍后再试');
                            }
                        });
                    }
                },
                Cancel : {
                    label: '取消',
                    className: "btn btn-default",
                    callback: true
                }
            }
        });
    });
});