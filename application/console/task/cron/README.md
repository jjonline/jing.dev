## 说明

* 本目录下的类必须继承`app\console\swoole\framework\CronTaskAbstract`并实现抽象静态方法`run`

* 参照`DemoTask`设定任务名称、定时规则和被执行的逻辑即可