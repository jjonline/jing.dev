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
            "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
            "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项"
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