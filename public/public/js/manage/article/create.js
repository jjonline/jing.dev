$(function () {

    // 初始化
    editor = UE.getEditor("content");
    editor.ready(function () {
        editor.setHeight(450);
        editor.execCommand('fontfamily','微软雅黑'); //字体
        editor.execCommand('fontsize', '14px'); //字号
    });

    //上传封面裁剪插件
    utils.bindCutImageUploader('cover_image_file',{
        title:'裁剪并上传文章封面图',
        rate:'4/3',//裁剪图的比例
        width:400,//指定裁剪后图片宽度
        height:300,//指定裁剪后图片高度
        extraData:{'file_type':'image'},//额外塞入上传的post数据体重的filed-value对象数组
        success:function (data) {
            if(data['error_code'] == 0)
            {
                $('#cover_img').remove();
                $('.cover-image-file-container').prepend('<div id="cover_img" class="upload-preview"><img src="'+data.data.file_path+'"></div>');
                $('#cover_image_id').val(data.data.id);
            }else{
                utils.alert(data.error_msg ? data.error_msg : '未知错误');
            }
        },//裁剪并上传成功后的回调函数，data参数为服务器返回的json对象
        error:function () {
            utils.alert('网络或服务器异常，文件上传失败！');
        }//裁剪或上传失败的回调函数
    });

    // 自定义文章显示时间
    layui.use('laydate', function(){
        var laydate = layui.laydate;
        laydate.render({
            elem: '#show_time',
            type: 'datetime'
        });
    });

    // 添加关键词
    $(".add-tag-btn").on("click",function () {

    });

});
