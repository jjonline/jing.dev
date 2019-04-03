
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

    // 正文区块内图片上传
    var section_images = $(".image_file_upload");
    $.each(section_images, function (i,n) {
        var input_id = $(n).data("id");
        var id = "image_upload_" + input_id;
        var node = $(n);
        // 带上传进度条的文件上传
        utils.bindAjaxUploader(id,{
            // url:'',//上传文件后端Url，留空则为/manage/upload/upload?origin=ajax
            allow_extension: null,//null不限制、需限制时使用数组 ['jpg','jpeg']
            extraData: {'file_type':'image'}, //上传额外附带的key-value
            multiple:true,//是否允许选择多个文件，默认允许多个
            success:function (data) {
                if(data["error_code"] == 0)
                {
                    $("#" + input_id).val(data.data.file_path);
                    var node_parent = node.parents('.content_section_body');
                    node_parent.find(".upload-preview").remove();
                    node_parent.prepend('<div class="upload-preview"><img src="'+data.data.file_path+'"></div>');
                } else {
                    utils.alert(data.error_msg ? data.error_msg : "未知错误");
                }
            },//上传成功的回调函数
            error:function () {
                utils.alert("网络或服务器异常，文件上传失败！");
            }//上传失败的回调函数
        });
    });

    // 正文区块内视频上传
    var section_videos = $(".video_file_upload");
    $.each(section_videos, function (i,n) {
        var input_id = $(n).data("id");
        var id = "video_upload_" + input_id;
        var node = $(n);
        // 带上传进度条的文件上传
        utils.bindAjaxUploader(id,{
            // url:'',//上传文件后端Url，留空则为/manage/upload/upload?origin=ajax
            allow_extension: ['mp4'],//null不限制、需限制时使用数组 ['jpg','jpeg']
            extraData: {'is_safe':0,'action':'video'}, //上传额外附带的key-value
            multiple:true,//是否允许选择多个文件，默认允许多个
            success:function (data) {
                if(data["error_code"] == 0)
                {
                    $("#" + input_id).val(data.data.id);
                    var node_parent = node.parents('.content_section_body');
                    node_parent.find(".upload-preview").remove();
                    node_parent.prepend('<div class="upload-preview"><video class="mp_video" controls="" style="max-width: 300px;"> <source src="'+data.data.file_path+'" type="video/mp4" style="background-color: #000;"> 你的浏览器不支持 HTML5 video. </video></div>');
                } else {
                    utils.alert(data.error_msg ? data.error_msg : "未知错误");
                }
            },//上传成功的回调函数
            error:function () {
                utils.alert("网络或服务器异常，文件上传失败！");
            }//上传失败的回调函数
        });
    });

    // 提交保存
    $(".btn-submit").on("click", function () {

        var that = this;

        var form = $("#PageForm");

        $(that).prop("disabled",true).text("提交中...");
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serializeArray(),
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/manage/page/config';
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : "未知错误");
                }
                $(that).prop("disabled",false).text("保存");
            },
            error:function () {
                $(that).prop("disabled",false).text("保存");
                utils.alert("网络或服务器异常，请稍后再试");
            }
        });

        return false;
    });
});
