
$(function () {

    /**
     * 清理runtime缓存文件
     */
    $("#clear_runtime").on("click", function () {
        utils.ajaxConfirm("确认清理runtime运行时文件么？","/manage/developer/runtime",function (data) {
            if (data.error_code == 0) {
                utils.toast(data.error_msg);
            } else {
                utils.alert(data.error_msg);
            }
        });
    });

    /**
     * 清理整站数据缓存
     */
    $("#clear_cache").on("click", function () {
        utils.ajaxConfirm("确认清理整站数据缓存么？清理后系统将自动重建","/manage/developer/cache",function (data) {
            if (data.error_code == 0) {
                utils.toast(data.error_msg);
            } else {
                utils.alert(data.error_msg);
            }
        });
    });

});
