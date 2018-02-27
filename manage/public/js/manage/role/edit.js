/**
 * 初始化默认菜单权限选择
 */
var default_permissions = [];
var edit_permissions = [];
$(function () {
    $('#roleEdit').submit(function () {
        if (utils.isEmpty($('#name').val())) {
            $('#name').focus();
            utils.toast('请输入角色名称');
            return false;
        }
        // 检查选择的菜单
        if($("input[name='Role_ID[]']").length <= 0)
        {
            utils.toast('请选择角色菜单和菜单权限');
            return false;
        }
        $('.btn-submit').prop('disabled',true).text('提交中...');
        $.ajax({
            url: $('#roleEdit').attr('action'),
            type: 'POST',
            data: $('#roleEdit').serializeArray(),
            success: function (data) {
                if(data.error_code == 0){
                    utils.alert(data.error_msg,function () {
                        location.href = '/manage/role/list';
                    });
                }else{
                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                }
                $('.btn-submit').prop('disabled',false).text('提交');
            },
            error:function () {
                $('.btn-submit').prop('disabled',false).text('提交');
                utils.alert('网络或服务器异常，请稍后再试');
            }
        });
        return false;
    });

    var zTree;
    var setting = {
        view: {showLine: false, showIcon: false, dblClickExpand: false},
        check: {enable: true, nocheck: false, chkboxType: {"Y": "ps", "N": "ps"}},
        callback: {
            onClick: function (e, treeId, treeNode,clickFlag) {
                zTree.checkNode(treeNode, !treeNode.checked, true);
                collectCheckedMenu();
            },
            onCheck:function (event, treeId, treeNode) {
                collectCheckedMenu();
            }
        }
    };
    zTree = $.fn.zTree.init($("#menu_tree"), setting, menu);
    initDefaultMenuPermissions();

    /**
     * 初始化菜单权限
     */
    function initDefaultMenuPermissions()
    {
        var zTreeObj     = $.fn.zTree.getZTreeObj("menu_tree");
        var permissions  = [];
        var permissions1 = [];
        var _menu        = zTreeObj.getNodes();
        var edit_menu;
        $.each(_menu,function (i,n) {
            if(n.is_required == 1)
            {
                edit_menu = isMenuEdit(n.id);
                var node = {};
                node.id   = n.id;
                node.name = n.name;
                node.url  = n._url;
                node.permissions  = n.permissions;
                node._permissions = edit_menu.permissions;
                permissions.push(node);
                if(!utils.isEmpty(n.children))
                {
                    child(n.children);
                }
            }else{
                // 被编辑的菜单
                edit_menu = isMenuEdit(n.id);
                if(edit_menu)
                {
                    zTreeObj.checkNode(n, true, true);//选中node
                    var _node = {};
                    _node.id   = n.id;
                    _node.name = n.name;
                    _node.url  = n._url;
                    _node.permissions  = n.permissions;
                    _node._permissions = edit_menu.permissions;
                    permissions1.push(_node);
                    if(!utils.isEmpty(n.children))
                    {
                        child(n.children);
                    }
                }
            }
        });
        function child(child_node)
        {
            var edit_menu;
            $.each(child_node,function (i,n) {
                if(n.is_required == 1)
                {
                    edit_menu = isMenuEdit(n.id);
                    var node = {};
                    node.id   = n.id;
                    node.name = n.name;
                    node.url  = n._url;
                    node.permissions  = n.permissions;
                    node._permissions = edit_menu.permissions;
                    permissions.push(node);
                    if(!utils.isEmpty(n.children))
                    {
                        child(n.children);
                    }
                }else{
                    // 被编辑的菜单
                    edit_menu = isMenuEdit(n.id);
                    if(edit_menu)
                    {
                        zTreeObj.checkNode(n, true, true);//选中node
                        var _node = {};
                        _node.id   = n.id;
                        _node.name = n.name;
                        _node.url  = n._url;
                        _node.permissions  = n.permissions;
                        _node._permissions = edit_menu.permissions;
                        permissions1.push(_node);
                        if(!utils.isEmpty(n.children))
                        {
                            child(n.children);
                        }
                    }
                }
            });
        }
        default_permissions = permissions;
        edit_permissions    = permissions.concat(permissions1);
        initPermissionsDom(edit_permissions);
    };

    /**
     * 检查是否编辑的菜单
     * @param menu_id
     */
    function isMenuEdit(menu_id)
    {
        for(var n in role_menu)
        {
            if(role_menu[n].id == menu_id)
            {
                return role_menu[n];
            }
        }
        return false;
    }

    /**
     * 收集选中节点
     */
    function collectCheckedMenu()
    {
        var permissions = default_permissions.concat();
        // console.log(default_permissions.length);
        var obj  = $.fn.zTree.getZTreeObj("menu_tree");
        var json = obj.getCheckedNodes(true);
        $.each(json,function (i,n) {
            var node = {};
            node.id   = n.id;
            node.name = n.name;
            node.url  = n._url;
            node.permissions  = n.permissions;
            node._permissions = null;
            permissions.push(node);
        });
        // console.log(default_permissions);
        initPermissionsDom(permissions);
    };

    /**
     * 渲染初始化菜单的权限选择dom
     */
    function initPermissionsDom(checked_permissions) {
        var dom = '<ul class="menu_permissions">';
        //console.log(checked_permissions);
        var is_super,is_leader,is_staff,is_guest;
        $.each(checked_permissions,function (i,n) {
            dom += '<li class="form-group">' +
                '<input type="hidden" name="Role_ID[]" value="'+ n.id +'">' +
                '<h3><i class="fa fa-child"></i> ' + n.name + '</h3>';
            if(!utils.isEmpty(n._permissions))
            {
                // 初始化待编辑的菜单权限勾选状态
                is_super  = n._permissions == 'super' ? ' checked="checked"' : '';
                is_leader = n._permissions == 'leader' ? ' checked="checked"' : '';
                is_staff  = n._permissions == 'staff' ? ' checked="checked"' : '';
                is_guest  = n._permissions == 'guest' || (utils.isEmpty(is_super) && utils.isEmpty(is_leader) && utils.isEmpty(is_staff)) ? ' checked="checked"' : '';
            }else{
                // 新增或删除后菜单权限级别渲染
                is_super  = n.permissions == 'super' ? ' checked="checked"' : '';
                is_leader = n.permissions == 'leader' ? ' checked="checked"' : '';
                is_staff  = n.permissions == 'staff' ? ' checked="checked"' : '';
                is_guest  = n.permissions == 'guest' || (utils.isEmpty(is_super) && utils.isEmpty(is_leader) && utils.isEmpty(is_staff)) ? ' checked="checked"' : '';
            }
            switch(n.permissions)
            {
                case 'super' :
                    dom += '<p><label class="radio-inline">' +
                        '   <input type="radio" value="super" name="permissions['+n.id+']"'+is_super+'> 超级管理员' +
                        '</label>';
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="leader" name="permissions['+n.id+']"'+is_leader+'> 部门领导' +
                        '</label>';
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="staff" name="permissions['+n.id+']"'+is_staff+'> 部门职员' +
                        '</label>';
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="guest" name="permissions['+n.id+']"'+is_guest+'> 游客' +
                        '</label>';
                    break;
                case 'leader' :
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="leader" name="permissions['+n.id+']"'+is_leader+'> 部门领导' +
                        '</label>';
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="staff" name="permissions['+n.id+']"'+is_staff+'> 部门职员' +
                        '</label>';
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="guest" name="permissions['+n.id+']"'+is_guest+'> 游客' +
                        '</label>';
                    break;
                case 'staff' :
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="staff" name="permissions['+n.id+']"'+is_staff+'> 部门职员' +
                        '</label>';
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="guest" name="permissions['+n.id+']"'+is_guest+'> 游客' +
                        '</label>';
                    break;
                case 'guest' :
                    dom += '<label class="radio-inline">' +
                        '   <input type="radio" value="guest" name="permissions['+n.id+']" checked> 游客' +
                        '</label>';
                    break;
            }
            dom += '</p></li>';
        });
        dom += '</ul>';
        $('#permissions').empty().html(dom);
    };

});