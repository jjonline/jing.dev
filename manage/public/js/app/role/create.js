$(function () {
    $('#roleAdd').submit(function () {
        if (utils.isEmpty($('#name').val())) {
            $('#name').focus();
            return false;
        }
        if($("input[name='menu']:checked").length <= 0)
        {
            utils.alert('请选择角色权限');
            return false;
        }
        var menu_object = $("input[name='menu']:checked");
        var menu = [];
        $.each(menu_object,function (i,n) {
            menu.push($(n).val());
        });
        var data = {
            'name':$('#name').val(),
            'sort':$('#sort').val(),
            'remark':$('#remark').val(),
            'menu':menu
        };
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#roleAdd').attr('action'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/role/list';
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