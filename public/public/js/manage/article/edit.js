$(function () {
    var has_tag = []; // 已添加关键词

    // 初始化
    editor = UE.getEditor("content");

    // 初始化编辑模式
    function init()
    {
        editor.ready(function () {
            editor.setHeight(450);
            editor.setContent(article.content || "");
        });
        has_tag = article.tags;
    }
    init();

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
        utils.bindSearchTag({
            select:function (data) {
                if ($.inArray(data.tag, has_tag) >= 0) {
                    utils.toast("该关键词已添加");
                    return false;
                }

                if (has_tag.length >= 5) {
                    utils.toast("一篇文章最多5个关键词");
                    return false;
                }

                // 添加关键词
                has_tag.push(data.tag);
                var _html = '';
                $.each(has_tag,function (i,n) {
                    _html += '<span class="tag_item">'+n+' <i class="fa fa-trash"></i></span>';
                });
                $("#tag_container").html(_html);
                $("#tags").val(has_tag.join('|'));
                utils.toast("已添加：" + data.tag);
            }
        });
    });
    // 删除关键词
    $("#tag_container").on("click", '.fa-trash', function () {
        var tag  = utils.trimAllSpace($(this).parent().text());
        var tags = $("#tags");
        utils.confirm("确认删除关键词："+tag+" 么？", function () {
            var exist_tag = tags.val().split('|');
            var new_tag   = [];
            $.each(exist_tag,function (i,n) {
                if (n != tag) {
                    new_tag.push(n);
                }
            });
            // 重新赋值添加关键词
            has_tag = new_tag;
            var _html = '';
            $.each(has_tag,function (i,n) {
                _html += '<span class="tag_item">'+n+' <i class="fa fa-trash"></i></span>';
            });
            $("#tag_container").html(_html);
            tags.val(has_tag.join('|'));
            utils.toast("已删除：" + tag);
        });
    });

    // 提交新增
    $("#ArticleForm").submit(function () {
        if(utils.isEmpty($("#title").val()))
        {
            $("#title").focus();
            utils.toast('输入文章标题');
            return false;
        }
        if(utils.isEmpty($("#cat_id").val()))
        {
            $("#cat_id").focus();
            utils.toast('请选择文章分类');
            return false;
        }
        if(utils.isEmpty($("#excerpt").val()))
        {
            $("#excerpt").focus();
            utils.toast('请输入文章摘要');
            return false;
        }
        if(utils.isEmpty(editor.getContent()))
        {
            editor.focus();
            utils.toast('请输入文章内容');
            return false;
        }
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#ArticleForm').attr('action'),
            type: 'POST',
            data: $('#ArticleForm').serializeArray(),
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/manage/article/list';
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit').prop('disabled',false).text('提交保存');
            },
            error:function () {
                $('.btn-submit').prop('disabled',false).text('提交保存');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });

        return false;
    });

});
