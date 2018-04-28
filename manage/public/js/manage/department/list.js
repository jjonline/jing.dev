$(function () {
    $('#mainTable').DataTable({
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
    utils.loadCss('/public/plugin/jquery-treegrid/css/jquery.treegrid.css');
    utils.loadJs('/public/plugin/jquery-treegrid/js/jquery.treegrid.min.js',function () {
        utils.loadJs('/public/plugin/jquery-treegrid/js/jquery.treegrid.bootstrap3.js',function () {
            $('#mainTable').treegrid();
        });
    });

    //sort
    $('.table').on('change','.list-sort-input',function () {
        var id = $(this).data('id');
        var sort = $(this).val();
        utils.ajaxConfirm('确认修改该部门的排序？','/manage/department/sort',{'id':id,'sort':sort},function () {
            location.reload();
        });
    //delete
    }).on('click','.delete',function () {
        var id = $(this).data('id');
        utils.ajaxConfirm('确认删除该部门么？','/manage/department/delete',{'id':id},function () {
            location.reload();
        });
    // edit
    }).on('click','.edit',function () {
        $('#DeptModal').modal('show');
        $('.btn-submit-create').hide();
        $('.btn-submit-edit').show();
        $('#DeptModelLabel').text('编辑部门');
        var parent_id = $(this).data('parent_id');
        if(utils.isEmpty(parent_id))
        {
            $('#parent_id').val('0').trigger('change');
        }else {
            $('#parent_id').val($(this).data('parent_id')).trigger('change');
        }
        $('#id').val($(this).data('id')).prop('disabled',false);
        $('#name').val($(this).data('name'));
        $('#sort').val($(this).data('sort'));
        $('#remark').val($(this).data('remark'));

        return false;
    // create
    }).on('click','.create',function () {
        $('#DeptModal').modal('show');
        $('.btn-submit-edit').hide();
        $('.btn-submit-create').show();
        $('#DeptModelLabel').text('新建子部门');
        $('#parent_id').val($(this).data('id')).trigger('change');
        $('#id').val('').prop('disabled',true);
        $('#name').val('');
        $('#sort').val('');
        $('#remark').val('');

        return false;
    });

    // 提交编辑
    $('.btn-submit-edit').click(function () {
        if($('#parent_id').val() == '-1')
        {
            utils.toast('请选择上级部门');
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('请输入部门名称');
            return false;
        }
        var data = $('#DeptForm').serializeArray();
        $('.btn-submit-edit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#DeptForm').data('edit'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        setTimeout(function () {
                            location.href = '/manage/department/list';
                        },300);
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

    // 提交创建子部门
    $('.btn-submit-create').click(function () {
        if($('#parent_id').val() == '-1')
        {
            utils.toast('请选择上级部门');
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('请输入部门名称');
            return false;
        }
        var data = $('#DeptForm').serializeArray();
        $('.btn-submit-create').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#DeptForm').data('create'),
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        setTimeout(function () {
                            location.href = '/manage/department/list';
                        },300);
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