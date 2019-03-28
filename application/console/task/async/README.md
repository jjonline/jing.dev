## 说明

* 本目录下的类必须继承`app\console\swoole\framework\AsyncTaskAbstract`并实现抽象静态方法`run`

* `init`方法进行一些初始化、`run`方法是被异步执行的入口、`finish`方法为run方法执行完毕之后自动执行的一些收尾逻辑方法