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
            "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
            "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项"
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
    }).on('click','.delete',function () {
        var id = $(this).data('id');
        utils.ajaxConfirm('确认删除该部门么？','/manage/department/delete',{'id':id},function () {
            location.reload();
        });
    });
});