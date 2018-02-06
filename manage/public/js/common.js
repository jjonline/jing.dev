$(function () {


    /**
     * 绑定select2事件
     */
    $(".select2").select2();

    /**
     * 绑定button模拟的radio
     */
    $('.js-select-container').on('click','button',function () {
        $(this).parents('.js-select-container').find('button').removeClass('btn-primary');
        //$('.js-select-container button').removeClass('btn-primary');
        $(this).addClass('btn-primary');
    });

    /**
     * 初始化切换公司下拉框宽度
     */
    if($('#default_dept1').width() > 160)
    {
        $('#dropdown-menu-dept').width($('#default_dept1').width());
    }
    /**
     * 切换公司
     */
    $('#default_dept1').on('click','.dept1-item',function () {
        var dept_id1 = $(this).data('dept');
        $.ajax({
            url: '/common/switchDept1',
            type: 'POST',
            data: {'dept_id1':dept_id1},
            success: function (data) {
                if(data.error_code == 0){
                   location.reload();
                }
            },
            error:function () {/*静默*/}
        });
    });
    /**
     * 切换业态
     */
    $('#default_dept2').change(function () {
        var dept_id2 = $(this).val();
        $.ajax({
            url: '/common/switchDept2',
            type: 'POST',
            data: {'dept_id2':dept_id2},
            success: function (data) {
                if(data.error_code == 0){
                    location.reload();
                }
            },
            error:function () {/*静默*/}
        });
    });

});