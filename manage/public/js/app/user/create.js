$(function () {
    var keyUpHandle;

    // 初始化二级栏目
    $('#dept_id1').change(function () {
        var dept_id1 = $(this).val();
        var _option  = '<option value="">--选择业态--</option>';
        if(utils.isEmpty(dept_id1))
        {
            return $('#dept_id2').html(_option);
        }
        $.ajax({
            url: '/common/getchilddept',
            type: 'POST',
            data: {'dept_id1':dept_id1},
            success: function (data) {
                if(data.error_code == 0){
                    $.each(data.data,function (i,n) {
                        _option += '<option value="'+n.id+'">'+n.name+'</option>';
                    });
                }
                $('#dept_id2').html(_option);
            },
            error:function () {
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
    });

    // 真实姓名转拼音，直接覆盖用户名
    $('#real_name').keyup(function () {
        var real_name = $(this).val();
        if(utils.isEmpty(real_name))
        {
            return true;
        }
        keyUpHandle && clearTimeout(keyUpHandle);
        keyUpHandle = setTimeout(function () {
            $.ajax({
                url: '/common/convertUserNameToPinyin',
                type: 'POST',
                data: {'name':real_name},
                success: function (data) {
                    if(data.error_code == 0){
                        $('#username').val(data.data);
                    }
                },
                error:function () {/*出现异常 静默*/}

            });
        }, 500);
    });

    //submit
    $('#userAdd').submit(function () {
        if(utils.isEmpty($('#dept_id1').val()))
        {
            utils.alert('请选择公司');
            return false;
        }
        if(utils.isEmpty($('#role_name').val()))
        {
            utils.alert('请选择角色');
            return false;
        }
        if(utils.isEmpty($('#real_name').val()))
        {
            $("#real_name").focus();
            return false;
        }
        if(utils.isEmpty($('#username').val()))
        {
            $("#username").focus();
            return false;
        }
        if(utils.isEmpty($('#password').val()))
        {
            $("#password").focus();
            return false;
        }
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#userAdd').attr('action'),
            type: 'POST',
            data: $('#userAdd').serializeArray(),
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/user/list';
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit').prop('disabled',false).text('新增');
            },
            error:function () {
                $('.btn-submit').prop('disabled',false).text('新增');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
        return false;
    });

});