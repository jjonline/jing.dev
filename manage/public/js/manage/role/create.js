/**
 * 初始化默认菜单权限选择
 */
var zTree;
$(function () {

    $('#roleAdd').submit(function () {
        if (utils.isEmpty($('#name').val())) {
            $('#name').focus();
            utils.toast('请输入角色名称');
            return false;
        }
        var menu = zTree.transformToArray(zTree.getNodes());
        // 获取选择的菜单数据
        var post_ids = [];
        var post_permissions = [];
        for(var i=0;i<menu.length;i++)
        {
            // 处理必选菜单
            if(menu[i].is_required == 1 && utils.isNumber(menu[i].id))
            {
                post_ids.push(menu[i].id);
                post_permissions.push('super');
            }else {
                // 读取已选中item
                var check_status = menu[i].getCheckStatus();
                if(utils.isNumber(menu[i].id) && check_status.checked)
                {
                    // 1级别没有菜单链接 直接super
                    if(menu[i].level == 1)
                    {
                        post_ids.push(menu[i].id);
                        post_permissions.push('super');
                    }
                    if(menu[i].level == 2)
                    {
                        var radio_name  = 'radio_' + menu[i].id;
                        var permissions = $("input:radio[name='"+radio_name+"']:checked").val();
                        if(!permissions)
                        {
                            utils.toast('请选择【'+menu[i].name+'】的数据权限');
                            return false;
                        }
                        post_ids.push(menu[i].id);
                        post_permissions.push(permissions);
                    }
                }

            }
        }
        // 检测是否有选择
        if(utils.isEmpty(post_ids) || utils.isEmpty(post_permissions))
        {
            utils.toast('请选择角色菜单和数据权限范围');
            return false;
        }
        var post = {
            'name' : $('#name').val(),
            'sort' : $('#sort').val(),
            'remark' : $('#remark').val(),
            'ids' : post_ids,
            'permissions' : post_permissions
        };
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#roleAdd').attr('action'),
            type: 'POST',
            data: post,
            success: function (data) {
                if(data.error_code == 0)
                {
                    utils.alert(data.error_msg,function () {
                        location.href = '/manage/role/list';
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit').prop('disabled',false).text('新增');
            },
            error:function () {
                $('.btn-submit').prop('disabled',false).text('新增');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
        return false;
    });

    var setting = {
        view: {
            showLine: false,
            showIcon: false,
            dblClickExpand: false,
            addDiyDom:addDiyDom
        },
        check: {enable: true, nocheck: false, chkboxType: {"Y": "ps", "N": "ps"}},
        callback: {
            onClick: function (e, treeId, treeNode,clickFlag) {
                zTree.checkNode(treeNode, !treeNode.checked, true);
                if(treeNode.level == 3)
                {
                    // console.log(treeNode);
                    $('#radio_' + treeNode.id).prop('checked',true);
                }
            },
            onCheck:function (event, treeId, treeNode) {
                //console.log(treeNode);
            }
        }
    };
    zTree = $.fn.zTree.init($("#menu_tree"), setting, menu);

    /**
     * 自定义tree
     * @param treeId
     * @param treeNode
     */
    function addDiyDom(treeId, treeNode) {
        var IDMark_A = '_a';
        var aObj = $("#" + treeNode.tId + IDMark_A);
        if (treeNode.level == 3)
        {
            var radio_name = 'radio_' + treeNode.getParentNode().id;
            var editStr = "<input type='radio' class='radioBtn' id='radio_" +treeNode.id+ "' name='"+radio_name+"' value='"+treeNode.value+"'></input>";
            aObj.before(editStr);

            // bind event
            var radio_input = "#radio_"+treeNode.id;
            $('#menu_tree').on("click", radio_input ,function() {
                // 点击radio元素本身
                aObj.click();
            });
            // aObj.on('click',function () {
            //     // 点击radio元素后方的文字
            //     $(radio_input).attr('checked',true);
            // });
        }
    }

});