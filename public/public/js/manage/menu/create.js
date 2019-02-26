$(function () {

    // 初始化自定义字段拖动事件
    var sortAble = $("#columns_item_container");
    new Sortable(sortAble.get(0), {
        animation: 150,
        ghostClass: "columns_sortable"
    });
    // 删除字段确认提醒
    $(sortAble).on("click", ".fa-trash", function () {
        var item = $(this).parents('li');
        utils.confirm("确认删除该字段么？", function () {
            $(item).remove();
            utils.toast("已删除");
        });
    });

    // 需要自定义字段打开输入框
    $("#is_column").on("switchChange.bootstrapSwitch",function (e,isCheck) {
        if (isCheck) {
            $("#columns_container").show();
        } else {
            $("#columns_container").hide();
        }
    });

    // 菜单选择事件
    $('#level1').change(function () {
        var option = '<option value="">--新建二级菜单--</option>';
        if(utils.isEmpty($(this).val()))
        {
            $('#level2').html(option);
            return false;
        }
        var level1_id = $(this).val();
        $.each(menu,function (i,n) {
            if(n.parent_id == level1_id)
            {
                option += '<option value="'+n.id+'">'+n.name+'</option>';
            }
        });
        $("#level2").html(option);
    });

    // 新增菜单提交
    $('#menuAdd').submit(function () {
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('输入菜单名称');
            return false;
        }
        if(utils.isEmpty($('#tag').val()))
        {
            $('#tag').focus();
            utils.toast('输入菜单tag');
            return false;
        }
        if(!utils.isEmpty($('#extra_param').val()))
        {
            try{
                JSON.parse($('#extra_param').val());
            }catch (e) {
                $('#extra_param').focus();
                utils.toast('菜单额外数据JSON字符串解析失败');
                return false;
            }
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
                        location.href = '/manage/menu/list';
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
