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
                                json.data[n].operate += ' <a href="javascript:;" data-href="/manage/user/edit?id='+json.data[n].id+'" class="btn btn-xs btn-primary edit" data-id="'+json.data[n].id+'" data-user_name="'+ json.data[n].user_name +'" data-real_name="'+json.data[n].real_name+'" data-gender="'+json.data[n].gender+'" data-mobile="'+json.data[n].mobile+'" data-email="'+json.data[n].email+'" data-telephone="'+json.data[n].telephone+'" data-dept_id="'+json.data[n].dept_id+'"  data-role_id="'+json.data[n].role_id+'" data-remark="'+json.data[n].remark+'" data-is_leader="'+json.data[n].is_leader+'" data-enable="'+ json.data[n].enable +'"><i class="fa fa-pencil-square-o"></i> 编辑</a>';
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
    // 显示编辑浮层
    }).on('click','.edit',function () {
        $('#id').val($(this).data('id'));
        $('#real_name').val($(this).data('real_name'));
        $('#user_name').val($(this).data('user_name'));
        $('#mobile').val($(this).data('mobile'));
        $('#email').val($(this).data('email'));
        $('#telephone').val($(this).data('telephone'));
        $('#remark').val($(this).data('remark'));

        $('#gender').val($(this).data('gender')).trigger('change');
        $('#dept_id').val($(this).data('dept_id')).trigger('change');
        $('#role_id').val($(this).data('role_id')).trigger('change');

        $('#is_leader').bootstrapSwitch('state',!!$(this).data('is_leader'));
        $('#enable').bootstrapSwitch('state',!!$(this).data('enable'));

        $('#UserModal').modal('show');
    });

    // 提交编辑
    $('.btn-submit-edit').click(function () {
        if(utils.isEmpty($('#real_name').val()))
        {
            $('#real_name').focus();
            utils.toast('输入真实姓名');
            return false;
        }
        if(utils.isEmpty($('#user_name').val()))
        {
            $('#user_name').focus();
            utils.toast('输入用户名');
            return false;
        }
        if(!utils.isEmpty($('#password').val())) {
            if (!utils.isPassWord($('#password').val())) {
                $('#password').focus();
                utils.toast('登录密码必须有字母和数字构成');
                return false;
            }
        }
        if(!utils.isEmpty($('#mobile').val()))
        {
            if(!utils.isPhone($('#mobile').val()))
            {
                $('#mobile').focus();
                utils.toast('手机号格式有误');
                return false;
            }
        }
        if(!utils.isEmpty($('#email').val()))
        {
            if(!utils.isMail($('#email').val()))
            {
                $('#email').focus();
                utils.toast('邮箱格式有误');
                return false;
            }
        }
        if(utils.isEmpty($('#role_id').val()))
        {
            utils.toast('请选择所属角色');
            return false;
        }
        if(utils.isEmpty($('#dept_id').val()))
        {
            utils.toast('请选择所属部门');
            return false;
        }
        var data = $('#userEdit').serializeArray();
        //$('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#userEdit').attr('action'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/manage/user/list';
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                //$('.btn-submit').prop('disabled',false).text('提交');
            },
            error:function () {
                //$('.btn-submit').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
        return false;
    });

});