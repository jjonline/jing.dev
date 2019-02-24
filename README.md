## 基础组件系统

基于ThinkPHP5.1

环境要求：

> PHP7.1+ (启用PHP-Redis、OpenSSL、Curl以及MbString扩展)
> 
> MySQL5.6
> 
> Redis缓存

本地开发地址：http://component.xnn.fun/

### 开发说明

* 1、使用`environments`目录保存各种不同的开发环境配置
* 2、使用`php think migration`管理数据表结构版本，在`database`目录下
* 3、使用`php think init --env=dev`生成开发、生成等环境配置文件

### 初始化步骤

* 1、composer安装，根目录下执行：`composer update`命令
* 2、创建数据库，utf8mb4编码
* 3、修改配置文件：修改配置`environments`目录下`dev`目录中的各个配置项目，其中`dev`是本地开发的各种配置，`prod`是生产发布下的各种配置，`test`是测试环境下的各种配置。对于本地开发设置好`dev`下的各项配置参数即可。
* 4、执行配置文件初始化，根目录下执行：`php think init --env=dev`命令初始化开发环境的配置。
* 5、执行数据表迁移命令，根目录下执行：`php think migrate:run`命令，生成各种数据表。
* 6、初始化各种表的基本数据，根目录下执行：`php think seed:run`命令，为各种数据表填充必要的数据。


