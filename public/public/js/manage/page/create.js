$(function () {
    // 初始化自定义字段拖动事件
    var sortAble = $("#template_options");
    new Sortable(sortAble.get(0), {
        animation: 150,
        ghostClass: "template_sortable"
    });

    // 删除模板待选项确认提醒
    $(sortAble).on("click", ".fa-trash", function () {
        var item = $(this).parents('li');
        utils.confirm("确认删除该模板待选项么？", function () {
            $(item).remove();
            utils.toast("已删除");
        });
    });

    // 需要使用封面图的开关事件
    $("#use_cover").on("switchChange.bootstrapSwitch",function (e,isCheck) {
        if (isCheck) {
            $("#use_cover_setting").show();
        } else {
            $("#use_cover_setting").hide();
        }
    });

    // 需要下拉模板的开关事件
    $("#use_template").on("switchChange.bootstrapSwitch",function (e,isCheck) {
        if (isCheck) {
            $("#use_template_setting").show();
        } else {
            $("#use_template_setting").hide();
        }
    });

    /**
     * 新增模板待选项封装
     * @param repeat
     */
    function insertTemplateOption(repeat)
    {
        var li  = '<li class="form-group template_options">' +
            '          <div class="template_options_manage">' +
            '              <i class="fa fa-trash"></i>' +
            '              <i class="fa fa-arrows-alt"></i>' +
            '          </div>' +
            '          <div class="col-xs-5">' +
            '             <div class="input-group">' +
            '                <div class="input-group-addon">中文名称</div>' +
            '                <input type="text" class="form-control columns" name="Config[template_options_name][]" placeholder="例如：绿色主题">' +
            '                </div>' +
            '          </div>' +
            '          <div class="col-xs-5">' +
            '               <div class="input-group">' +
            '                   <div class="input-group-addon">模板文件名</div>' +
            '                   <input type="text" class="form-control name" name="Config[template_options_template][]" placeholder="例如：green_page">' +
            '          </div>' +
            '    </div>' +
            '</li>';
        var _li = '';
        while (repeat) {
            _li = _li + li;
            repeat--;
        }
        $("#template_options").append(_li);
    }

    // 新增1个模板待选项
    $("#insert_one_template").on("click", function () {
        insertTemplateOption(1);
    });
    // 新增5个模板待选项
    $("#insert_five_template").on("click", function () {
        insertTemplateOption(5);
    });
});
