/**
 * 个人中心
 */
$(function () {

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