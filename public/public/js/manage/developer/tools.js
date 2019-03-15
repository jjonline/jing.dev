
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
            utils.ajaxConfirm("请确认你知道在干什么！！存在的队列也将一并被删除，此项操作存在风险","/manage/developer/cache",function (data) {
            if (data.error_code == 0) {
                utils.toast(data.error_msg);
            } else {
                utils.alert(data.error_msg);
            }
        });
    });

});
