## common模块下controller目录说明

`common`公共模块下的`controller`目录为所有业务控制器继承的基类。

**1、BasicController.php**

所有控制器的顶级控制器基类

1、实现一些非常基础的所有控制器下的操作均可使用的方法以及控制器下直接使用的记录操作用户动作日志的方法，例如：自定义json输出方法`asJson`、按约定输出json字符串方法`renderJson`、

2、实现一些拦截逻辑

3、所有开发控制器类不要直接继承该类，请继承`BaseController`

**2、BaseAuthController.php**

基础拦截验证控制器基类，本类继承`BasicController`

1、实现控制器、操作的权限效验和拦截提示

2、实现权限有关的公用方法

3、所有开发控制器类不要直接继承该类，请继承`BaseController`
