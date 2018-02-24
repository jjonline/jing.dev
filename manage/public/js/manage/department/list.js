$(function () {
    var searchBeginDate = $("#search_begin_date");
    var searchEndDate = $("#search_end_date");
    var selectProject = $('#select_project');
    var txtSearch = $('#txt_search');
    var keyUpHandle;

    var targetSearch = utils.cookie('txtDepartmentSearch');
    if (!utils.isEmpty(targetSearch)) {
        txtSearch.val(targetSearch);
        txtSearch.select();
    }


    var bindSearchEvents = function () {
        $("#department_container button").on("click", function () {
            $(this).parent().children("button.btn-primary").removeClass("btn-primary").removeClass('active').addClass('btn-default');
            $(this).removeClass('btn-default').addClass("btn-primary").addClass('active');
            refreshTable();
        });

        txtSearch.on("keyup", function () {
            keyUpHandle && clearTimeout(keyUpHandle);
            keyUpHandle = setTimeout(function () {
                utils.cookie('txtDepartmentSearch', txtSearch.val());
                refreshTable();
            }, 600);
        });

        // $(".search_date").datepicker({
        //     language: 'zh-CN',
        //     autoclose: true,
        //     todayHighlight: true,
        //     format: 'yyyy-mm-dd',
        //     minView: 2,
        //     viewSelect: 2
        // }).on('changeDate', function () {
        //     // $.cookie($(this).data("key"), $(this).val(), {expires: 365});
        //     setSearchDate();
        //     refreshTable();
        // });
        //
        // var setSearchDate = function () {
        //     searchEndDate.datepicker('setStartDate', searchBeginDate.val());
        //     searchBeginDate.datepicker('setEndDate', searchEndDate.val());
        // };
        // setSearchDate();
    };

    var pageDataSearch;

    var refreshTable = function () {
        pageDataSearch.ajax.reload(null, false);
    };

    var initTable = function () {
        pageDataSearch = $('#mainTable').DataTable({
            serverSide: true,
            responsive: true,
            paging: true,
            searching: false,
            info: true,
            ordering: true,
            processing: true,
            pageLength: 100,
            lengthChange: false,
            AutoWidth: false,
            ajax: {
                url: 'list',
                type: 'GET',
                data: function (d) {
                    return $.extend({}, d, {
                        project_id: selectProject.val(),
                        keyword: txtSearch.val(),
                        begin_date: searchBeginDate.val(),
                        end_date: searchEndDate.val(),
                        level: $("#department_container button.active").data("status"),
                        deleted: $("#deleted_container button.active").data("status")
                    });
                },
                dataSrc: function (json) {
                    if (json.data && json.data.length > 0) {
                        for (var n in json.data) {
                            json.data[n].operate = '<a href="/department/edit?id='+json.data[n].id+'" class="btn btn-xs btn-primary delete" data-id="'+json.data[n].id+'"><i class="fa fa-pencil-square-o"></i> 编辑</a> ';
                            if(utils.isEmpty(json.data[n].delete_time))
                            {
                                json.data[n].delete = '<label class="badge bg-green">有效</label>';
                                json.data[n].operate += ' <a href="javascript:;" class="btn btn-xs btn-danger delete" data-id="'+json.data[n].id+'"><i class="fa fa-trash-o"></i> 删除</a> ';
                            }else{
                                json.data[n].delete = '<label class="badge bg-red">失效</label>';
                                json.data[n].operate += '<a href="javascript:;" disabled="disabled" class="btn btn-xs btn-default" data-id="'+json.data[n].id+'">删除</a> ';
                            }
                            if(json.data[n].level == 1)
                            {
                                json.data[n].operate += ' <a href="/department/create?dept_id='+json.data[n].id+'" class="btn btn-xs btn-info create" data-id="'+json.data[n].id+'"><i class="fa fa-plus"></i> 新建业态</a> ';
                            }
                        }
                    }
                    return json.data;
                }
            },
            columns: [
                {data: 'name'},
                {data: 'level'},
                {data: 'parent_name'},
                {data: 'remark'},
                {data: 'create_time'},
                {data: 'delete'},
                {data: 'operate'}
            ],
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
    };

    var initPage = function () {
        initTable();
        bindSearchEvents();
    };

    initPage();
});