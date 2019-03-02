
$(function () {
    //init cookie记住用户名
    if(!utils.isEmpty(utils.cookie('UserName')))
    {
        $("input[name='username']").val(utils.cookie('UserName'));
    }
    //submit
    $('.login-form').submit(function () {
        var btnLogin = $('#btnLogin');
        var data = {
            '__token__':utils.getToken(),
            'user_name':$("input[name='username']").val(),
            'password':$("input[name='password']").val()
        };
        if(utils.isEmpty(data.user_name))
        {
            $('#login-tips').removeClass('hide').find('span').empty().text('请输入用户名');
            return false;
        }
        utils.cookie('UserName',data.user_name);//cookie记住登录用户名
        if(utils.isEmpty(data.password))
        {
            $('#login-tips').removeClass('hide').find('span').empty().text('请输入密码');
            return false;
        }
        btnLogin.prop('disabled',true).text('登录中...');
        $.ajax({
            url: $('.login-form').attr('action'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0)
                {
                    btnLogin.prop('disabled',true).text('登录成功，请稍后');
                    location.href = '/manage?token='+utils.randString();
                    return false;
                }
                if(data.error_code == -2)
                {
                    setTimeout(function () {
                        location.reload();
                    },3000);
                }
                btnLogin.prop('disabled',false).text('登录');
                $('#login-tips').removeClass('hide').find('span').empty().text(data.error_msg ? data.error_msg : '未知错误');
            },
            error: function (){
                btnLogin.prop('disabled',false).text('登录');
                $('#login-tips').removeClass('hide').find('span').empty().text('服务异常，请稍后再试或联系管理员');
            }
        });
        // prevent default event
        return false;
    });

    // 关闭提示框
    $('.close').click(function () {
        $('#login-tips').addClass('hide');
        return false;
    });

    /**
     * 检查http下的https是否有效
     */
    function isHttpsEffect() {
        try {
            // 非https请求自动检查是否支持https
            if(!utils.isHttps()) {
                var https = "https://" + document.domain;
                $.ajax({
                    url: https + "/manage/common/chineseToPinyin",
                    dataType:"jsonp",
                    data: {chinese:"颜如玉"},
                    success: function (data) {
                        if (data.error_code == 0) {
                            // https检查通过，跳转到https协议
                            window.location.href = location.href.replace("http", "https");
                        }
                    },
                    error:function () {}
                });
            }
        }catch (e) {}
    }
    isHttpsEffect();
});