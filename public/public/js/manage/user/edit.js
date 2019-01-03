$(function () {

    // 真实姓名转拼音
    $('#real_name').change(function () {
        if(!utils.isEmpty($(this).val()) && utils.isEmpty($('#user_name').val()))
        {
            $.get('/manage/common/chineseToPinyin',{chinese:$(this).val()},function (data) {
                if(data.error_code == 0)
                {
                    $('#user_name').val(data.data);
                }
            });
        }
    });

    // 提交
    $('#userEdit').submit(function () {
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
        $('.btn-submit').prop('disabled',true).text('提交中...');
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
                $('.btn-submit').prop('disabled',false).text('提交');
            },
            error:function () {
                $('.btn-submit').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
        return false;
    });
});
