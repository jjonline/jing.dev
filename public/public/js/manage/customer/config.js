$(function () {

    function insertOneLevel()
    {
        var html =  "<div class=\"col-xs-12\">" +
                    "   <div class=\"form-group\">" +
                    "       <div class=\"col-sm-4\">" +
                    "           <div class=\"input-group\">" +
                    "               <div class=\"input-group-addon\">" +
                    "                   <i class=\"fa fa-trophy\"></i>" +
                    "               </div>" +
                    "               <input type=\"text\" class=\"form-control\" placeholder=\"等级名称\" name=\"Config[name][]\" data-toggle=\"tooltip\" title=\"设置该级别的等级名称\">" +
                    "           </div>" +
                    "       </div>" +
                    "       <div class=\"col-sm-4\">" +
                    "           <div class=\"input-group\">" +
                    "               <div class=\"input-group-addon\">" +
                    "                   <i class=\"fa fa-cny\"></i>" +
                    "               </div>" +
                    "               <input type=\"number\" class=\"form-control\" placeholder=\"等级积分开始值\" name=\"Config[begin][]\" data-toggle=\"tooltip\" title=\"该等级所需积分开始值[含]\">" +
                    "           </div>" +
                    "       </div>" +
                    "       <div class=\"col-sm-4\">" +
                    "           <div class=\"input-group\">" +
                    "               <div class=\"input-group-addon\">" +
                    "                   <i class=\"fa fa-cny\"></i>" +
                    "               </div>" +
                    "               <input type=\"number\" class=\"form-control\" placeholder=\"等级积分结束值\" name=\"Config[end][]\" data-toggle=\"tooltip\" title=\"该等级所需积分结束值[不含]\">" +
                    "           </div>" +
                    "       </div>" +
                    "   </div>" +
                    "</div>";
        $("#level").append(html);
    }

    // 新增1个等级
    $("#insert_level").on("click", function () {
        insertOneLevel();
        $("[data-toggle='tooltip']").tooltip();
    });

    // 提交
    $(".btn-submit").on("click",function () {
        var that = this;
        $(that).prop("disabled",true).text("提交中...");
        $.ajax({
            url: "/manage/customer/configsave.html",
            type: 'POST',
            data: $('#ConfigForm').serializeArray(),
            success: function (data) {
                if(data.error_code == 0){
                    utils.toast("保存成功");
                }else{
                    utils.alert(data.error_msg ? data.error_msg : "未知错误");
                }
                $(that).prop("disabled",false).text("提交保存");
            },
            error:function () {
                $(that).prop("disabled",false).text("提交保存");
                utils.alert("网络或服务器异常，请稍后再试");
            }
        });

        return false;
    });
});
