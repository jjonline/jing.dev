
$(function () {


    /**
     * 存在上传封面图
     */
    if (config.use_cover) {
        //上传销项裁剪插件
        utils.bindCutImageUploader("cut_upload",{
            // url:"4/3",// 可以单独设置后端接收上传的图片和额外参数的url，默认系统提供的附件资源管理器自动处理
            rate:"4/3",// 设置裁剪图的比例
            title:"封面图裁剪上传", // 设置裁剪浮层的标题
            width:400,//指定裁剪后图片宽度
            height:300,//指定裁剪后图片高度
            extraData:{'file_type':'image'},//额外塞入上传的post数据体重的filed-value对象数组
            success:function (data) {
                if(data["error_code"] == 0)
                {
                    $('#cover_img').remove();
                    $('.cover-image-file-container').prepend('<div id="cover_img" class="upload-preview"><img src="'+data.data.file_path+'"></div>');
                    $('#cover_id').val(data.data.id);
                }else{
                    utils.alert(data.error_msg ? data.error_msg : "未知错误");
                }
            },//裁剪并上传成功后的回调函数，data参数为服务器返回的json对象
            error:function () {
                utils.alert("网络或服务器异常，文件上传失败！");
            }//裁剪或上传失败的回调函数
        });

    }

});
