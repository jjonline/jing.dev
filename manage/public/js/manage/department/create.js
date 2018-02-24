$(function () {

    // 管理员新增部门提交
    $('#departmentAdd').submit(function () {
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            return false;
        }
        var data = $('#departmentAdd').serializeArray();
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#departmentAdd').attr('action'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/department/list';
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
