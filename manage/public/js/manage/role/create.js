/**
 * 初始化默认菜单权限选择
 */
var default_permissions = [];
$(function () {
    $('#roleAdd').submit(function () {
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
            url: $('#roleAdd').attr('action'),
            type: 'POST',
            data: $('#roleAdd').serializeArray(),
            success: function (data) {
                if(data.error_code == 0){
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
        var permissions = [];
        $.each(menu,function (i,n) {
            if(n.is_required == 1)
            {
                var node = {};
                node.id   = n.id;
                node.name = n.name;
                node.url  = n._url;
                permissions.push(node);
                if(!utils.isEmpty(n.children))
                {
                    child(n.children);
                }
            }
        });
        function child(child_node)
        {
            $.each(child_node,function (i,n) {
                if(n.is_required == 1)
                {
                    var node = {};
                    node.id   = n.id;
                    node.name = n.name;
                    node.url  = n._url;
                    permissions.push(node);
                    if(!utils.isEmpty(n.children))
                    {
                        child(n.children);
                    }
                }
            });
        }
        default_permissions = permissions;
        initPermissionsDom(default_permissions);
    };

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
            permissions.push(node);
        });
        // console.log(default_permissions.length);
        initPermissionsDom(permissions);
    };

    /**
     * 渲染初始化菜单的权限选择dom
     */
    function initPermissionsDom(checked_permissions) {
        var dom = '<ul class="menu_permissions">';
        //console.log(checked_permissions);
        $.each(checked_permissions,function (i,n) {
            dom += '<li class="form-group">' +
                '<input type="hidden" name="Role_ID[]" value="'+ n.id +'">' +
                '<h3><i class="fa fa-child"></i> ' + n.name + '</h3>';
            dom += '<p><label class="radio-inline">' +
                   '   <input type="radio" value="super" name="permissions['+n.id+']"> 超级管理员' +
                   '</label>';
            dom += '<label class="radio-inline">' +
                '   <input type="radio" value="leader" name="permissions['+n.id+']"> 部门领导' +
                '</label>';
            dom += '<label class="radio-inline">' +
                '   <input type="radio" value="staff" name="permissions['+n.id+']"> 部门职员' +
                '</label>';
            dom += '<label class="radio-inline">' +
                '   <input type="radio" value="guest" name="permissions['+n.id+']" checked> 游客' +
                '</label>';
            dom += '</p></li>';
        });
        dom += '</ul>';
        $('#permissions').empty().html(dom);
    };

});