$(function () {
    var select2Level1 = $('#level1').select2();
    var select2Level2 = $('#level2').select2();
    // 初始化分级菜单 level =1的在模板层面已处理
    if(level == 2)
    {
        var level1_name = item.parent_name;
        select2Level1.val(level1_name).trigger('change');//设置一级菜单即可
    }
    if(level == 3)
    {
        var level2_name = item.parent_name;
        var level2Node  = menu[level2_name];
        var level1Node  = menu[level2Node.parent_name];
        // 渲染二级菜单
        var option = '<option value="">--新建二级菜单--</option>';
        $.each(menu,function (i,n) {
            if(n.parent_name == level1Node.name)
            {
                option += '<option value="'+n.name+'">'+n.title+'</option>';
            }
        });
        $('#level2').html(option);
        select2Level1.val(level1Node.name).trigger('change');//设置一级菜单
        select2Level2.val(level2_name).trigger('change');//设置二级菜单
    }

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

    // 提交
    $('#menuEdit').submit(function () {
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
        var data = $('#menuEdit').serializeArray();
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
