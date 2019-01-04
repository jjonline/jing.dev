$(function () {

    utils.hideNav();

    $(".btn-save").click(function () {
        var flag = $(this).data('flag');
        var that = this;
        utils.showLoading('提交中，请稍后...');
        $(that).prop('disabled',true).text('提交中...');
        $.ajax({
            url: '/manage/config/save',
            type: 'POST',
            data: $("#"+flag).serializeArray(),
            success: function (data) {
                utils.hideLoading();
                utils.toast(data.error_msg ? data.error_msg : '未知错误');
                $(that).prop('disabled',false).text('提交');
            },
            error:function () {
                utils.hideLoading();
                $(that).prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
        return false;
    });
});