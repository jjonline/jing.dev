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
            '           </div>' +
            '           <div class="col-xs-4">' +
            '               <div class="input-group">' +
            '                   <div class="input-group-addon">字段</div>' +
            '                       <input type="text" class="form-control columns" name="Columns[columns][]" placeholder="order.order_sn">' +
            '                   </div>' +
            '           </div>' +
            '           <div class="col-xs-3">' +
            '               <div class="input-group">' +
            '                   <div class="input-group-addon">名称</div>' +
            '                       <input type="text" class="form-control name" name="Columns[name][]" placeholder="字段名称">' +
            '               </div>' +
            '           </div>' +
            '           <div class="col-xs-5">' +
            '               <div class="col-sm-4">' +
            '                   <div class="checkbox">' +
            '                       <label>' +
            '                           <input type="checkbox" class="align" name="Columns[align][]"> 居中' +
            '                       </label>' +
            '                   </div>' +
            '               </div>' +
            '               <div class="col-sm-4">' +
            '                   <div class="checkbox">' +
            '                       <label>' +
            '                           <input type="checkbox" class="sorted" name="Columns[sorted][]"> 可排序' +
            '                       </label>' +
            '                   </div>' +
            '               </div>' +
            '               <div class="col-sm-4">' +
            '                   <div class="checkbox">' +
            '                       <label>' +
            '                           <input type="checkbox" class="default" name="Columns[default][]"> 必选' +
            '                       </label>' +
            '                   </div>' +
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

        // 修改可排序checkbox
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

        // 修改必选checkbox
        var fix_data1 = fix_data.concat();
        // 将checkbox值重设成选中true不选false
        var index1 = 0;
        $.each(fix_data,function (i,n) {
            // 清理掉可排序已选数组元素
            if (n.name == 'Columns[default][]') {
                fix_data1.remove(i - index1);
                index1 ++;
            }
        });
        // 添加自定义按顺序的可排序checkbox数组元素
        fix_data1 = fix_data1.concat(
            $(".default").map(
                function() {
                    return {"name": this.name, "value": $(this).prop('checked') ? 1 : 0}
                }).get()
        );

        // 修改居中checkbox
        var fix_data2 = fix_data1.concat();
        // 将checkbox值重设成选中true不选false
        var index2 = 0;
        $.each(fix_data1,function (i,n) {
            // 清理掉可排序已选数组元素
            if (n.name == 'Columns[align][]') {
                fix_data2.remove(i - index2);
                index2 ++;
            }
        });
        // 添加自定义按顺序的可排序checkbox数组元素
        fix_data2 = fix_data2.concat(
            $(".align").map(
                function() {
                    return {"name": this.name, "value": $(this).prop('checked') ? 1 : 0}
                }).get()
        );

        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#menuAdd').attr('action'),
            type: 'POST',
            data: fix_data2,
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
