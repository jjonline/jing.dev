$(function () {

    // code
    prettyPrint();

    /**
     * 上传裁剪组件demo
     */
    //上传销项裁剪插件
    utils.bindCutImageUploader("cut_upload",{
        rate:"4/3",// 设置裁剪图的比例
        title:"图片裁剪demo", // 设置裁剪浮层的标题
        width:400,//指定裁剪后图片宽度
        height:300,//指定裁剪后图片高度
        extraData:{'file_type':'image','is_safe':0},//额外塞入上传的post数据体重的filed-value对象数组
        success:function (data) {
            if(data["error_code"] == 0)
            {
                utils.alert(data.data.file_path);
                console.log(data);
            }else{
                utils.alert(data.error_msg ? data.error_msg : "未知错误");
            }
        },//裁剪并上传成功后的回调函数，data参数为服务器返回的json对象
        error:function () {
            utils.alert("网络或服务器异常，文件上传失败！");
        }//裁剪或上传失败的回调函数
    });

    // 带上传进度条的文件上传
    utils.bindAjaxUploader("file_upload",{
        // url:'',//上传文件后端Url，留空则为/manage/upload/upload?origin=ajax
        allow_extension: null,//null不限制、需限制时使用数组 ['jpg','jpeg']
        extraData: {'is_safe':0}, //上传额外附带的key-value
        multiple:true,//是否允许选择多个文件，默认允许多个
        success:function (data) {
            if(data["error_code"] == 0)
            {
                utils.alert(data.data.file_path);
                console.log(data);
            }else{
                utils.alert(data.error_msg ? data.error_msg : "未知错误");
            }
        },//上传成功的回调函数
        error:function () {
            utils.alert("网络或服务器异常，文件上传失败！");
        }//上传失败的回调函数
    });
});
