/**
 * 常用Js帮助函数
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2018-01-05 13:59:46
 * @version $Id$
 */


// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
Date.prototype.Format = function (fmt)
{
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    {
        if (new RegExp("(" + k + ")").test(fmt))
        {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
};
/**
 * utits帮助对象函数
 * @type {Object}
 */
var utils = {
    /**
     * 检查参数是否为空，是空返回true
     */
    isEmpty: function (input) {
        if (!input) {
            return true;
        }

        var reg = /\S+/ig;
        return !reg.test(input);
    },
    /**
     * 检查是否数组
     */
    isNumber : function (input) {
        return !utils.isEmpty(input) && !isNaN(input);
    },
    /**
     * 获取表单验证令牌值，同名函数getToken
     */
    getCsrf: function () {
        return $("input[name='__token__']").val();
    },
    getToken: function () {
        return utils.getCsrf();
    },
    /**
     * bootbox提示方法封装的提示层
     * @param  message  提示消息文字
     * @param  callback 点击确认之后的回调函数
     * @param  title    可选的自定义提示框标题
     * @param  OkbtnClass    可选的自定义确认按钮的class名称
     */
    alert: function (message, callback, title, OkbtnClass) {
        title = title || "提示";
        OkbtnClass = OkbtnClass || 'btn-info';
        bootbox.dialog({
            message: message,
            title: title,
            onEscape: callback || true,
            backdrop: true,
            buttons: {
                success: {
                    label: '确定',
                    className: "btn " + OkbtnClass,
                    callback: callback
                }
            }
        });
    },
    /**
     * layer封装的toast提示层方法，定时自动消失
     * @param message
     * @param time
     * @param callback
     */
    toast:function (message,time,callback) {
        time = time || 3000;
        layer.msg(message,{
            time:time
        });
        setTimeout(function () {
            callback && callback();
        },time);
    },
    /**
     * 操作确认
     * @param message 提示语
     * @param okCallBack 确认回调函数
     * @param callCallBack 取消回调函数
     */
    confirm : function (message,okCallBack,callCallBack) {
        bootbox.dialog({
            message: message,
            title: '操作确认',
            onEscape: true,
            backdrop: true,
            buttons: {
                success: {
                    label: '确定',
                    className: "btn btn-info",
                    callback: okCallBack || true
                },
                Cancel : {
                    label: '取消',
                    className: "btn btn-default",
                    callback: callCallBack || true
                }
            }
        });
    },

    /**
     * ajax提交之前确认方法
     * @param message          删除确认浮层提示消息
     * @param request_url      删除提交的后端request网址，仅post
     * @param data             删除确认提交的数据对象
     * @param successCallBack  确认并执行之后的回调函数
     * @param toastTime        是否启用toast显示结果及显示结果多少毫秒之后执行回调函数，单位：毫秒
     * @param callCallBack     取消不执行后的回调函数
     */
    ajaxConfirm: function (message,request_url,data,successCallBack,toastTime,callCallBack) {
        toastTime = toastTime || 3000;
        bootbox.dialog({
            message: message,
            title: '操作确认',
            onEscape: true,
            backdrop: true,
            buttons: {
                success: {
                    label: '确定',
                    className: "btn btn-info",
                    callback: function () {
                        utils.showLoading('提交中，请稍后...');
                        $.ajax({
                            url: request_url,
                            type: 'POST',
                            data: data,
                            success: function (data) {
                                utils.hideLoading();
                                if(data.error_code == 0){
                                    // toast方式提示
                                    utils.toast(data.error_msg ? data.error_msg : '操作成功',toastTime,successCallBack);
                                }else{
                                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                                }
                            },
                            error:function () {
                                utils.hideLoading();
                                utils.alert('网络或服务器异常，请稍后再试');
                            }
                        });
                    }
                },
                Cancel : {
                    label: '取消',
                    className: "btn btn-default",
                    callback: callCallBack || true
                }
            }
        });
    },
    /**
     * 显示自动消失的提示框
     * @param  str  提示文字
     * @param  time    提示框显示后多久消失，单位毫秒，默认3000毫秒亦既3秒
     */
    shortNotice:function(str,time){
        utils.showLoading(str);
        this._timer=setTimeout(function(){
            utils.hideLoading();
        },time || 3000);
    },
    /**
     * 生成随机数字
     * @param min 随机数字最小值
     * @param max 随机数字最大值
     */
    randNumber:function(min,max) {
        var params = {
            min: min || 0,
            max: max || 9999999
        };
        var Range = params.max - params.min;
        var Rand  = Math.random();
        return (params.min + Math.round(Rand * Range));
    },
    /**
     * 生成随机字符串
     * @param length 随机字符串的长度
     * @returns {string}
     */
    randString:function(length) {
        var lens = length || 16;
        var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
        var maxPos = chars.length;
        var strings = '';
        for (i = 0; i < lens; i++) {
            strings += chars.charAt(Math.floor(Math.random() * maxPos));
        }
        return strings;
    },
    /**
     * 去除字符串中的所有空白
     * @param strings
     */
    trimAllSpace:function(strings) {
        if (!strings) {
            return false;
        }
        strings.replace(/(^\s+)|(\s+$)/g,"");
        return strings.replace(/\s/g,"");
    },
    /**
     * 检查是否合法邮箱格式
     * @param match
     * @returns bool
     */
    isMail:function(match) {
        if (!match) {
            return false;
        }
        var mail = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
        if (mail.test(match)) {
            var maiarr = match.split(/@/);
            var mailobj = { //返回用户名与域名部分组成的数组 boolean判断时为真
                'name': maiarr[0],
                'domain': maiarr[1]
            };
            return mailobj;
        }
        return false;
    },
    /**
     * 检查是否合法手机号
     * @param match
     * @returns {boolean}
     */
    isPhone:function(match) {
        if (!match) {
            return false;
        }
        var phone = /^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9])\d{8}$/;
        if (phone.test(match)) {
            return true;
        }
        return false;
    },
    /**
     * 检查是否为utf8格式的中文
     * @param match
     * @returns {boolean}
     */
    isChinese:function(match) {
        var zhcn =/^[\u4E00-\u9FA5]+$/gi;//fixed |[\uFE30-\uFFA0]
        if (!zhcn.exec(match)) {
            return false;
        }
        return true;
    },
    /**
     * 检查是否http或https打头的url
     * @param match
     * @returns {boolean}
     */
    isUrl:function(match) {
        var Url = /^http[s]?:\/\/(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*\'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/\?)|(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/i;
        if (Url.test(match)) {
            return true;
        }
        return false;
    },
    isPassWord:function(match) {
        var pcre = /[A-Za-z]+/,num = /\d+/;
        if (pcre.test(match)) {
            if (num.test(match)) {
                return true;
            }
            return false;
        }
        return false;
    },
    /**
     * 检查是否全部为字母构成
     * @param match
     * @returns {boolean}
     */
    isAlphabet:function(match) {
        var pcre = /^[A-Za-z]+$/;
        if (pcre.test(match)) {
            return true;
        }
        return false;
    },
    isOpera:typeof opera !== 'undefined' && opera.toString() === '[object Opera]',
    /**
     * 页面载入Js文件
     * @param src      js文件url
     * @param callback 载入完毕（无论成功与否）执行的回调函数 可选
     * @returns {*}
     */
    loadJs:function (src,callback) {
        var scripts = document.getElementsByTagName('script');
        for(i in scripts)
        {
            if(scripts[i].src == src)
            {
                if(typeof callback != 'function')
                {
                    return false;
                }
                return callback();
            }
        }
        var head     = document.getElementsByTagName('head').item(0);
        var node     = document.createElement('script');
        node.type    = 'text/javascript';
        node.src     = src;
        node.charset = 'utf-8';
        node.async   = true;
        //addEventListener
        if(typeof callback == 'function')
        {
            if (node.attachEvent && !(node.attachEvent.toString && node.attachEvent.toString().indexOf('[native code') < 0) && !this.isOpera)
            {
                node.attachEvent('onreadystatechange', callback);
            }else{
                node.addEventListener('load', callback, false);
                node.addEventListener('error', callback, false);
            }
        }
        head.appendChild(node);
    },
    /**
     * 载入css文件
     * @param href 载入css文件的url
     * @param callback 载入完成后执行的回调函数（无论成功与否） 可选
     * @returns {*}
     */
    loadCss:function (href,callback) {
        var links = document.getElementsByTagName('link');
        for(i in links)
        {
            if(links[i].href == href)
            {
                if(typeof callback != 'function')
                {
                    return false;
                }
                return callback();
            }
        }
        var head  = document.getElementsByTagName('head').item(0);
        var link  = document.createElement('link');
        link.type = 'text/css';
        link.rel  = 'stylesheet';
        link.href = href;
        if(typeof callback == 'function')
        {
            if (link.attachEvent && !(link.attachEvent.toString && link.attachEvent.toString().indexOf('[native code') < 0) && !this.isOpera)
            {
                link.attachEvent('onreadystatechange', callback);
            }else{
                link.addEventListener('load', callback, false);
                link.addEventListener('error', callback, false);
            }
        }
        head.appendChild(link);
    },
    /**
     * 获取窗体尺寸信息
     * @returns {{width: *, w: *, height: *, h: *}}
     */
    windowSize:function() {
        var winWidth,winHeight;
        if (window.innerWidth) {
            winWidth = window.innerWidth;
        }else if ((document.body) && (document.body.clientWidth)) {
            winWidth = document.body.clientWidth;
        }
        if (window.innerHeight){
            winHeight = window.innerHeight;
        }else if ((document.body) && (document.body.clientHeight)) {
            winHeight = document.body.clientHeight;
        }
        if (document.documentElement && document.documentElement.clientHeight && document.documentElement.clientWidth) {
            winHeight = document.documentElement.clientHeight;
            winWidth = document.documentElement.clientWidth;
        }
        return {
            'width': winWidth,
            'w': winWidth,
            'height': winHeight,
            'h': winHeight
        };
    },
    /**
     * 读取|设置|清理cookie
     * -----
     * 读取cookie utils.cookie('cookieName');
     * 设置cookie utils.cookie('cookieName','cookieValue',cookieOptions);
     * ###cookieOptions对象格式：{expires: 365, path: '/', domain: 'example.com',secure: true}###
     * 清理cookie utils.cookie('cookieName',null);
     * -----
     * @param key
     * @param value
     * @param options
     * @returns {*}
     */
    cookie:function(key,value,options) {
        if(options == undefined) { //无options选项，options选项中 过期时间以天为单位
            options = {};//默认cookie设置{expires: 365, path: '/', domain: 'example.com',secure: true}
        }
        /*内部方法--cookie是否存在*/
        function hasCookie(key) {
            var cookieArray=document.cookie.split("; ");
            var cookie=new Object();
            for (var i=0;i<cookieArray.length;i++){
                var arr=cookieArray[i].split("=");
                if(arr[0]==key) return true;
            }
            return false;
        }
        /*===删除cookie 将value参数设定为null或字符串格式的'null'===*/
        if((!value && value !== undefined && value != 0) || value==='null') {
            if(hasCookie(key)) { //存在该cookie则删除
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() - 1);
                document.cookie = [
                    key, '=', '',
                    options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                    options.path    ? '; path=' + options.path : '',
                    options.domain  ? '; domain=' + options.domain : '',
                    options.secure  ? '; secure' : ''
                ].join('');
            }
            return true;//删除成功
        }
        /*===读取cookie 仅key参数===*/
        if(value == undefined) {
            var cookieArray=document.cookie.split('; ');//得到分割的cookie名值对
            var cookie=new Object();
            for (var i=0;i<cookieArray.length;i++){
                var arr=cookieArray[i].split('=');       //将名和值分开
                if(arr[0]==key) return decodeURIComponent(arr[1]); //如果是指定的cookie，则返回它的值
            }
            return '';//不存在的key 则返回空串
        }
        /*===写入cookie===*/
        //写入cookie ；有key有value，且value!==null
        if (value !== undefined && key!==undefined) {
            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            return (document.cookie = [
                key, '=', String(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));//返回设置cookie的字符串原型
        }
    },
    /**
     * 封装的利用localStorage的存储|读取|删除方法
     * @param key
     * @param value
     * @returns {*}
     */
    localData:function(key,value) {
        /*使用userData在本地存储文本数据的解决方案  -- 参考至 http://sofish.de/1872 */
        if(!window.localStorage) {
            //return 'use userData for localStorage';
            var localStorage  = window.localStorage =  {},
                prefix = 'data-userdata',
                doc = document,
                attrSrc = doc.body,
                //save attributeNames to <body>'s `data-userdata` attribute
                mark = function (keys, isRemove, temp, reg) {
                    attrSrc.load(prefix);
                    temp = attrSrc.getAttribute(prefix) || '';
                    reg = RegExp('\\b' + keys + '\\b,?', 'i');
                    var hasKey = reg.test(temp) ? 1 : 0;
                    temp = isRemove ? temp.replace(reg, '') : hasKey ? temp : temp === '' ? keys : temp.split(',').concat(keys).join(',');
                    //alert(temp);
                    attrSrc.setAttribute(prefix, temp);
                    attrSrc.save(prefix);
                };
            // add IE behavior support
            attrSrc.addBehavior('#default#userData');

            localStorage.getItem = function (keys) {
                attrSrc.load(keys);
                return attrSrc.getAttribute(keys);
            };
            localStorage.setItem = function (keys, values) {
                attrSrc.setAttribute(keys, values);
                attrSrc.save(keys);
                mark(keys);
            };
            localStorage.removeItem = function (keys) {
                attrSrc.removeAttribute(keys);
                attrSrc.save(keys);
                mark(keys, 1);
            };

            // clear all attributes on <body> tag that using for textStorage
            // and clearing them from the
            // 'data-userdata' attribute's value of <body> tag
            localStorage.clear = function () {
                attrSrc.load(prefix);
                var attrs = attrSrc.getAttribute(prefix).split(','),
                    len = attrs.length;
                if (attrs[0] === '') return;
                for (var i = 0; i < len; i++) {
                    attrSrc.removeAttribute(attrs[i]);
                    attrSrc.save(attrs[i]);
                };
                attrSrc.setAttribute(prefix, '');
                attrSrc.save(prefix);
            };
            //window.localStorage = J.localStorage = localStorage;
        }//利用userData绑定到html的body标签的localStorage方法完毕

        /*===清除所有本地数据 -- key参数为null或'null'字符串===*/
        if((!key && key !== undefined && key != 0) || key==='null') {
            window.localStorage.clear();
            return true;
        }
        /*===删除指定key的本地数据===*/
        if((!value && value !== undefined && value != 0) || value==='null') {
            window.localStorage.removeItem(key);
            return true;
        }
        /*===写入本地数据===*/
        if (value !== undefined && key!==undefined) {
            window.localStorage.setItem(key,value);
            return true;
        }
        /*===读取本地数据===*/
        if(key !== undefined && value == undefined) {
            return window.localStorage.getItem(key);
        }
    },
    /**
     * 检查是否合符天朝规范的身份证号，不是返回false，是返回身份证读取出的信息
     * @param match
     * @return false|[]
     */
    isID:function(match) {
        var id = match.toUpperCase();//18位身份证中的x为大写
        id = utils.trimAllSpace(match);//去除字符中的所有空格
        var ID18 = /^\d{17}(\d|X)$/,ID15 = /^\d{15}$/,
            oCity = {
                11:"\u5317\u4eac",
                12:"\u5929\u6d25",
                13:"\u6cb3\u5317",
                14:"\u5c71\u897f",
                15:"\u5185\u8499\u53e4",
                21:"\u8fbd\u5b81",
                22:"\u5409\u6797",
                23:"\u9ed1\u9f99\u6c5f",
                31:"\u4e0a\u6d77",
                32:"\u6c5f\u82cf",
                33:"\u6d59\u6c5f",
                34:"\u5b89\u5fbd",
                35:"\u798f\u5efa",
                36:"\u6c5f\u897f",
                37:"\u5c71\u4e1c",
                41:"\u6cb3\u5357",
                42:"\u6e56\u5317",
                43:"\u6e56\u5357",
                44:"\u5e7f\u4e1c",
                45:"\u5e7f\u897f",
                46:"\u6d77\u5357",
                50:"\u91cd\u5e86",
                51:"\u56db\u5ddd",
                52:"\u8d35\u5dde",
                53:"\u4e91\u5357",
                54:"\u897f\u85cf",
                61:"\u9655\u897f",
                62:"\u7518\u8083",
                63:"\u9752\u6d77",
                64:"\u5b81\u590f",
                65:"\u65b0\u7586",
                71:"\u53f0\u6e7e",
                81:"\u9999\u6e2f",
                82:"\u6fb3\u95e8",
                91:"\u56fd\u5916"
            };
        //不符合基本的身份证号码规则
        if(!(ID18.test(id) || ID15.test(id))) {
            return false;
        }
        function _15to18(cardNumber) {
            //15位转换为18位 数据库统一保存18位数字身份证
            var CardNo17 = cardNumber.substr(0, 6) + '19' + cardNumber.substr(6, 9) ;
            var Wi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
            var Ai = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
            var cardNoSum = 0;

            for (var i = 0; i < CardNo17.length; i++) {
                cardNoSum += CardNo17.charAt(i) * Wi[i];
            }
            var seq = cardNoSum % 11;
            return CardNo17+''+Ai[seq];
        }
        function CheckValidCode(carNumber) {
            //效验第18位字符的合法性
            var CardNo17 = carNumber.substr(0,17);//去除18位id中的最后一位进行运算后对比
            var Wi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
            var Ai = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
            var cardNoSum = 0;
            for (var i = 0; i < CardNo17.length; i++) {
                cardNoSum += CardNo17.charAt(i) * Wi[i];
            }
            var seq = cardNoSum % 11;
            if(Ai[seq]!=carNumber.substr(17,1)) {
                return false;
            }
            return true;
        }
        //输入的18位效验码合法性检测
        if(ID18.test(id)) {if(!CheckValidCode(id)) {return false;}}//输入的18位身份证号  先效验其标准编码合法性
        if(ID15.test(id)) {id = _15to18(id);}//将15位转换为18位 == 唯一对应
        //使用处理并转换完毕的18位身份证数字进行统一效验
        var City       = id.substr(0, 2),
            BirthYear  = id.substr(6, 4),
            BirthMonth = id.substr(10, 2),
            BirthDay   = id.substr(12, 2),
            StrData    = id.substr(6, 8),//形如19881101类型的出生日期表示法
            Sex        = id.charAt(16) % 2 ,//男1 女0
            Sexcn      = Sex?'男':'女';
        //地域验证
        if(oCity[parseInt(City)] == null) {return false;}
        //出生日期验证
        var BirthObj = StrData.match(/^(\d{1,4})(\d{1,2})(\d{1,2})$/);
        if(BirthObj == null) {return false;}//出生日期基本的组合规则不符合要求
        var d = new Date(BirthObj[1], BirthObj[2] - 1, BirthObj[3]); //效验出生日期的数字年份是否符合要求
        if(d.getFullYear() == BirthObj[1] && (d.getMonth() + 1) == BirthObj[2] && d.getDate() == BirthObj[3]) {
            return {
                'ID':id,
                'Y':BirthYear,
                'm':BirthMonth,
                'd':BirthDay,
                'YmdNumber':Number(StrData),
                'YmdString':BirthYear+'-'+BirthMonth+'-'+BirthDay,
                'sexInt':Sex,
                'sexCn':Sexcn,
                'local':oCity[parseInt(City)]
            };
        }
        return false;
    },
    // 检查身份证同名函数
    isCardNumber:function (id) {
        return utils.isID(id);
    },
    /**
     * 绑定datetimepicker日历时间组件
     * @param $node jquery对象
     */
    bindDateTimePicker:function ($node) {
        $node.each(function (i,e) {
            $(e).datetimepicker({
                language: 'zh-CN',
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd hh:ii:ss',
                minView: 0,
                startView: 2,
                viewSelect: 3
            });
        });
    },
    /**
     * 精简的没有进度条的单个文件ajax上传
     * @param id
     * @param success
     * @param error
     * @returns {boolean}
     */
    ajaxUploadFile: function (id,success,error) {
        var that = $('#' + id);
        that.dmUploader({
            method:'POST',
            fieldName:'File',
            multiple:false,
            url:'/manage/upload/upload?origin=ajax',
            onInit:function () {},
            onNewFile:function (id,file_info) {},
            onUploadProgress:function (id, percent) {},
            onUploadSuccess:function (id, data) {
                if(data.error_code == 0)
                {
                    success && success(data);
                }else {
                    error && error();
                }
            },
            onUploadError:function (id, xhr, status, errorThrown) {
                error && error();
            }
        });
        return false;
    },
    /**
     * 绑定带进度条的单个或多个文件上传控件
     * @param id string 待绑定上传input文件框的ID
     * @param _param object 绑定ajax文件上传的参数对象， {multiple:bool,error:function,success:function,data:{}}
     */
    bindAjaxUploader: function (id, _param) {
        var param = $.extend({
            url:'',//上传文件后端Url，留空则为/manage/upload/upload?origin=ajax
            allow_extension: null,//null不限制、需限制时使用数组 ['jpg','jpeg']
            multiple:true,//是否允许选择多张图，默认允许多张
            error:function () {},//上传成功的回调函数
            success:function () {},//上传失败的回调函数
            data: {} //上传控制器额外附带的key-value
        },_param);
        param.file_name = [];//初始化内部变量
        /**
         * -----
         * 上传按钮html结构如下，其中input的title属性可用于一些提示性文案文字
         * -----
         * <div class="upload-container">
         *    <span class="upload">
         *        <input type="file" name="File" title="点击选择图片并上传" id="dm">
         *    </span>
         * </div>
         * -----
         * Js中调用：utils.bindUploader('input元素的ID',{
         *      'data':'需在上传时额外附带的key-value对象，一般留空即可',
         *      'success':'文件上传成功后的回调函数，参数为上传后端控制器返回的json',
         *      'error':'文件上传失败后的回调函数，大部分时候不用传，页面会有toast提示',
         * });
         * 即可实现ajax文件上传，上传成功后使用回调函数处理接下来的逻辑，譬如将文件url放入隐藏的input框，将图片显示成预览图等
         * -----
         */
        var that = $("#" + id);
        // 提示
        if(!utils.isEmpty($(that).parent('.upload').html()))
        {
            if(!utils.isEmpty(that.attr('title')))
            {
                $(that).parent('.upload').tooltip({
                    title:that.attr('title')
                });
            }
        }
        /**
         * 初始化上传进度条内部方法
         * @param file_name 文件名
         */
        var initProcess = function (file_name) {
            var process_dom = [
                '<div id="dm-uploader-process">',
                    '<div class="row">',
                        '<div class="col-md-12">',
                            '<div id="dm-uploader-filename">正在上传：'+ file_name || '文件名未知' +'</div>',
                            '<div id="dm-uploader-percent-container">',
                                '<div id="dm-uploader-percent-bar" class="bg-primary dm-uploader-percent">',

                                '</div>',
                            '</div>',
                        '</div>',
                    '</div>',
                '</div>'
            ].join('');
            bootbox.dialog({
                message: process_dom,
                closeButton:false,
                //title: '文件上传进度',
                onEscape: true,
                //backdrop: true,
                buttons: {}
            });
        };
        // 图片（若是图片）预览，上传进度
        that.dmUploader({
            method:'POST',
            fieldName:'File',
            extraData:param.data,
            extFilter:param.allow_extension ? param.allow_extension : null,
            multiple:!!param.multiple,
            url:param.url ?  param.url : '/manage/upload/upload?origin=ajax',
            onInit:function () {},
            onNewFile:function (id,file_info) {
                param.file_name[id] = file_info.name;
                // console.log(file_info);
                // initProcess();
            },
            onUploadProgress:function (id, percent) {
                if(utils.isEmpty($('#dm-uploader-process').html()))
                {
                    initProcess(param.file_name[id]);
                }
                $('#dm-uploader-percent-bar').css('width',percent + '%').text(percent+'%');
                // console.log(percent);
            },
            onUploadSuccess:function (id, data) {
                $('.bootbox').modal('hide');
                param.success && param.success(data);
            },
            onFileExtError:function(file) {
                $('.bootbox').modal('hide');
                param.error && param.error('所选文件后缀有误，允许后缀：' + param.allow_extension.join('、'));
            },
            onUploadError:function (id, xhr, status, errorThrown) {
                $('.bootbox').modal('hide');
                param.error && param.error('上传文件出错');
            }
        });
    },
    /**
     * 绑定图片裁剪上传控件
     * @param node_id 绑定裁剪元素的ID
     * @param params  裁剪方法的参数对象
     */
    bindCutImageUploader:function (node_id,params) {

        // 默认参数和额外参数进行合并
        var param = $.extend({
            rate:'4/3',//裁剪图的比例
            width:800,//指定裁剪后图片宽度
            height:600,//指定裁剪后图片高度
            extraData:{},//额外塞入上传的post数据体重的filed-value对象数组
            success:function (data) {},//裁剪并上传成功后的回调函数，data参数为服务器返回的json对象
            error:function () {}//裁剪或上传失败的回调函数
        },params);

        // 渲染生成裁剪框
        var dialog_content = [
            '<div id="image_cut_container">',
                '<div id="image_cut_preview">',
                    '<div id="cut"></div>',
                '</div>',
                '<div id="image_cut_uploader">',
                    '<input type="file" id="_File" name="File">',
                    '<button class="btn btn-default btn-upload btn-select-image" type="button">点击选择图片</button>',
                    '<p>建议上传宽高比为 <span id="image_cut_rate"></span> 的图片，裁剪上传的图片尺寸为：<span id="image_cut_width"></span>px * <sapn id="image_cut_height"></sapn>px</p>',
                '</div>',
                '<canvas id="image_cut_canvas" width="800px" height="600px" style="display: none;"></canvas>',
            '</div>'
        ].join('');


        var crop = {
            /**
             * 显示选择图片和预览图并裁剪的浮层
             */
            showCutDialog:function () {
                var that = this;
                bootbox.dialog({
                    message: dialog_content,
                    //size:'large',
                    title: '图片剪裁',
                    onEscape: true,
                    //backdrop: true,
                    buttons: {
                        success: {
                            label: '<i class="fa fa-upload" id="do_upload"></i> 确认并上传',
                            className: "btn btn-danger",
                            callback: function () {
                                if(!$('#cut').html()){
                                    utils.toast('请选择图片并裁剪');
                                    return false;
                                }
                                that.uploadBlob();
                            }
                        },
                        cancel: {
                            label: '取消操作',
                            className: "btn btn-default btn-cancel"
                        }
                    }
                });
            },
            /**
             * 初始化裁剪框浮层和各种事件
             */
            init:function () {
                var width = param.width;
                var height= param.width / param._rate;
                // 调整canvas元素长宽
                $('#image_cut_canvas').attr({
                    width : width + 'px',
                    height:height + 'px'
                });
                this.width = width;
                this.height= height;
                this.param = param;
                this.bindEvents();
            },
            /**
             * 初始化Jcrop裁剪插件
             */
            initCrop:function () {
                var that = this;
                var crop_object;
                $('#corp_box').Jcrop(
                    {
                        onSelect:that.onCropSelect,
                        allowSelect:false,
                        aspectRatio:param._rate,
                        boxWidth:$('#cut').width(),
                        boxHeight:$('#cut').height()
                    },
                    function () {
                        crop_object = this;
                        this.setSelect([0,0,3000,3000]);
                    }
                );
            },
            /**
             * Jcrop的onSelect事件，将图片按尺寸塞入canvas
             */
            onCropSelect:function (corp_object) {
                var img=document.getElementById("corp_box");
                var ctx=document.getElementById("image_cut_canvas").getContext("2d");
                ctx.drawImage(img,corp_object.x,corp_object.y, corp_object.w, corp_object.h,0,0,crop.width,crop.height);
            },
            /**
             * 绑定裁剪框各种事件
             */
            bindEvents:function () {
                var that = this;
                // 绑定点击选择图片按钮的事件进行传递
                $('#image_cut_container').on('click','.btn-select-image',function () {
                    $('#_File').click();
                });
                // 绑定浮层上传input输入框的change事件
                $('#_File').on('change',function () {
                    that.changeEvent.call(this);
                });
            },
            /**
             * 选择图片后的变化事件
             */
            changeEvent:function () {
                // 浮层上的input触发change事件后的动作和方法
                var checked_file_info = $(this).get(0).files[0];
                if(utils.isEmpty(checked_file_info))
                {
                    return false;
                }
                var fileName = checked_file_info.name;
                var fileType=fileName.substring(fileName.lastIndexOf('.'),fileName.length).toLowerCase();
                if(fileType!='.gif' && fileType!='.jpeg' && fileType!='.png' && fileType!='.jpg')
                {
                    utils.toast('请选择gif、jpeg、jpg或png格式的图片');
                    return false;
                }
                utils.field_name = fileName;
                // 将选择的图片文件本地渲染装载
                var file_reader = new FileReader();
                file_reader.readAsDataURL(checked_file_info);
                file_reader.onload =function (e) {
                    var img_preview = '<img id="corp_box" src="'+e.target.result+'">';
                    $('#cut').html(img_preview);
                };

                // 装载图初始化Jcrop
                setTimeout(function () {
                    crop.initCrop();
                },50);
            },
            uploadBlob:function () {
                url  = '/manage/upload/upload?action=image';
                data = param.extraData || {};
                if(!$('#cut').html())
                {
                    return false;
                }

                function append(form,obj)
                {
                    for(var i in obj){
                        form.append && form.append( i , obj[i] );
                    }
                    return form;
                }

                //获取裁剪完后的base64图片url,转换为blob
                var data_blob = document.getElementById("image_cut_canvas").toDataURL();
                var formData  = new FormData();

                formData.append("File",dataURLtoBlob(data_blob) , utils.field_name || "default.png" );

                function dataURLtoBlob(dataurl) {
                    var arr  = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
                    while (n--) {
                        u8arr[n] = bstr.charCodeAt(n);
                    }
                    return new Blob([u8arr], { type: mime });
                }
                var data1 = append(formData,data);

                utils.showLoading("文件上传中,请稍候...");
                $.ajax( {
                    url: url, //用于文件上传的服务器端请求地址
                    type: 'POST',
                    data: data1,
                    async:true,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        param.success && param.success(data);
                        utils.hideLoading();
                        utils.toast(data.error_msg);
                    },
                    error: function (data, status, e){
                        utils.hideLoading();
                        param.error && param.error();
                        utils.alert("网络或服务器异常，请稍后再试，如有疑问请联系管理员");
                    }
                });
            }
        };

        // 指定ID名为 mode_id 的元素绑定点击事件
        $('#'+node_id).on('click',function () {
            crop.showCutDialog();
            var rate     = param.rate.split('/');
            param._rate  = rate[0] / rate[1];
            param.height = Math.floor(param.width / param._rate);
            $('#image_cut_rate').text(rate[0] + ':' + rate[1]);
            $('#image_cut_width').text(param.width);
            $('#image_cut_height').text(param.height);

            // init ++ bind event
            crop.init();
            return false;
        });
    },
    showLoading: function (title) {
        title = title || "数据加载中，请稍候...";
        var modalHtml = '<div class="modal fade" id="loadingModal" role="basic" aria-hidden="true" style="display: none;">' + '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                            '<div class="modal-body">' +
                                '<img src="/public/images/load.gif" alt="" class="loading">' +
                                '<span id="loadingLabel"> &nbsp;&nbsp;' + title + ' </span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        if (!($("#loadingModal").length > 0)) {
            $(document.body).append(modalHtml);
            $("#loadingModal").modal();
        } else {
            $("#loadingLabel").html("&nbsp;&nbsp;" + title);
            $("#loadingModal").modal('show');
        }
    },
    hideLoading: function (callback) {
        $("#loadingModal").modal('hide');
        setTimeout(function () {
            $("body").addClass('modal-open');
        },500);
        callback && callback();
    },
    /**
     * 解析Url
     * @param  url string 待解析的url
     * @return []
     */
    parseUrl: function (url) {
        var a  = document.createElement('a');
        a.href = url;
        return {
            source: url,
            protocol: a.protocol.replace(':', ''),
            host: a.hostname,
            port: a.port,
            query: a.search,
            params: (function () {
                var ret = {},
                        seg = a.search.replace(/^\?/, '').split('&'),
                        len = seg.length, i = 0, s;
                for (; i < len; i++) {
                    if (!seg[i]) {
                        continue;
                    }
                    s = seg[i].split('=');
                    ret[s[0]] = s[1];
                }
                return ret;
            })(),
            file: (a.pathname.match(/\/([^\/?#]+)$/i) || [, ''])[1],
            hash: a.hash.replace('#', ''),
            path: a.pathname.replace(/^([^\/])/, '/$1'),
            relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ''])[1],
            segments: a.pathname.replace(/^\//, '').split('/')
        };
    },
    
    /*  
     *  过滤XSS
     *  @param  {String}    str 需要过滤的内容
     *  @return {String}    显示的内容
     */
    xss: function (str) {
        var div = document.createElement("div"),
            text = document.createTextNode(str), val = '';
        div.appendChild(text);
        val  = div.innerHTML;
        text = null;
        div  = null;
        return val;
    },
    /**
     * 获取url中的get变量值
     * @param  name string 获取的get变量的key名称
     * @return
     */
    getUrlParam: function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&#]*)(&|$|#)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg);  //匹配目标参数
        if (r != null)
        {
            return decodeURI(r[2]);
        }
        return null; //返回参数值
    },
    // getUrlParam同名函数
    getUrlQueryString:function (name) {
        return utils.getUrlParam(name);
    },
    /**
     * 设置url的变量并返回设置后的url
     * @param name get变量名
     * @param value get变量值
     * @param url 待设置的url-可选
     * @returns {string}
     */
    setUrlParam:function (name,value,url) {
        url         = url || window.location.href;
        var str     = "";
        var baseUrl = "";
        if (url.indexOf('?') != -1) {
            str     = url.substr(url.indexOf('?') + 1);
            baseUrl = url.substr(0,url.indexOf('?'));//不带“？”的路径Url
        }else{
            return url + "?" + name + "=" + value;
        }
        var paraArr,hasPara = false;
        if(str.indexOf('&') != -1) {
            /*将现有多个key-value参数拆分成key<==>value形式，避免多余的&捣乱*/
            paraArr = str.split('&');
            //console.log(paraArr);
            for(key in paraArr) {
                if(paraArr[key] == "") {
                    paraArr.splice(key,1);//清理这个空元素
                    continue;
                }
                var keyValue = paraArr[key].split('=');
                if(paraArr[key] == "") {
                    /**无getName变态形式**/
                    paraArr.splice(key,1);//清理这个变态元素
                    continue;
                }
                /*找到url中原先存在该get变量，修改其值*/
                if(keyValue[0] == name) {
                    hasPara = true;
                    paraArr[key] = name + "=" + value;
                }
            }
            if(hasPara) {
                return baseUrl + "?" + paraArr.join("&");
            }else {
                return baseUrl + "?" + paraArr.join("&") + "&" + name + "=" + value;
            }
        }else {
            /**Url中存在"?"但不存在"&" => 可能有一个get变量也可能仅有一个破"?"**/
            if(str.indexOf('=') != -1) {
                paraArr = str.split('=');
                if(paraArr[0] == name || paraArr[0] == "") {
                    return baseUrl + "?" + name + "=" + value;
                }
                return baseUrl + "?" + paraArr[0] + "=" + paraArr[1] + "&" + name + "=" + value;
            }else {
                return baseUrl + "?" + name + "=" + value;
            }
        }
    },
    // setUrlParam同名函数
    setUrlQueryString:function (name,value,url) {
        return utils.setUrlParam(name,value,url);
    },
    isHttps : function () {
        return 'https:' === document.location.protocol;
    },
    /**
     * 获取http协议
     * @returns {string}
     */
    getProtocol : function () {
        return utils.isHttps() ? 'https' : 'http';
    },
    htmlEncode : function(s) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(s));
        return div.innerHTML;
    },
    //转化小数为分数
    decimalToFraction:function(dec,digit){
        dec = Math.round(dec * (Math.pow(10,digit))) / Math.pow(10,digit) ;
        var tmpa,tmpb
        tmpa = dec*100
        tmpb =100
        for (var i = 100 ;i >0 ;i--){
            var tmp = (tmpa/i).toString();
            var tmp2 = tmp.indexOf(".")
            if ( tmp2 == -1){
                var tmp3 = (tmpb/i).toString();
                var tmp4 = tmp3.indexOf(".")
                if ( tmp4 == -1 ){
                    tmpa = tmpa/i
                    tmpb = tmpb/i
                }
            }
        }
       return (tmpa+"/"+tmpb);
    },
    /**
     * 裁剪字符串并返回能用于tooltip预览的html结构
     * @param string
     * @param length
     */
    subStringForTooltip:function (string,length) {
        if(utils.isEmpty(string) || string.length <= length)
        {
            return string;
        }
        var node = $('<div><div class="tooltips" data-toggle="tooltip"></div></div>');
        node.find('.tooltips').attr('data-original-title',string);
        node.find('.tooltips').text(string.substring(0,length) + '...');
        return node.html();
    },
    /**
     * 金额输入框限制
     * @param obj
     */
    numberControl:function (obj) {
        obj.value = obj.value.replace(/[^\d.]/g, ""); //清除"数字"和"."以外的字符
        obj.value = obj.value.replace(/^\./g, ""); //验证第一个字符是数字
        obj.value = obj.value.replace(/\.{2,}/g, "."); //只保留第一个., 清除多余的
        obj.value = obj.value.replace(".", "$#$").replace(/\./g, "").replace("$#$", ".");
        obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/, '$1$2.$3'); //只能输入两个小数
    },
    /**
     * 字符截取和省略
     * @param string
     * @param length
     * @returns string
     */
    subString:function (string,length)
    {
        if(utils.isEmpty(string) || string.length <= length)
        {
            return string;
        }
        return string.substring(0,length) + '...';
    }
};// End utils

Array.prototype.remove = function(dx) {
    if (isNaN(dx) || dx > this.length) {
        return false;
    }
    for (var i = 0, n = 0; i < this.length; i++) {
        if (this[i] != this[dx]) {
            this[n++] = this[i]
        }
    }
    this.length -= 1;
};

/**
 * 元素抖动
 * @param intShakes   次数
 * @param intDistance 距离
 * @param intDuration 持续时间，毫秒
 * @returns {jQuery}
 */
jQuery.fn.shake = function (intShakes /*Amount of shakes*/, intDistance /*Shake distance*/, intDuration /*Time duration*/) {
    this.each(function () {
        var jqNode = $(this);
        jqNode.css({ position: 'relative' });
        for (var x = 1; x <= intShakes; x++) {
            jqNode.animate({ left: (intDistance * -1) }, (((intDuration / intShakes) / 4)))
                .animate({ left: intDistance }, ((intDuration / intShakes) / 2))
                .animate({ left: 0 }, (((intDuration / intShakes) / 4)));
        }
    });
    return this;
};
