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
     * bootbox提示方法封装
     * @param  message  提示消息文字
     * @param  callback 点击确认之后的回调函数
     * @param  title    可选的自定义提示框标题
     * @param  OkbtnClass    可选的自定义确认按钮的class名称
     */
    alert: function (message, callback, title, OkbtnClass) {
        title = title || "提示";
        OkbtnClass = OkbtnClass || 'btn-circle';
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
     * 删除数据确认方法封装
     * @param message 删除确认浮层提示消息
     * @param request_url 删除提交的后端request网址，仅post
     * @param data  删除确认提交的数据对象
     * @param successCallBack  确认并执行之后的回调函数
     * @param callCallBack 取消不执行后的回调函数
     */
    deleteConfirm: function (message,request_url,data,successCallBack,callCallBack) {
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
                        $.ajax({
                            url: request_url,
                            type: 'POST',
                            data: data,
                            success: function (data) {
                                if(data.error_code == 0){
                                    utils.alert(data.error_msg ? data.error_msg : '操作成功');
                                    successCallBack();
                                }else{
                                    utils.alert(data.error_msg ? data.error_msg : '未知错误');
                                }
                            },
                            error:function () {
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
            utils.hideLoading()
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
        var phone = /^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|170)\d{8}$/;
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
        if (window.innerWidth)
            winWidth = window.innerWidth;
        else if ((document.body) && (document.body.clientWidth))
            winWidth = document.body.clientWidth;
        if (window.innerHeight)
            winHeight = window.innerHeight;
        else if ((document.body) && (document.body.clientHeight))
            winHeight = document.body.clientHeight;
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
     * @returns false|[]
     */
    isID:function(match) {
        var id = match.toUpperCase();//18位身份证中的x为大写
        id = this.trimAll(match);//去除字符中的所有空格
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
            return {'ID':id,'Y':BirthYear,'m':BirthMonth,'d':BirthDay,'YmdNumber':Number(StrData),'YmdString':BirthYear+'-'+BirthMonth+'-'+BirthDay,'sexInt':Sex,'sexCn':Sexcn,'local':oCity[parseInt(City)]};
        }
        return false;
    },
    // 检查身份证同名函数
    isCardNumber:function (id) {
        return utils.isID(id);
    },
    // 上传formdata
    uploadBlob: function (id, callback,param) {
            url = param["url"] || '/upload/handle?action=uploadimage';
            data = param["data"] || {};
            data._csrf = utils.getCsrf();
            data._safe = param["safe"];
            if(!$("#imgfield").html()){  
              return;
           }

           function append(form,obj){
              for(var i in obj){
                form.append && form.append( i , obj[i] );
              }

              return form;
           }
           
           //获取裁剪完后的base64图片url,转换为blob  
           var data_blob=document.getElementById("myCan").toDataURL();
           var formData=new FormData();

           formData.append("upfile",dataURLtoBlob(data_blob) , utils.uploadBlob.filename || "default.png" );

           function dataURLtoBlob(dataurl) {
               var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                       bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
               while (n--) {
                   u8arr[n] = bstr.charCodeAt(n);
               }  
               return new Blob([u8arr], { type: mime });
           }  
           var data1=append(formData,data);

            utils.showLoading("文件上传中,请稍候...");
            $.ajax( {
                        url: url, //用于文件上传的服务器端请求地址
                        secureuri: false, //是否需要安全协议，一般设置为false
                        type: 'POST',  
                        data: data1,
                        async:true,
                        cache: false,
                        contentType: false,  
                        processData: false,  
                        success: function (data) {
                            callback && callback(data);
                            // $uploader.replaceWith(origin);
                            // $("#" + id).on("change", self);
                            utils.hideLoading();
                           
                        },
                        error: function (data, status, e){
                            utils.hideLoading();
                            console.log(e);
                            utils.alert("网络或服务器异常，请稍后再试，如有疑问请联系管理员");
                        }
                    });
        },
    //传入fn  解决utils.uploadFile.caller为null的问题
    uploadFile: function (id, callback, url, data, safe,fn) {
        url = url || '/upload/handle?action=uploadimage';
        data = data || {};
        data._csrf = utils.getCsrf();
        data._safe = safe;
        var $uploader = $("#" + id);
        var origin = $uploader[0].outerHTML;
        var self = utils.uploadFile.caller || fn;
        utils.showLoading("文件上传中,请稍候...");
        $.ajaxFileUpload({
            url: url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: id, //文件上传域的ID
            data: data,
            success: function (data) {
                var str = $(data).find("body").text();
                var data = $.parseJSON(str);
                callback && callback(data);
                $uploader.replaceWith(origin);
                $("#" + id).on("change", self);
                $("#" + id).val('');
                utils.hideLoading();
            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                $uploader.replaceWith(origin);
                $("#" + id).on("change", self);
                $("#" + id).val('');
                utils.hideLoading();

                console.log(e);
                utils.alert("网络正忙，请稍后再试，如有疑问请联系管理员");
            }
        })
        return false;
    },
    bindUploader: function (id, callback, url, data, safe) {
        $("#" + id).on("change", function () {
            utils.uploadFile($(this).attr("id"),
                function (data) {
                    if (data.state == "SUCCESS") {
                        callback && callback(data);
                    } else {
                        utils.alert(data.state);
                    }
                }
            , url, data, safe);
        });
    },
    showLoading: function (title) {
        title = title || "数据加载中，请稍候...";
        var modalHtml = '<div class="modal fade" id="loadingModal" role="basic" aria-hidden="true" style="display: none;">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-body">' +
                '<img src="/static/images/load.gif" alt="" class="loading">' +
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
        callback && callback();
    },
    /**
     * 显示加载进度|显示进度条
     * @param  percent float 0~100
     * @param  msg     string 显示的消息
     */
    showProgress: function (percent, msg) {
        percent = Number(percent);
        if(!utils.isNumber(percent)) {
            percent = 0;
        }
        if(percent < 0) {
            percent = 0;
        }
        if (percent > 100) {
            percent = 100;
        }
        percent = Math.round(percent);
        var modalHtml = '<div class="modal fade" id="progressModal" role="basic" aria-hidden="true" style="display: none;">'
                      + '    <div class="modal-dialog">'
                      + '        <div class="modal-content">'
                      + '            <div class="modal-body" style="padding:1px;">'
                      + '                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:' + percent + '%;min-width: 2em;">'
                      + '                    <span>' + percent + '%</span>'
                      + '                </div>'
                      + '                <div class="clearfix"></div>'
                      + '            </div>'
                      + '        </div>'
                      + '    </div>'
                      + '</div>';
        // 不存在添加到body中
        if (!($("#progressModal").length > 0)) {
            $(document.body).append(modalHtml);
            $("#progressModal").modal();//创建遮罩层
        } else {
            $("#progressModal .progress-bar").css("width",percent + "%").children("span").text(percent + "%");//更新进度值
            $("#progressModal").modal('show');
        }
        
        $('#progressModal').offset().height;
        if(this.isEmpty(msg)){
            $('#progressModal .modal-dialog #progressModal_msg').remove();
        }else{
            if($('#progressModal_msg').length){
                $('#progressModal_msg').html(msg)
            }else{
                $('#progressModal .modal-dialog').append('<div id="progressModal_msg" style="text-align:center; line-height:50px; color:#337ab7; border-radius:2px; background:#FFF;">' + msg + '</div>')
            }
        }
    },
    // 隐藏进度条
    hideProgress: function (callback) {
        $("#progressModal").modal('hide');
        callback && callback();
    },
    /**
     * 解析Url
     * @param  url string 待解析的url
     * @return []
     */
    parseURL: function (url) {
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
     * 获取url或者自定义字符串中的参数
     * 
     * @param {String} name 不传name则直接返回整个参数对象
     * @param {String} queryStr 自定义字符串
     * @param {Boolean} true 不进行参数XSS安全过滤
     * @param {Boolean} true 不进行自动解码
     * @return {String|Object} 获取到的参数值或者由所有参数组成完整对象
     */
    getQuery: function (name, queryStr, unxss, undecode) {
        var str = queryStr || location.search.replace("?", ""), tempArr,
            obj = {}, temp, arr = str.split("&"), len = arr.length;

        if (len > 0) {
            for (var i = 0; i < len; i++) {
                try {
                    if ((tempArr = arr[i].split('=')).length === 2) {
                        temp = undecode ? tempArr[1] : decodeURIComponent(tempArr[1]);
                        obj[tempArr[0]] = unxss ? temp : this.xss(temp);
                    }
                } catch (e) {
                }
            }
        }
        return name ? obj[name] : obj;
    },
    bindOnlyTimePicker: function ($controls, opt) {
        $controls.each(function (i, e) {
            var $control = $(e),
                options = $.extend({
                    autoclose: true,
                    minuteStep: 5,
                    showSeconds: false,
                    showMeridian: false
                }, opt);
            
            $control.timepicker(options);
            
            $control.parent('.input-group').on('click', '.input-group-btn', function(e){
                e.preventDefault();
                $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
            });
        });
    },
    bindTimePicker: function ($controls, opt) {
        $controls.each(function (i, e) {
                                 
            var $control = $(e),
                options = $.extend({
                    language:  'zh-CN', 
                    autoclose: true,
                    format: "yyyy-mm-dd hh:ii:ss"
                }, opt);
            
            $control.datetimepicker(options);
            
            $control.next(".input-group-btn").find(".fa-calendar").parent("button").on("click", function () {
                $control.datetimepicker("show");
            });
            $control.next(".input-group-btn").find(".fa-times").parent("button").on("click", function () {
                $control.val("");
                $control.trigger("changeDate").trigger("change");
            });
        });
    },
    bindDatePicker: function ($controls) {
        $controls.each(function (i, e) {
            var $control = $(e);

            $control.datepicker({
                language:  'zh-CN', 
                autoclose: true,
                format: "yyyy-mm-dd"
            });
            $control.next(".input-group-btn").find(".fa-calendar").parent("button").on("click", function () {
                $control.datepicker("show");
            });
            $control.next(".input-group-btn").find(".fa-times").parent("button").on("click", function () {
                $control.val("");
                $control.trigger("changeDate").trigger("change");
            });
        });
    },
    bindMonthPicker: function ($controls) {
        $controls.each(function (i, e) {
            var $control = $(e);

            $control.datetimepicker({
                language:  'zh-CN', 
                autoclose: true,
                startView: 3,
                minView: 3,
                maxView : 4,
                viewSelect: 3,
                format: "yyyy-mm"
            });
            $control.next(".input-group-btn").find(".fa-calendar").parent("button").on("click", function () {
                $control.datetimepicker("show");
            });
            $control.next(".input-group-btn").find(".fa-times").parent("button").on("click", function () {
                $control.val("");
                $control.trigger("changeDate").trigger("change");
            });
        });
    },
    /**
     * 获取url中的get变量值
     * @param  {[type]} name [description]
     * @return {[type]}      [description]
     */
    getUrlParam: function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&#]*)(&|$|#)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg);  //匹配目标参数
        if (r != null)
            return unescape(r[2]);
        return null; //返回参数值
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
    /**
     * 强制刷新网页
     */
    reloadPage : function(){
        utils.showLoading("页面加载中,请稍候...");
        location.reload(true);
    },
    // 交换数组元素
    swapArrItems : function(arr, index1, index2) {
        arr[index1] = arr.splice(index2, 1, arr[index1])[0];
        return arr;
    },
 
    // 上移
    moveUpArrItem : function(arr, $index) {
        if($index === 0) {
            return;
        }
        return utils.swapArrItems(arr, $index, $index - 1);
    },
 
    // 下移
    moveDownArrItem : function(arr, $index) {
        if($index === arr.length -1) {
            return;
        }
        return utils.swapArrItems(arr, $index, $index + 1);
    },
    
    isHttps : function () {
        return 'https:' === document.location.protocol;
    },
    
    getProtocol : function () {
        return utils.isHttps() ? 'https' : 'http';
    },
    
    getProtocolUrl : function (originUrl) {
        if(utils.isHttps()) {
            originUrl = originUrl && originUrl.replace("http://","https://");
        }
        return originUrl;
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
     * 绑定剪切上传框
     * @param  String id     绑定的元素ID
     * @param  Object param  绑定后的裁剪参数 {callback,url,data,safe,aspectRatio,width}
     */
    bindCrop:function(id,param){
         var filename='';

         var htmlCodes = [
         '<div id="upload-content">',
         '     <div class="picCont" style="width:500px;margin-left: 33px;" >  ',
         '         <div id=imgfield  style=overflow:hidden;width:100%;height:100% >',
         '         </div>  ',
         '     </div>  ',
         '     <div class="picFooter">  ',
         '         <input type="file" id="fileimg" name="fileimg" style="display:none" />  ',
         '         <span class="btn upload" >选择图片</span> ',
         '         <p>建议上传宽度高度之比为 <span id="js-aspt">4:3</span>&nbsp;的图片</p>' ,
         '     </div> ',
         '    <canvas id="myCan" width="800" height="600" style="display: none;"></canvas>',
         '</div>'
         ].join("");


        $("#"+id).on("click",function(){
            param["clickCallback"] &&  param["clickCallback"](param);
            var _param=$.extend({},param);

            // 弹框
            dialog();
            if(_param["aspectRatio"]){
                var aspt = param["aspectRatio"].split("/");
                _param["aspectRatio"] = aspt[0]/aspt[1];
                $("#js-aspt").html( aspt[0]+":"+aspt[1] );
            }else{

            };
            crop.init(_param);
            // 阻止默认事件
            return false;

            function dialog(){
                  bootbox.dialog({
                        message: htmlCodes,
                        title: '图片剪裁',
                        onEscape: true,
                        backdrop: true,
                        buttons: {
                            success:
                            {
                                label: '<i class="fa fa-trash-o" id="upload_img"></i>确认并上传',
                                className: "btn btn-danger btn-circle",
                                callback: function () {
                                        if(!$("#imgfield").html()){
                                            return;
                                        }
                                        utils.uploadBlob.filename=filename;
                                        utils.uploadBlob("",function(data){
                                            _param && _param["callback"] && _param["callback"](data);
                                        },_param);
                                }
                            },
                            cancel:
                            {
                                 label: '取消操作',
                                 className: "btn btn-circle"
                            }
                      }
                });
            };
        });


        var crop = {
            updateCoords:function(c){
                var img=document.getElementById("cropbox");
                var ctx=document.getElementById("myCan").getContext("2d");

                ctx.drawImage(img,c.x,c.y, c.w, c.h,0,0,crop.width,crop.height);
            },
            init:function(param){
                var width=800,
                    height=600;
                //img,开始剪切的x,Y坐标宽高，放置图像的x,y坐标宽高。
                //根据param自动计算
                if(param && param["aspectRatio"] && param["width"]){
                  param["aspectRatio"]=parseFloat( param["aspectRatio"] );
                  width=parseFloat(param["width"]);
                  height=width/param["aspectRatio"];
                }
                $("#myCan").attr({
                  width:width+"px",
                  height:height+"px"
                });
                this.width=width;
                this.height=height;

                this.bindEvent();
                this.param=param;
            },
            initJcrop:function(){
                var param=this.param;
                if(!param["aspectRatio"]){
                    param["aspectRatio"]=4/3
                };
                  var c=this;
                  $('#cropbox').Jcrop({
                      onSelect: c.updateCoords, //移动图片上面的选择区域时的回调
                      aspectRatio: param["aspectRatio"],
                      boxWidth: 400,  //画布宽度
                      boxHeight: 300  //画布高度
                  }, function () {
                      //获取图片实际尺寸
                      // var bb = this.getBounds();
                      var bWidth=0,bHeight=0;

                      // this.setSelect([0, 0, bWidth,bHeight]);

                      var ss = this.getWidgetSize();
                      var imgaspt=ss[0]/ss[1];
                      if(param["aspectRatio"]<imgaspt){
                        //以高为准
                        bWidth=ss[1]*param["aspectRatio"];
                        bHeight=ss[1];
                      }else if(param["aspectRatio"]>imgaspt){
                        bWidth=ss[0];
                        bHeight=ss[0]/param["aspectRatio"];
                      }else{
                        bWidth=ss[0];
                        bHeight=ss[1];
                      }
                      var c=this;
                       // setTimeout(function(){
                            c.setSelect([0, 0, 3000,3000]);
                       // },20)

                      // var aheight = (300 - Number(ss[1])) / 2 + "px";
                      // // 设置顶部的
                      // $(".jcrop-holder").css("margin-top", aheight);
                  });
            },
            // 选取图片文件后的回调
            imgchange:function() {

                var localimg = $(this).get(0).files[0];

                if(!localimg){
                    return;
                }

                var fileName = localimg.name;

                filename=fileName;

                var fileSize = localimg.size;
                var fileType=fileName.substring(fileName.lastIndexOf('.'),fileName.length).toLowerCase();
                if(fileType!='.gif' && fileType!='.jpeg' && fileType!='.png' && fileType!='.jpg')
                {
                    alert("上传失败，请上传jpg,png格式的图片");
                    return;
                }

                var reader=new FileReader();
                //将文件读取为DataURL
                reader.readAsDataURL(localimg);
                // 读取文件成功之后
                reader.onload= function (e) {
                    var localimghtml = '<img id="cropbox"   src="' +  e.target.result + '" >';
                    // 写入图片
                    $("#imgfield").html(localimghtml);
                    // 初始化 jcrop
                   setTimeout(function(){
                     crop.initJcrop();
                   },0);
                };

            },
            bindEvent:function(){
                var c=this;

                $("#upload-content .upload").on("click",function(){
                    $("#fileimg").click();
                })

                $("#fileimg").on("change", function () {
                        c.imgchange.call(this);
                });
            }
        };
    } //End bindCrop
};// End utils

