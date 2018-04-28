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
            "sProcessing": "<i class=\"fa fa-refresh fa-spin\"></i> 载入中...",
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
        utils.ajaxConfirm('确认修改该角色的排序？','/manage/role/sort',{'id':id,'sort':sort},function () {
            location.reload();
        });
    }).on('click','.delete',function () {
        var id = $(this).data('id');
        utils.ajaxConfirm('确认删除该角色么？','/manage/role/delete',{'id':id},function () {
            location.reload();
        });
    });
});