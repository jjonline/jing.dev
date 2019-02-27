$(function () {
    // 初始化自定义字段拖动事件
    var sortAble = $("#columns_item_container");
    new Sortable(sortAble.get(0), {
        animation: 150,
        ghostClass: "columns_sortable"
    });

    // init有字段列表时自动打开
    if ($("#is_column").attr("checked")) {
        $("#columns_container").show();
        // 操作说明宽度fix + show
        $(".menu_deal_intro").css("width",$(".box-body").width() * 5 / 13).show();
    }

    // 删除字段确认提醒
    $(sortAble).on("click", ".fa-trash", function () {
        var item = $(this).parents('li');
        utils.confirm("确认删除该字段么？", function () {
            $(item).remove();
            utils.toast("已删除");
        });
    });

    // 需要自定义字段打开输入框和提示说明
    $("#is_column").on("switchChange.bootstrapSwitch",function (e,isCheck) {
        if (isCheck) {
            $("#columns_container").show();
            // 操作说明宽度fix + show
            $(".menu_deal_intro").css("width",$(".box-body").width() * 5 / 13).show();
        } else {
            $("#columns_container").hide();
            $(".menu_deal_intro").hide();
        }
    });

    /**
     * 新增字段列表方法封装
     * @param repeat
     */
    function insertColumns(repeat)
    {
        var li = '<li class="form-group columns_group">' +
            '           <div class="columns_manage">' +
            '               <i class="fa fa-trash"></i>' +
            '               <i class="fa fa-arrows-alt"></i>' +
            '               </div>' +
            '           <div class="col-xs-4">' +
            '               <div class="input-group">' +
            '                   <div class="input-group-addon">字段</div>' +
            '                       <input type="text" class="form-control columns" name="Columns[columns][]" placeholder="order.order_sn">' +
            '                   </div>' +
            '           </div>' +
            '           <div class="col-xs-4">' +
            '               <div class="input-group">' +
            '                   <div class="input-group-addon">名称</div>' +
            '                       <input type="text" class="form-control name" name="Columns[name][]" placeholder="字段名称">' +
            '               </div>' +
            '           </div>' +
            '           <div class="col-xs-2">' +
            '               <div class="checkbox">' +
            '                   <label>' +
            '                       <input type="checkbox" class="sorted" name="Columns[sorted][]"> 可排序' +
            '                   </label>' +
            '               </div>' +
            '           </div>' +
            '</li>';
        var _li = '';
        while (repeat) {
            _li = _li + li;
            repeat--;
        }
        $("#columns_item_container").append(_li);
    }

    // 新增5字段列表
    $("#insert_five_columns").on("click", function () {
        insertColumns(5);
    });
    // 新增10字段列表
    $("#insert_ten_columns").on("click", function () {
        insertColumns(10);
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
        $('#level2').html(option);
    });


    var select2Level1 = $('#level1').select2();
    var select2Level2 = $('#level2').select2();
    // 初始化分级菜单 level =1的在模板层面已处理
    if(level == 2)
    {
        var level1_id = item.parent_id;
        select2Level1.val(level1_id).trigger('change');//设置一级菜单即可
        //$('#level1').change();
    }
    if(level == 3)
    {
        var level2_id = item.parent_id;
        var level2Node  = menu[level2_id];
        var level1Node  = menu[level2Node.parent_id];
        // 渲染二级菜单
        var option = '<option value="">--新建二级菜单--</option>';
        $.each(menu,function (i,n) {
            if(n.parent_id == level1Node.id)
            {
                option += '<option value="'+n.id+'">'+n.name+'</option>';
            }
        });
        $('#level2').html(option);
        select2Level1.val(level1Node.id).trigger('change');//设置一级菜单
        select2Level2.val(level2_id).trigger('change');//设置二级菜单
    }

    // 提交
    $('#menuEdit').submit(function () {
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            return false;
        }
        if(utils.isEmpty($('#tag').val()))
        {
            $('#tag').focus();
            return false;
        }
        var data = $('#menuEdit').serializeArray();
        var fix_data = data.concat();
        // 将checkbox值重设成选中true不选false
        var index = 0;
        $.each(data,function (i,n) {
            // 清理掉可排序已选数组元素
            if (n.name == 'Columns[sorted][]') {
                fix_data.remove(i - index);
                index ++;
            }
        });
        // 添加自定义按顺序的可排序checkbox数组元素
        fix_data = fix_data.concat(
            $(".sorted").map(
                function() {
                    return {"name": this.name, "value": $(this).prop('checked') ? 1 : 0}
                }).get()
        );
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#menuAdd').attr('action'),
            type: 'POST',
            data: fix_data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/manage/menu/list';
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
