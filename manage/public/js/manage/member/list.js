$(function () {
    var searchBeginDate = $("#search_begin_date");
    var searchEndDate = $("#search_end_date");
    var txtSearch = $('#txt_search');
    var keyUpHandle;

    var targetSearch = utils.cookie('txtMemberSearch');
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
                utils.cookie('txtMemberSearch', txtSearch.val());
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
                url: '/manage/member/list',
                type: 'GET',
                data: function (d) {
                    return $.extend({}, d, {
                        keyword: txtSearch.val(),
                        begin_date: searchBeginDate.val(),
                        end_date: searchEndDate.val(),
                        status: $("#task_status button.active").data("status")
                    });
                },
                dataSrc: function (json) {
                    if (json.data && json.data.length > 0) {
                        for (var n in json.data) {
                            json.data[n].operate = '';
                            if(has_edit_permission)
                            {
                                json.data[n].operate += ' <a href="javascript:;" data-href="/manage/member/edit?id='+json.data[n].id+'" class="btn btn-xs btn-primary edit" data-id="'+json.data[n].id+'" data-json=\''+ JSON.stringify(json.data[n]) +'\'><i class="fa fa-pencil-square-o"></i> 编辑</a>';
                            }

                            // 启用|禁用账号按钮
                            if(has_enable_permission)
                            {
                                if(json.data[n].enable == 1)
                                {
                                    json.data[n].operate += ' <a href="javascript:;" data-href="/manage/member/enableToggle?id='+json.data[n].id+'" class="btn btn-xs btn-danger enableToggle" data-id="'+json.data[n].id+'" data-enable="'+ json.data[n].enable +'"><i class="fa fa-toggle-off"></i> 禁用</a>';
                                }else{
                                    json.data[n].operate += ' <a href="javascript:;" data-href="/manage/member/enableToggle?id='+json.data[n].id+'" class="btn btn-xs btn-success enableToggle" data-id="'+json.data[n].id+'" data-enable="'+ json.data[n].enable +'"><i class="fa fa-toggle-on"></i> 启用</a>';
                                }
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
                {data: 'id'},
                {data: 'user_name'},
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
        var text = $(this).data('enable') == 1 ? '确认禁用该前台会员么？' : '确认启用该前台会员么？';
        utils.ajaxConfirm(text,url,{'id':id},function () {
            refreshTable();
        });
        return false;
    // 显示编辑浮层
    }).on('click','.edit',function () {
        $('#id').val($(this).data('id'));
        var member = $(this).data('json');

        $('#address_picker').distpicker();

        $('#real_name').val(member.real_name);
        $('#user_name').val(member.user_name);
        $('#mobile').val(member.mobile);
        $('#email').val(member.email);
        $('#telephone').val(member.telephone);
        $('#remark').val(member.remark);
        $('#gender').val(member.gender).trigger('change');
        $('#enable').bootstrapSwitch('state',!!member.enable);
        $('.province').val(member.province).trigger('change');
        $('.city').val(member.city).trigger('change');
        $('.district').val(member.district).trigger('change');


        $('#MemberEditModal').modal('show');
    });

    // 省市县event
    $('.province').on('change',function () {
        var p = $(this).val();
        if(!utils.isEmpty(p))
        {
            $('#address').val(p);
        }
    });
    $('.city').on('change',function () {
        var p = $('.province').val();
        var c = $(this).val();
        if(!utils.isEmpty(c))
        {
            $('#address').val(p + c);
        }
    });
    $('.district').on('change',function () {
        var p = $('.province').val();
        var c = $('.city').val();
        var d = $(this).val();
        if(!utils.isEmpty(d))
        {
            $('#address').val(p + c + d);
        }
    });

    // 提交编辑
    $('.btn-edit-submit').click(function () {
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
        var data = $('#MemberEditForm').serializeArray();
        $('.btn-edit-submit').prop('disabled',true).text('提交中...');
        utils.showLoading('提交中，请稍后...');
        $.ajax({
            url: $('#MemberEditForm').attr('action'),
            type: 'POST',
            data: data,
            success: function (data) {
                utils.hideLoading();
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        $('#MemberEditModal').modal('hide');
                        refreshTable();
                    });
                }else{
                    utils.toast(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-edit-submit').prop('disabled',false).text('提交');
            },
            error:function () {
                utils.hideLoading();
                $('.btn-edit-submit').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
        return false;
    });

});