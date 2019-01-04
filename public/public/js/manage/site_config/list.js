$(function () {
    $('#role_table').DataTable({
        serverSide: false,
        responsive: true,
        paging: false,
        searching: false,
        info: true,
        ordering: false,
        processing: true,
        lengthChange: true,
        AutoWidth: false,
        language: {
            "sLengthMenu": "显示 _MENU_ 项结果",
            "sZeroRecords": "没有匹配结果",
            "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
            "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
            "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
            "sInfoPostFix": "",
            "sSearch": "搜索:",
            "sUrl": "",
            "sEmptyTable": "数据为空",
            "sLoadingRecords": "载入中...",
            "sInfoThousands": ",",
            "oPaginate": {
                "sFirst": "首页",
                "sPrevious": "上页",
                "sNext": "下页",
                "sLast": "末页"
            },
            "oAria": {
                "sSortAscending": ": 以升序排列此列",
                "sSortDescending": ": 以降序排列此列"
            }
        }
    });

    //sort
    $('#table').on('change','.list-sort-input',function () {
        var id = $(this).data('id');
        var sort = $(this).val();
        utils.ajaxConfirm('确认修改该配置项的排序？','/manage/site_config/sort',{'id':id,'sort':sort},function () {
            location.reload();
        });
    }).on('click','.delete',function () {
        var id = $(this).data('id');
        utils.ajaxConfirm('确认删除该配置项么？','/manage/site_config/delete',{'id':id},function () {
            location.reload();
        });
    }).on('click','.edit',function () {
        // edit
        var data = $(this).parents('tr').data('json');
        $('.btn-submit-create').hide();
        $('.btn-submit-edit').show();
        $('#SiteConfigLabel').text('编辑配置项');
        $('#id').val(data.id).prop('disabled',false);
        $('#flag').val(data.flag);
        $('#key').val(data.key);
        $('#default').val(data.default);
        $('#name').val(data.name);
        $('#description').val(data.description);
        $('#sort').val(data.sort);
        $('#select_items').val(parseVal(data.select_items));
        $('#type').val(data.type).trigger('change');

        $('#SiteConfigModal').modal('show');

        return false;

    });
    // create
    $("#btn-create").on('click',function () {
        $("#SiteConfigForm").get(0).reset();
        $('.btn-submit-create').hide();
        $('.btn-submit-edit').show();
        $('#SiteConfigLabel').text('新增配置项');
        $('#id').val('').prop('disabled',true);
        $('#SiteConfigModal').modal('show');

        return false;
    });

    /**
     * 分析radio类型的待选值
     * @param val
     * @returns {string}
     */
    function parseVal(val)
    {
        var str = '';
        $.each(val,function (i,n) {
            str += n.value + '|' + n.name + "\n";
        });
        return str;
    }

    // 提交编辑
    $('.btn-submit-edit').click(function () {
        if($('#type').val() == '-1')
        {
            utils.toast('请完善配置项类型');
            $('#type').focus();
            return false;
        }
        if($('#type').val() == 'select')
        {
            if(utils.isEmpty($('#select_items').val()))
            {
                utils.toast('请完善select选项值');
                $('#select_items').focus();
                return false;
            }
        }
        if(utils.isEmpty($('#key').val()))
        {
            $('#key').focus();
            utils.toast('请完善配置项Key');
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('请完善配置项名称');
            return false;
        }
        if(utils.isEmpty($('#description').val()))
        {
            $('#description').focus();
            utils.toast('请完善配置项名称');
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('请完善配置项说明');
            return false;
        }

        var data = $('#SiteConfigForm').serializeArray();
        $('.btn-submit-edit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#SiteConfigForm').data('edit'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    $('#SiteConfigModal').modal('hide');
                    utils.toast(data.error_msg,2000,function () {
                        window.location.reload();
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit-edit').prop('disabled',false).text('提交');
            },
            error:function () {
                $('.btn-submit-edit').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
    });

    // select类型才显示输入框
    $("#type").on("select2:select",function(e) {
        $("#select_wrap").hide();
        if ($(this).val() == 'select') {
            $("#select_wrap").show();
        }
    });

    // 提交创建
    $('.btn-submit-create').click(function () {
        if($('#type').val() == '-1')
        {
            utils.toast('请完善配置项类型');
            return false;
        }
        if($('#type').val() == 'radio')
        {
            if(utils.isEmpty($('#val').val()))
            {
                utils.toast('请完善radio选项值');
                return false;
            }
        }
        if(utils.isEmpty($('#key').val()))
        {
            $('#key').focus();
            utils.toast('请完善配置项Key');
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('请完善配置项名称');
            return false;
        }
        if(utils.isEmpty($('#description').val()))
        {
            $('#description').focus();
            utils.toast('请完善配置项名称');
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('请完善配置项说明');
            return false;
        }

        var data = $('#SiteConfigForm').serializeArray();
        $('.btn-submit-create').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#SiteConfigForm').data('create'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    $('#SiteConfigModal').modal('hide');
                    utils.toast(data.error_msg,2000,function () {
                        window.location.reload();
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit-create').prop('disabled',false).text('提交');
            },
            error:function () {
                $('.btn-submit-create').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
    });
});