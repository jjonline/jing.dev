$(function () {

    // 菜单选择事件
    $('#level1').change(function () {
        var option = '<option value="">--新建二级菜单--</option>';
        if(utils.isEmpty($(this).val()))
        {
            $('#level2').html(option);
            return false;
        }
        var level1_name = $(this).val();
        $.each(menu,function (i,n) {
            if(n.parent_name == level1_name)
            {
                option += '<option value="'+n.name+'">'+n.title+'</option>';
            }
        });
        $('#level2').html(option);
    });

    // 新增菜单提交
    $('#menuAdd').submit(function () {
        if(utils.isEmpty($('#title').val()))
        {
            $('#title').focus();
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            return false;
        }
        var data = $('#menuAdd').serializeArray();
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#menuAdd').attr('action'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/menu/list';
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
