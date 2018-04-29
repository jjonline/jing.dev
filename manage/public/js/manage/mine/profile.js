/**
 * 个人中心
 */
$(function () {
    $('#user_log').DataTable({
        serverSide: false,
        responsive: true,
        paging: false,
        searching: false,
        info: true,
        ordering: false,
        processing: true,
        lengthChange: true,
        AutoWidth: false,
        language: {
            "sProcessing": "<i class=\"fa fa-refresh fa-spin\"></i> 载入中...",
            "sLengthMenu": "显示 _MENU_ 项结果",
            "sZeroRecords": "没有匹配结果",
            "sInfo": "仅显示最近10条记录",
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

    // 编辑个人账户信息 1、登录用的手机号；2、登录用的邮箱；3、账号密码
    $('#profile').on('click','.edit',function () {
        $('#ProfileModal').modal('show');
        $('#ProfileForm').get(0).reset();
    });

    // submit
    $('.btn-submit-edit').click(function () {
        if(utils.isEmpty($('#password').val()))
        {
            $('#password').focus();
            utils.toast('请输入您的账号密码');
            return false;
        }
        var data = $('#ProfileForm').serializeArray();
        $('.btn-submit-create').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#ProfileForm').attr('action'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        setTimeout(function () {
                            location.href = '/manage/mine/profile';
                        },300);
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit-create').prop('disabled',false).text('提交');
            },
            error:function () {
                $('.btn-submit-create').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
    });

});