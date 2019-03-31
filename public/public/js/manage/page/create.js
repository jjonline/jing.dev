$(function () {
    // 初始化自定义字段拖动事件
    var sortAble1 = $("#template_options");
    var sortAble2 = $("#content_options");
    new Sortable(sortAble1.get(0), {
        animation: 150,
        ghostClass: "template_sortable"
    });
    new Sortable(sortAble2.get(0), {
        animation: 150,
        ghostClass: "template_sortable"
    });

    // 删除模板待选项确认提醒
    $(sortAble1).on("click", ".fa-trash", function () {
        var item = $(this).parents('li');
        utils.confirm("确认删除该模板待选项么？", function () {
            $(item).remove();
            utils.toast("已删除");
        });
    });

    // 删除正文区块选项确认提醒
    $(sortAble2).on("click", ".fa-trash", function () {
        var item = $(this).parents('li');
        utils.confirm("确认删除该正文区块么？", function () {
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

    // 需要正文区块的开关事件
    $("#use_content").on("switchChange.bootstrapSwitch",function (e,isCheck) {
        if (isCheck) {
            $("#use_content_options").show();
        } else {
            $("#use_content_options").hide();
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

    /**
     * 统一设置输入框类型
     * @param content_type
     */
    function changeSectionInput(content_type)
    {
        if (content_type == '1') {
            $("#text_setting").show();
            $("#image_setting").hide();
        } else if (content_type == '2') {
            $("#text_setting").hide();
            $("#image_setting").show();
        } else {
            $("#text_setting").hide();
            $("#image_setting").hide();
        }
    }

    function insertContentNode(node)
    {
        var _content = '<li class="form-group content_options">' +
            '               <div class="content_options_manage">' +
            '                   <i class="fa fa-trash"></i>' +
            '                   <i class="fa fa-arrows-alt"></i>' +
            '               </div>' +
            '               <div class="content_options_section">' +
            '                   <input type="hidden" name="Content[id][]" class="id" value="'+node.id+'">' +
            '                   <input type="hidden" name="Content[name][]" class="name" value="'+node.name+'">' +
            '                   <input type="hidden" name="Content[type][]" class="type" value="'+node.type+'">' +
            '                   <input type="hidden" name="Content[length][]" class="length" value="'+node.length+'">' +
            '                   <input type="hidden" name="Content[width][]" class="width" value="'+node.width+'">' +
            '                   <input type="hidden" name="Content[height][]" class="height" value="'+node.height+'">' +
            '                   <input type="hidden" name="Content[explain][]" class="explain" value="'+node.explain+'">' +
            '                   <div class="view_section view_id">' +
            '                       <span>ID：</span>' + node.id +
            '                   </div>' +
            '                   <div class="view_section view_name">' +
            '                       <span>名称：</span>' + node.name +
            '                   </div>' +
            '                   <div class="view_section view_type">' +
            '                       <span>类型：</span>' + node.type_name +
            '                   </div>' +
            '                   <div class="view_section view_length">' +
            '                       <span>文字长度：</span>' + node.length +
            '                   </div>' +
            '                   <div class="view_section view_width">' +
            '                       <span>图片长度：</span>' + node.width +
            '                   </div>' +
            '                   <div class="view_section view_height">' +
            '                       <span>图片宽度：</span>' + node.height +
            '                   </div>' +
            '                   <div class="view_section view_explain">' +
            '                       <span>填写说明：</span>' + node.explain +
            '                   </div>' +
            '               </div>' +
            '       </li>';
        $("#content_options").append(_content);
    }

    /**
     * 新增区块相关方法
     */
    // 浮层新增1个正文区块
    $("#insert_one_content").on("click",function () {
        $("#ContentModal").modal("show");
    });

    // select类型才显示输入框
    $("#content_type").on("select2:select",function(event) {
        changeSectionInput($(this).val());
    });

    // 点击新增区块
    $(".btn-submit-content").click(function () {

        var id = $("#content_id");
        var name = $("#content_name");
        var type = $("#content_type");
        var length = $("#content_length");
        var width = $("#image_width");
        var height = $("#image_height");
        var explain = $("#content_explain");

        var node  = {};
        node.type = 3;
        node.type_name = '视频';

        if (utils.isEmpty(id.val())) {
            id.focus();
            utils.toast("请完善区块ID");
            return false;
        }
        if (utils.isEmpty(name.val())) {
            name.focus();
            utils.toast("请完善区块名称");
            return false;
        }
        if (utils.isEmpty(type.val())) {
            utils.toast("请选择区块类型");
            return false;
        }

        // 文本类型 指定最大长度
        if (type.val() == "1") {
            if (utils.isEmpty(length.val())) {
                length.focus();
                utils.toast("请完善区块文本最大长度");
                return false;
            }
            node.type = 1;
            node.type_name = '文字';
        }

        // 图片类型 指定最大宽高
        if (type.val() == "2") {
            if (utils.isEmpty(width.val())) {
                width.focus();
                utils.toast("请完善区块图片宽度");
                return false;
            }
            if (utils.isEmpty(height.val())) {
                height.focus();
                utils.toast("请完善区块图片高度");
                return false;
            }
            node.type = 2;
            node.type_name = '图片';
        }

        if (utils.isEmpty(explain.val())) {
            explain.focus();
            utils.toast("请完善区块填写说明");
            return false;
        }

        node.id = id.val();
        node.name = name.val();
        node.length = length.val();
        node.width = width.val();
        node.height = height.val();
        node.explain = explain.val();

        insertContentNode(node);

        $("#ContentModal").modal("hide");

        return false;
    });


    // 提交保存
    $(".btn-submit").on("click", function () {

        var that = this;

        var flag = $("#flag");
        var use_cover = $("#use_cover");
        var cover_width = $("#cover_options_width");
        var cover_height = $("#cover_options_height");

        if (utils.isEmpty(flag.val())) {
            flag.focus();
            utils.toast("请完善页面唯一标识");
            return false;
        }

        // 启用封面图
        if (use_cover.prop("checked")) {
            if (utils.isEmpty(cover_width.val())) {
                cover_width.focus();
                utils.toast("请完善封面图宽度");
                return false;
            }
            if (utils.isEmpty(cover_height.val())) {
                cover_height.focus();
                utils.toast("请完善封面图高度");
                return false;
            }
        }

        var form = $('#ContentSecForm');

        $(that).prop('disabled',true).text('提交中...');
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serializeArray(),
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/manage/page/config';
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $(that).prop('disabled',false).text('保存');
            },
            error:function () {
                $(that).prop('disabled',false).text('保存');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });

        return false;
    });
});
