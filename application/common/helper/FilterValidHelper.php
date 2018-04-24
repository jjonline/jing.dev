<?php
/**
 * 常用表单过滤和检测函数
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2017-08-03
 *
 */

namespace app\common\helper;

class FilterValidHelper
{

    /**
     * 检测传入的变量是否为合法邮箱 提供两种方法 可选内置fliter函数 
     * 默认正则[邮箱用户名(即@符号之前的部分)构成部分为数字、字母、下划线、中划线和点均可，且开头必须是数字或字母]
     * @param  string $mail
     * @return boolean
     */
    public static function is_mail_valid($mail)
    {
        # PHP内置filter_var方式较为宽泛 不予采用
        /* !"#$%&'*+-/0123456789=?@ABCDEFGHIJKLMNOPQRSTUVWXYZ^_ `abcdefghijklmnopqrstuvwxyz{|}~ 的类型均正确
         也就是说 这种格式的邮箱 JJon#?`!#$%&'*+-/line@JJonline.Cn 也会被filter_var认为是合法邮箱 不符合人类认知 暂不采用
         详见：http://www.cs.tut.fi/~jkorpela/rfc/822addr.html
        */
        #return !!filter_var($mail,FILTER_VALIDATE_EMAIL);
        #正则方式 '/^\w+(?:[-+.]\w+)*@\w+(?:[-.]\w+)*\.\w+(?:[-.]\w+)*$/' 邮箱域名顶级后缀至少两个字符
        return preg_match('/^\w+(?:[-+.]\w+)*@\w+(?:[-.]\w+)*\.\w{2,}$/',$mail) === 1;
    }

    /**
     * 检测传入的变量是否为天朝手机号
     * @param  mixed $phone
     * @return boolean
     */
    public static function is_phone_valid($phone)
    {
        #Fixed 171 170x
        #详见：http://digi.163.com/15/0812/16/B0R42LSH00162OUT.html
        #2017-8-13新增196、198、199号段 http://www.miinac.gov.cn/components/Notice.action?doType=view&id=150951658150000389527
        return preg_match('/^13[\d]{9}$|14^[0-9]\d{8}$|^15[0-9]\d{8}$|^166\d{8}$|^170[015789]\d{7}$|^171[89]\d{7}$|^17[34678]\d{8}$|^18[0-9]\d{8}$|^198\d{8}$|^199\d{8}$/',$phone) === 1;
    }

    /**
     * 检测Url是否为合法的http或https链接
     * --------
     * 1、仅检测http、https打头的网址字符串
     * 2、网址中可带端口号
     * 3、网址中可带get变量
     * 4、网址中可带锚点
     * --------
     * @param  mixed $url
     * @return boolean
     */
    public static function is_url_valid($url)
    {
        return preg_match('/^http[s]?:\/\/(?:(?:[0-9]{1,3}\.){3}[0-9]{1,3}|(?:[0-9a-z_!~*\'()-]+\.)*(?:[0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(?::[0-9]{1,4})?(?:(?:\/\?)|(?:\/[0-9a-zA-Z_!~\*\'\(?:\)\.;\?:@&=\+\$,%#-\/]*)?)$/i',$url) === 1;
    }

    /**
     * 检测传入的变量是否为一个合法的账户id
     * ---------
     * 提供两种方法:函数方法和正则方法
     * 默认数字ID长度为4至11位
     * ---------
     * @param  mixed $uid       待检测的数字ID
     * @param  int   $minLength 允许的uid最短位数 默认4
     * @param  int   $maxLength 允许的uid最长位数 默认11
     * @return boolean
     */
    public static function is_uid_valid($uid, $minLength = 4, $maxLength = 11)
    {
        #正则方式
        return preg_match('/^[1-9]\d{'.( $minLength - 1 ).','.( $maxLengt - 1 ).'}$/',$uid) === 1;
        #函数方式 可能未编译ctype扩展不存在ctype_digit内置函数
        // return strlen($uid)>=$minLength && strlen($uid)<=$maxLength && ctype_digit((string)$uid);
    }

    /**
     * 检测传入的变量是否为一个合法的账户密码
     * ---------
     * 1、必须同时包含数字和字母
     * 2、通过第二个参数指定最小长度，默认值6
     * 3、通过第三个可选参数指定最大长度，默认值18
     * ---------
     * @param  string $password 需要被判断的字符串
     * @param  int $minLength 允许的账户密码最短位数 默认6
     * @param  int $maxLength 允许的账户密码最长位数 默认16
     * @return boolean
     */
    public static function is_password_valid($password, $minLength = 6, $maxLength = 18)
    {
        if(strlen($password) > $maxLength || strlen($password) < $minLength)
        {
            return false;
        }
        return preg_match('/\d{1,'.$maxLength.'}/',$password) === 1 && preg_match('/[a-zA-Z]{1,'.$maxLength.'}/',$password) === 1;
    }

