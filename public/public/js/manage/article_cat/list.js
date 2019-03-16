$(function () {
    $('#table').DataTable({
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
    $('.table').on('change','.list-sort-input',function () {
        var id = $(this).data('id');
        var sort = $(this).val();
        utils.ajaxConfirm('确认修改该文章分类的排序？','/manage/article_cat/sort',{'id':id,'sort':sort},function () {
            location.reload();
        });
    }).on('click','.delete',function () {
        var id = $(this).data('id');
        utils.ajaxConfirm('确认删除该文章分类么？','/manage/article_cat/delete',{'id':id},function () {
            location.reload();
        });
        return false;
    }).on('click','.edit',function () {
        // 编辑模式
        var data = $(this).parents('tr').data('json');
        $('#ArticleCatModalLabel').text('编辑文章分类');
        $('#parent_id').select2({'disabled':true});
        if(utils.isEmpty(data.parent_id))
        {
            $('#parent_id').val('0').trigger('change');
        }else {
            $('#parent_id').val(data.parent_id).trigger('change');
        }
        $('#id').val(data.id).prop('disabled',false);
        $('#name').val(data.name);
        $('#icon').val(data.icon);
        $('#sort').val(data.sort);
        $('#remark').val(data.remark);

        $('#ArticleCatModal').modal('show');
        return false;
    }).on('click','.create',function () {
        // 新建子分类
        $('#ArticleCatModalLabel').text('新建文章子分类');
        $('#parent_id').select2({'disabled':false});
        $('#parent_id').val($(this).data('id')).trigger('change');
        $('#id').val('').prop('disabled',true);
        $('#name').val('');
        $('#sort').val('');
        $('#icon').val('');
        $('#remark').val('');

        $('#ArticleCatModal').modal('show');
        return false;
    });

    // 新增分类
    $('#create').on('click',function () {
        $('#ArticleCatModalLabel').text('新建文章分类');
        $('#parent_id').select2({'disabled':false});
        $('#parent_id').val('-1').trigger('change');
        $('#id').val('').prop('disabled',true);
        $('#name').val('');
        $('#sort').val('');
        $('#icon').val('');
        $('#remark').val('');

        $('#ArticleCatModal').modal('show');
        return false;
    });

    // 提交编辑/新增
    $('.btn-submit-edit').click(function () {
        if($('#parent_id').val() == '-1')
        {
            utils.toast('请选择上级分类');
            return false;
        }
        if(utils.isEmpty($('#name').val()))
        {
            $('#name').focus();
            utils.toast('请输入分类名称');
            return false;
        }
        var action = '';
        if(utils.isEmpty($('#id').val()))
        {
            action = $('#ArticleCatForm').data('create');
        }else
        {
            action = $('#ArticleCatForm').data('edit')
        }
        $('#parent_id').select2({'disabled':false});
        var data = $('#ArticleCatForm').serializeArray();
        $('.btn-submit-edit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: action,
            type: 'POST',
            data: data,
            success: function (data) {
                if(data.error_code == 0)
                {
                    utils.toast(data.error_msg,3000,function () {
                        location.href = '/manage/article_cat/list';
                    });
                } else {
                    $('#parent_id').select2({'disabled':true});
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit-edit').prop('disabled',false).text('提交');
            },
            error:function () {
                $('#parent_id').select2({'disabled':true});
                $('.btn-submit-edit').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
    });
});
