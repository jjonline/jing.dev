## 基础组件系统

基于ThinkPHP5.1

环境要求：

> PHP7.2+ (启用PHP-Redis、OpenSSL、Curl以及MbString扩展)
> 
> MySQL5.7+
> 
> Redis缓存

本地开发地址：https://component.jing.dev/manage

### 开发说明

* 1、使用`environments`目录保存各种不同的开发环境配置
* 2、使用`php think migration`管理数据表结构版本，在`database`目录下
* 3、使用`php think init --env=dev`生成开发、生产等环境配置文件

### 初始化步骤

* 1、composer安装，根目录下执行：`composer update`命令
* 2、创建数据库，utf8mb4编码
* 3、修改配置文件：修改配置`environments`目录下`dev`目录中的各个配置项目，其中`dev`是本地开发的各种配置，`prod`是生产发布下的各种配置，`test`是测试环境下的各种配置。对于本地开发设置好`dev`下的各项配置参数即可。
* 4、执行配置文件初始化，根目录下执行：`php think init --env=dev`命令初始化开发环境的配置。
* 5、执行数据表迁移命令，根目录下执行：`php think migrate:run`命令，生成各种数据表。
* 6、初始化各种表的基本数据，根目录下执行：`php think seed:run`命令，为各种数据表填充必要的数据。
* 7、web根目录指向`public`目录，做好必要的rewrite，登录入口为`manage`模块，seed的唯一默认管理员`jing`初始密码`12345`

### 参考nginx配置

* `*.jing.dev`域名已经做了泛解析，均解析至:`127.0.0.1`，各位开发者可以使用

```
server {
    listen                 80;
    listen                 443 ssl http2;
    server_name            *.jing.dev;
    charset                utf-8;
    client_max_body_size   50M;
    #autoindex             on;
    #autoindex_exact_size  off;
    #autoindex_localtime   off;

    # if ($server_port !~ 443)
    # {
    #    rewrite ^(/.*)$ https://$host$1 permanent;
    # }

    ssl_certificate           /Users/jingjing/.acme.sh/*.jing.dev/fullchain.cer;
    ssl_certificate_key       /Users/jingjing/.acme.sh/*.jing.dev/*.jing.dev.key;
    ssl_protocols             TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers               ECDHE-RSA-AES128-GCM-SHA256:HIGH:!aNULL:!MD5:!RC4:!DHE;
    ssl_prefer_server_ciphers on;
    ssl_session_cache         shared:SSL:10m;
    ssl_session_timeout       10m;

    # preg domain name and set web root dir
    if ($host ~* ^([^\.]+)\.jing.dev$)
    {
        set $domain_name $1;
    }
    root    /Users/jingjing/Developer/$domain_name/public;
    index   index.html index.htm default.html default.htm index.php default.php;

    # default rewrite
    location / {
        if (!-e $request_filename)
        {
            rewrite ^/(.*)$ /index.php?s=$1 last;
            break;
        }
    }

    # handle php
    location ~ ^(.+\.php)(.*)$ {
        root                     /Users/jingjing/Developer/$domain_name/public;
        fastcgi_pass             127.0.0.1:9000;
        fastcgi_read_timeout     36000;
        fastcgi_index            index.php;
        fastcgi_split_path_info  ^(.+\.php)(.*)$;
        fastcgi_param            PATH_INFO $fastcgi_path_info;
        fastcgi_param            SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include                  fastcgi_params;
    }

    # deny hide file
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```


### Git分支说明

* `master`分支是最新的已合并的分支
* `framework`分支则是纯粹的组件系统，只有最基础的功能和表结构
* 其他分支则一些特性分支或功能调试分支

### 菜单、角色、部门和用户之间的关系

````
角色与菜单：一对多，即一个角色可以拥有多个菜单
用户与角色：一对一，即一个用户只能拥有1个角色
用户与部门：一对一，即一个用户只能属于1个部门
````

> 当前组件系统一个用户只能是1个角色1个部门的设定支撑中小型应用已无问题，不计划支持一对多。

#### 一、菜单

菜单即对应于每一个需要能控制访问与否、基于部门层级的数据范围、字段限定的可访问操作单元。简单理解就是可点击的超链接以及该超链接的一些设定属性。

系统层面来看，菜单对应于控制器下的每一个需要做权限限定的操作，所以每增加一个控制器就需要对应增加菜单数据。

