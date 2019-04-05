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
                    // 0级别和1级别没有菜单链接 直接super
                    if(menu[i].level == 1 || menu[i].level == 0)
                    {
                        post_ids.push(menu[i].id);
                        post_permissions.push('super');
                    }
                    if(menu[i].level == 2)
                    {
                        // 需要数据权限的必须勾选
                        if(menu[i].is_permissions == 1) {
                            var radio_name  = 'radio_' + menu[i].id;
                            var permissions = $("input:radio[name='"+radio_name+"']:checked").val();
                            if(!permissions)
                            {
                                var radio_node = $("input:radio[name='"+radio_name+"']");
                                var offset_top = radio_node.offset().top - 30;// 元素距离window顶部的偏移量
                                $('html,body').animate({scrollTop:offset_top},300);
                                radio_node.shake(3,20,1000);
                                utils.toast('请选择【'+menu[i].name+'】的数据权限');
                                return false;
                            }
                            post_ids.push(menu[i].id);
                            post_permissions.push(permissions);
                        }else {
                            // 不需要数据权限的直接super
                            post_ids.push(menu[i].id);
                            post_permissions.push('super');
                        }
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

        // 选择的可操作字段
        var columns = [];
        var show_columns = $(".check_item_columns");
        $.each(show_columns, function (i,n) {
            if ($(n).prop("checked")) {
                columns.push($(n).val());
            }
        });

        var post = {
            "name" : $('#name').val(),
            "sort" : $('#sort').val(),
            "remark" : $('#remark').val(),
            "ids" : post_ids,
            "permissions" : post_permissions,
            "show_columns" : columns
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
                if(treeNode.level == 3)
                {
                    zTree.checkNode(treeNode, !treeNode.checked, false);
                    // console.log(treeNode);
                    $('#radio_' + treeNode.id).prop('checked',true);
                }else {
                    zTree.checkNode(treeNode, !treeNode.checked, true);
                }

                // 选择可操作字段显示隐藏
                var change_nodes = zTree.getChangeCheckedNodes();
                $.each(change_nodes, function (i,node) {
                    if (node.is_column) {
                        if (node.checked) {
                            $("#show_columns" + node.id).show();
                        } else {
                            $("#show_columns" + node.id).hide();
                        }
                    }
                });

            },
            onCheck:function (event, treeId, treeNode) {

                // 选择可操作字段显示隐藏
                var change_nodes = zTree.getChangeCheckedNodes();
                $.each(change_nodes, function (i,node) {
                    if (node.is_column) {
                        if (node.checked) {
                            $("#show_columns" + node.id).show();
                        } else {
                            $("#show_columns" + node.id).hide();
                        }
                    }
                });

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
        // 有备注信息，则将备注信息显示出来
        if(treeNode.remark)
        {
            aObj.find('.node_name').html(treeNode.name + '<em class="node_remark">['+treeNode.remark+']</em>');
        }
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
        }
    }

    initPermissions();

    /**
     * 初始化数据权限勾选逻辑
     */
    function initPermissions() {
        var all_nodes = zTree.transformToArray(zTree.getNodes());
        for(var i=0;i<all_nodes.length;i++)
        {
            if(utils.isNumber(all_nodes[i].id))
            {
                if(all_nodes[i].level == 2 && all_nodes[i].is_required == 0)
                {
                    setRadioStatus(all_nodes[i].id,all_nodes[i].permissions);
                }
            }
        }
    }

    /**
     * radio禁用范围
     * @param node_id
     * @param up_permissions
     */
    function setRadioStatus(node_id,up_permissions)
    {
        var super_id   = '#radio_permissions_super_' + node_id;
        var leader_id  = '#radio_permissions_leader_' + node_id;
        var staff_id   = '#radio_permissions_staff_' + node_id;
        switch (up_permissions)
        {
            case 'super':
                break;
            case 'leader':
                $(super_id).prop('disabled',true).parent('li').on('click',function () {
                    return false;
                }).find('span').css({'cursor':'not-allowed','color':'#bbb'});
                break;
            case 'staff':
                $(super_id).prop('disabled',true).parent('li').on('click',function () {
                    return false;
                }).find('span').css({'cursor':'not-allowed','color':'#bbb'});
                $(leader_id).prop('disabled',true).parent('li').on('click',function () {
                    return false;
                }).find('span').css({'cursor':'not-allowed','color':'#bbb'});
                break;
            case 'guest':
                $(super_id).prop('disabled',true).parent('li').on('click',function () {
                    return false;
                }).find('span').css({'cursor':'not-allowed','color':'#bbb'});
                $(leader_id).prop('disabled',true).parent('li').on('click',function () {
                    return false;
                }).find('span').css({'cursor':'not-allowed','color':'#bbb'});
                $(staff_id).prop('disabled',true).parent('li').on('click',function () {
                    return false;
                }).find('span').css({'cursor':'not-allowed','color':'#bbb'});
                break;
        }
    }

    /**
     * 渲染当前角色拥有的字段权限:将当前用户所具有的菜单的可操作字段数据渲染成html
     */
    function initColumns() {
        var html = '';
        $.each(columns, function (i,column) {
            html += "<div class=\"col-md-12 show_columns\" id=\"show_columns"+column.menu_id+"\">" +
                    "   <h4><i class=\"fa fa-check-square-o\"></i> "+column.name+" <span>["+column.url+"下可操作字段]</span></h4>" +
                    "   <div class=\"col-sm-12\">" +
                    "       <div class=\"checkbox\">" +
                    "           <label class=\"control-label text-maroon\">"  +
                    "               <input type=\"checkbox\" class=\"check_all_columns\"> 全选" +
                    "           </label>" +
                    "       </div>" +
                    "   </div>";

            $.each(column.columns,function (j,item) {

                // 必须 + 已选中处理
                if (item.default) {
                    html += "<div class=\"columns_items\">" +
                            "   <div class=\"col-xs-2\">" +
                            "       <div class=\"checkbox\">" +
                            "           <label class=\"control-label\">" +
                            "               <input type=\"checkbox\" checked=\"checked\" disabled=\"disabled\" class=\"check_item_columns\"  value=\""+column.menu_id+"."+item.columns+"\"> " + item.name +
                            "           </label>" +
                            "       </div>" +
                            "   </div>"+
                            "</div>";
                } else {
                    html += "<div class=\"columns_items\">" +
                            "   <div class=\"col-xs-2\">" +
                            "       <div class=\"checkbox\">" +
                            "           <label class=\"control-label\">" +
                            "               <input type=\"checkbox\" class=\"check_item_columns\"  name=\"Columns["+column.menu_id+"."+item.columns+"]\"> " + item.name +
                            "           </label>" +
                            "       </div>" +
                            "   </div>"+
                            "</div>";
                }

            });

            html += "</div>";
        });
        $(".column_box").append(html);
    }
    initColumns();

    // 全选/反选操作
    $(".column_box").on("change", ".check_all_columns",function () {
        if( $(this).prop('checked')) {
            $(this).parents(".show_columns").find("input").prop("checked",true);
        } else {
            $(this).parents(".show_columns").find("input").not(":disabled").prop("checked",false);
        }
    });
});
