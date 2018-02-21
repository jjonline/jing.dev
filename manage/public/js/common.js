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
     * 绑定bootstrapSwitch事件
     */
    $('.js-switch-container input').bootstrapSwitch();

});