每一个菜单中包含超链接、icon、排序、导航文字标题、是否进行数据范围限定、是否有字段限定、有字段限定情况下的该菜单对应的所有字段列表信息 等。其中数据范围是基于部门层级而言，见角色中相关数据范围标记说明。

> 一个菜单包含菜单本身的一些属性信息例如菜单名称、菜单url、菜单图标icon、菜单层级（一级菜单、二级菜单\三级菜单，菜单最多3级，且仅显示一二级菜单，第三级菜单就已经到了各操作操作按钮）；还包含了菜单内是有具备基于部门的数据范围限定，即某用户属于A部门，那么该用户是能查看A部门的数据呢还是只能查看属于自己的数据；还包含了该菜单是否需要进行菜单内列表页面的显示字段的限定，若有限定则在新建菜单时需要一并穷举该菜单列表中的所有字段信息，用于设置角色时的待选--即每个角色从菜单设定好的这些字段中挑选该角色能显示的字段。

为了便于描述，上述菜单的属性、数据范围和字段限制的设置统称`菜单权限`设置。

#### 二、部门

部门是一个抽象概念，可以与现实中的部门对应，也可以抽象理解。部门实现了用户的隔离，即用户可以属于不同的部门，然后基于部门用户可以实现数据的隔离。部门存在层级，当前系统最多支持5级部门。

若某个菜单需要基于部门做数据范围的限定，即不同部门的用户能否跨部门查看数据的限定是需要在菜单中先设置该菜单需要做数据范围限定（菜单中开启数据范围限定），然后在分配角色时具体指定该角色的数据范围。

> 请规划好部门层级设定，整站最好只有1个一级部门，这样做数据范围限定时更方便，程序实现也相对容易一些。若多个顶级部门平级，可以考虑抽象一个虚拟的顶级部门，所有顶级部门都归属于该抽象的虚拟一级部门。

#### 三、用户

这里特指后台系统用户即`user`表，无需过多解释。

> 前台用户是另外一张表`customer`，前台用户不在此处说明。

* 1、用户字段中有标记是否为部门领导的字段`is_leader`，标记是否为所在部门的领导，该字段与数据范围没有关系，数据范围在角色菜单中指定。
* 2、用户字段中有标记是否为根用户的特性标记字段`is_root`，标记该用户是否为根用户，若是则表示该用户不受角色权限限制，永远具备所有菜单的最高权限以及能查看所有字段（若有字段限制）。只有根用户才能创建根用户，初始数据来说只有用户id为1的用户才能新建根用户。

> 请慎重创建根用户，根用户整站最好只有1个，创建多个之前请务必知晓您在做什么！！！

#### 四、角色

角色是一群菜单权限的集合，即该1个角色包含了多个菜单以及每一个菜单的数据范围、字段限定等属性。是用户与菜单之间的桥梁，一个用户属于某个角色，该用户就拥有了这个角色集合内的菜单权限。

角色中菜单下的数据范围数据标记说明

* `super`  - 超管，所有数据，即不受限的查看所有数据，跟用户所属部门没有任何关系。
* `leader` - 领导，部门及子部门数据，这里还需考虑跨部门存在个人数据的情况。
* `staff`  - 职员，仅个人数据，可能有跨部门存在个人数据的情况。
* `guest`  - 游客，无数据权限，可能就是为了演示功能。

---

新建角色时，拟被新建的角色待选的菜单和该菜单可能存在的数据范围限定、字段范围限定均是基于当前登录用户所属角色下的菜单权限范围来设定，譬如当前登录用户不能操作A菜单，那么拟被新建的角色一定是不具备A菜单操作权限的，同理当前登录用户具备B菜单的`leader`数据范围权限，那么拟被新建的角色是不可能具备`super`数据范围权限的，只能是`super`及其以下的数据范围权限。

---

> 角色本身做层级限定比较复杂，而且需要自定义角色所属菜单元数据，实际使用起来会非常复杂，固不计划实现角色的层级。请规划好每一个角色的本身识别性，做到能通过角色名称就大概知道是个什么样的菜单权限，譬如通过角色名称与存在上下级关系的职务名称挂钩，通过该挂钩的角色名称区分角色本身的权限高低，也便于为用户分配角色时快速识别。