    /**
     * 检查字符串是否是UTF8编码下的中文
     * @param  string $string 字符串
     * @return boolean
     */
    public static function is_chinese_valid($str)
    {
        return preg_match("/[\x{4e00}-\x{9fa5}]+/u", $str) === 1;
    }

    /**
     * 检查字符串是否是UTF8编码
     * @param  string $string 字符串
     * @return boolean
     */
    public static function is_utf8_valid($str)
    {
        $c    = 0;
        $b    = 0;
        $bits = 0;
        $len  = strlen($str);
        for($i = 0; $i<$len; $i++)
        {
            $c = ord($str[$i]);
            if($c > 128){
                if(($c >= 254)) return false;
                elseif($c >= 252) $bits=6;
                elseif($c >= 248) $bits=5;
                elseif($c >= 240) $bits=4;
                elseif($c >= 224) $bits=3;
                elseif($c >= 192) $bits=2;
                else return false;
                if(($i+$bits) > $len) return false;
                while($bits > 1){
                    $i++;
                    $b = ord($str[$i]);
                    if($b < 128 || $b > 191) return false;
                    $bits--;
                }
            }
        }
        return true;
    }

    /**
     * 检测传入的变量是否为一个合法的天朝身份证号（15位、18位兼容）
     * @param  mixed $citizen_id
     * @return bool | array
     */
    public static function is_citizen_id_valid($citizen_id)
    {
        $id                 =   strtoupper($citizen_id);
        if(!(preg_match('/^\d{17}(\d|X)$/',$id) || preg_match('/^\d{15}$/',$id)))
        {
            return false;
        }
        # 15位老号码转换为18位
        $Wi                 =   array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1); 
        $Ai                 =   array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
        $cardNoSum          =   0;
        if(strlen($id)==16)
        {
            $id             =   substr(0, 6).'19'.substr(6, 9); 
            for($i = 0; $i < 17; $i++) {
                $cardNoSum +=   substr($id,$i,1) * $Wi[$i];
            }  
            $seq            =   $cardNoSum % 11; 
            $id             =   $id.$Ai[$seq];
        }
        # 效验18位身份证最后一位字符的合法性
        $cardNoSum          =   0;
        $id17               =   substr($id,0,17);
        $lastString         =   substr($id,17,1);
        for($i = 0; $i < 17; $i++)
        {
            $cardNoSum     +=   substr($id,$i,1) * $Wi[$i];
        }  
        $seq                =   $cardNoSum % 11;
        $realString         =   $Ai[$seq];
        # 最后一位效验失败 不是合法身份证号
        if($lastString     !=   $realString) {
            return false;
        }
        # 地域仅能精确到省、自治区信息，再往下就需大量数据支撑才能精确
        $oProvice   = array(
                11 => "北京",
                12 => "天津",
                13 => "河北",
                14 => "山西",
                15 => "内蒙古",
                21 => "辽宁",
                22 => "吉林",
                23 => "黑龙江",
                31 => "上海",
                32 => "江苏",
                33 => "浙江",
                34 => "安徽",
                35 => "福建",
                36 => "江西",
                37 => "山东",
                41 => "河南",
                42 => "湖北 ",
                43 => "湖南",
                44 => "广东",
                45 => "广西",
                46 => "海南",
                50 => "重庆",
                51 => "四川",
                52 => "贵州",
                53 => "云南",
                54 => "西藏",
                61 => "陕西",
                62 => "甘肃",
                63 => "青海",
                64 => "宁夏",
                65 => "新疆",
                71 => "台湾",
                81 => "香港",
                82 => "澳门",
                91 => "国外"
        );
        $Provice    = substr($id, 0, 2);
        $BirthYear  = substr($id, 6, 4);
        $BirthMonth = substr($id, 10, 2);
        $BirthDay   = substr($id, 12, 2);
        $Sex        = substr($id, 16,1) % 2 ;//男1 女0
        # 省份数据
        if(!isset($oProvice[$Provice]))
        {
            return false;
        }
        # 年份超限
        if($BirthYear > 2078 || $BirthYear < 1900)
        {
            return false;
        }
        # 年月日是否合法
        $RealDate           =   strtotime($BirthYear.'-'.$BirthMonth.'-'.$BirthDay);
        if(date('Y',$RealDate) != $BirthYear || date('m',$RealDate) != $BirthMonth || date('d',$RealDate) != $BirthDay)
        {
            return false;
        }
        # 效验成功 返回关联数组，便于从身份证号中提取基本信息 boolean判断为true
        return array('id'=>$id,'location'=>$oProvice[$Provice],'Y'=>$BirthYear,'m'=>$BirthMonth,'d'=>$BirthDay,'sex'=>$Sex);
    }

    /**
     * 清理html内容中的js代码和各种标签内包裹的onXX事件
     * 直接清理掉所有标签内属性即可
     * {
     *     1、清理所有js代码
     *     2、清理所有标签内属性性质的js事件
     * }
     * @param  string $content 待清理的html文本
     * @return string 清理妥善的html文本
     */
    public static function clear_js_code($content)
    {
        ##去除所有JavaScript代码
        $content = preg_replace('/<script(.*?)<\/?script>/is', "", $content);
        ##去除所有a标签
        // $content = preg_replace('/<\/?a[^>]>/', '', $content); ##a标签相对危害小一些，依据实际情况取消注释
        ## 去除标签内的各种事件属性(以on开头的全部干掉，可能会误杀)，保留非事件属性
        return preg_replace_callback('/<(\w+)\s+([^>]+)>/i', function ($match) {
            // self::dump($match);
            // 匹配出所有的属性对
            $prop = preg_split('/\s+/', trim($match[2]));
            // 去除所有属性中以on开头的属性名和属性值
            foreach ($prop as $key => $value) {
                if(preg_match('/^on/', $value))
                {
                    unset($prop[$key]);
                }
            }
            // self::dump($prop);
            return empty($prop) ? '<'.$match[1].'>' : '<'.$match[1].' '.implode(' ', $prop).'>';
            /**
             * 2017-8-7 Bug
             * 
             * 下面这种方式可以规避清理掉onxx事件，例如：
             * <p onclick="alert("dd")" oonload="ds" nloadonload="ds" ="load">string</p>
             * 
             * $Attribute = trim(preg_replace('/on\w+=.*?\s/is', '', $match[2].' '));
             * return empty($Attribute) ? '<'.$match[1].'>' : '<'.$match[1].' '.$Attribute.'>';
             */
        }, $content);
    }

    /**
     * 将相对url转换为绝对完整Url
     * <code>
     *     将某一个Url（当前Url）页面中的超链接不同的写法转换为实际完整的Url
     *     
     *     例如1、当前Url为：
     *         http://blog.jjonline.cn/phptech/172.html，该页面中超链接Url为：/view/173.html
     *         则该超链接Url的实际完整Url为：http://blog.jjonline.cn/view/173.html
     *     例如2、当前Url为：
     *         http://blog.jjonline.cn/phptech/172.html，待转换Url为：./173.html 或 173.html
     *         则待转换Url的实际完整Url为：http://blog.jjonline.cn/phptech/173.html
     *     例如3、当前Url为：
     *         http://blog.jjonline.cn/phptech/172.html，待转换Url为：../view/173.html
     *         则待转换Url的实际完整Url为：http://blog.jjonline.cn/view/173.html
     *     例如4、当前Url为：
     *         http://blog.jjonline.cn/phptech/view/172.html，待转换Url为：./../../173.html
     *         则待转换Url的实际完整Url为：http://blog.jjonline.cn/173.html
     *         
     *     当然第3种和第4种比较变态，但这种Url也是可能存在的
     * </code>
     * @param  string $sUrl    页面中的Url，例如：./../../171.html
     * @param  string $baseUrl 该页面的Url，例如：http://blog.jjonline.cn/sort/php/area/article/173.html
     * @return string
     */
    public static function to_absolute_url($sUrl,$baseUrl) 
    {
        $srcinfo = parse_url($sUrl);
        if(isset($srcinfo['scheme'])) {
            ##完整的Url无需转换
            return $sUrl;
        }
        $baseinfo = parse_url($baseUrl);
        $url      = $baseinfo['scheme'].'://'.$baseinfo['host'];##识别出基础的根Url
        ##识别出待转换Url中的路径部分
        if(substr($srcinfo['path'], 0, 1) == '/') {
            $path   = $srcinfo['path'];
        }else{
            $path   = dirname($baseinfo['path']).'/'.$srcinfo['path'];
        }
        $rst        = array();##保存待转换Url中的路径部分，索引数组，一个元素是一个文件夹名或.和.. 下方对.和..进行替换
        $path_array = explode('/', $path);
        if(!$path_array[0]) {
            $rst[]  = '';
        }
        foreach ($path_array as $key => $dir) {
            if ($dir == '..')
            {
                if (end($rst) == '..')
                {
                    $rst[] = '..';
                }elseif(!array_pop($rst)) {
                    $rst[] = '..';
                }
            }elseif($dir && $dir != '.') {
                $rst[]     = $dir;
            }
        }
        if(!end($path_array)) {
            $rst[] = '';
        }
        $url .= implode('/', $rst);
        return str_replace('\\', '/', $url);
    }

    /**
     * 将一个Unix时间戳转换成“xx前”模糊时间表达方式
     * @param  mixed $timestamp Unix时间戳
     * @return boolean
     */
    public static function time_ago($timestamp)
    {
        $etime = time() - $timestamp;
        if ($etime < 1) return '刚刚';
        $interval = array (
            12 * 30 * 24 * 60 * 60  =>  '年前 ('.date('Y-m-d', $timestamp).')',
            30 * 24 * 60 * 60       =>  '个月前 ('.date('m-d', $timestamp).')',
            7 * 24 * 60 * 60        =>  '周前 ('.date('m-d', $timestamp).')',
            24 * 60 * 60            =>  '天前',
            60 * 60                 =>  '小时前',
            60                      =>  '分钟前',
            1                       =>  '秒前'
        );
        foreach ($interval as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . $str;
            }
        }
    }

    /**
     * 隐藏ip v4地址的中间两位
     * @param  string $ip_v4 ipV4的地址
     * @return string 处理隐藏后的地址
     */
    public static function hide_ipv4($ip_v4)
    {
        $ip = explode('.', $ip_v4);
        if(count($ip) == 4)
        {
            $ip[1] = '**';
            $ip[2] = '**';
            return implode('.', $ip);
        }
        return $ip_v4;
    }
    /**
     * nl2br的类似函数，将(多个)换行替换成p标签
     * @param  string $str
     * @return string
     */
    public static function nl2p($str)
    {
        $str = str_replace(array('<p>', '</p>', '<br>', '<br/>', '<br />'), '', $str);
        return '<p>'.preg_replace("/([\n|\r\n|\r]{1,})/i", "</p>\n<p>", trim($str)).'</p>';
    }

    /**
     * 用户名加星隐藏核心信息
     * @param  string $nickname 用户名、昵称
     * @return string 隐藏处理后的用户名、昵称
     */
    public static function hide_name($nickname)
    {
        if(mb_strlen($nickname) <=4)
        {
            return '***';
        }
        // 手机号隐藏中间4位
        if(self::is_phone_valid($nickname))
        {
            return self::hide_phone($nickname);
        }
        // 隐藏中间4位
        $begin_len = intval(ceil((mb_strlen($nickname) - 4) / 2));
        $replace   = mb_substr($nickname,$begin_len,4,'utf8');
        return self::mb_str_replace($replace,'****',$nickname);
    }

    /**
     * 隐藏手机号中间4位\\方法体本身不检测传入参数是否为手机号，请调用静态方法is_phone_valid提前检测
     * @param $phone
     * @return string
     */
    public static function hide_phone($phone)
    {
        return substr_replace($phone,'****',3,4);
    }

    /**
     * 多字节字符串替换
     * @param mixed $search
     * @param mixed $replace
     * @param mixed $subject
     * @param int $count
     * @return bool|string
     */
    public static function mb_str_replace($search, $replace, $subject, &$count=0) {
        if (!is_array($search) && is_array($replace)) {
            return false;
        }
        if (is_array($subject)) {
            // call mb_replace for each single string in $subject
            foreach ($subject as &$string) {
                $string = self::mb_str_replace($search, $replace, $string, $c);
                $count += $c;
            }
        } elseif (is_array($search)) {
            if (!is_array($replace)) {
                foreach ($search as &$string) {
                    $subject = self::mb_str_replace($string, $replace, $subject, $c);
                    $count += $c;
                }
            } else {
                $n = max(count($search), count($replace));
                while ($n--) {
                    $subject = self::mb_str_replace(current($search), current($replace), $subject, $c);
                    $count += $c;
                    next($search);
                    next($replace);
                }
            }
        } else {
            $parts   = mb_split(preg_quote($search), $subject);
            $count   = count($parts)-1;
            $subject = implode($replace, $parts);
        }
        return $subject;
    }
}
