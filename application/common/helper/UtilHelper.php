<?php
/**
 *
 * @user Jea杨 (JJonline@JJonline.Cn)
 * @date 2018-04-28 12:13
 * @file UtilHelper.php
 */

namespace app\common\helper;

use think\Exception;
use think\facade\Cache;
use think\facade\Log;

class UtilHelper
{
    /**
     * 检查用户是否处于被防御状态
     * @param int $user_id
     * @return bool
     */
    public static function isInDefense($user_id)
    {
        $defense_key   = '__LoginDefense__'.$user_id;
        $defense_times = Cache::get($defense_key);
        if(empty($defense_times))
        {
            $defense_times = 0;
        }
        return $defense_times >= 10;
    }

    /**
     * 登录密码错误防御
     * ---
     * 密码错误次数超过10次则抛出异常
     * ---
     * @param int $user_id
     * @param bool $is_release
     * @return bool
     * @throws Exception
     */
    public static function loginDefense($user_id)
    {
        $defense_key   = '__LoginDefense__'.$user_id;
        $defense_times = Cache::get($defense_key);
        if(empty($defense_times))
        {
            $defense_times = 0;
        }
        $defense_times++;//自增记录防御次数
        Cache::set($defense_key,$defense_times,900);//缓存15分钟
        if($defense_times >= 10)
        {
            throw new Exception('密码连续错误次数过多，请15分钟后再试');
        }
    }

    /**
     * 释放对指定用户的登录防御
     * @param int $user_id
     * @return bool
     */
    public static function releaseDefense($user_id)
    {
        $defense_key  = '__LoginDefense__'.$user_id;
        return Cache::rm($defense_key);
    }

    /**
     * 导出字段
     * @param array $selected 选择的字段
     * @param array $map 所有字段数组
     * @return array
     */
    public static function getExportField(array $selected,array $map)
    {
        //导出的字段
        $export_field = [];
        foreach ($map as $key => $value)
        {
            foreach ($selected as $_key => $_value)
            {
                if ($value == $_value)
                {
                    $index = explode('.',$key);
                    $new_key = $index[1];
                    $export_field[] = [$new_key,$_value];
                }
            }
        }
        return $export_field;
    }

    /**
     * 根据字段排序索引获取Excel的字表头字母
     * ---
     * A~Z、AA~AZ、BA~BZ ...
     * ---
     * @param int $idx 从0开始的索引数字
     * @return string
     */
    public static function getColumnsCharByIndex($idx)
    {
        if($idx >= 0 && $idx <= 25)
        {
            $char_idx = $idx + 65;
            return chr($char_idx);
        }
        $ary    = floor(($idx + 1) / 26);
        $prefix = chr($ary + 64);
        $suffix = chr( $idx - $ary * 25 + 64);
        return $prefix.$suffix;
    }

    /**
     * 数字左侧补0至指定位数，默认7位
     * @param $number
     * @param int $length
     * @return string
     */
    public static function leftZeroPadding($number,$length = 7)
    {
        return str_pad($number,$length,'0',STR_PAD_LEFT);
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
        $src_info  = parse_url($sUrl);
        if(isset($src_info['scheme'])) {
            ##完整的Url无需转换
            return $sUrl;
        }
        $base_info  = parse_url($baseUrl);
        $url        = $base_info['scheme'].'://'.$base_info['host'];##识别出基础的根Url
        ##识别出待转换Url中的路径部分
        if(substr($src_info['path'], 0, 1) == '/') {
            $path   = $src_info['path'];
        }else{
            $path   = dirname($base_info['path']).'/'.$src_info['path'];
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
        $e_time = time() - $timestamp;
        if ($e_time < 1) return '刚刚';
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
            $d = $e_time / $secs;
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
        if(mb_strlen($nickname) <= 4)
        {
            return '***';
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

    /**
     * userAgent获取客户操作系统信息
     * @param string $user_agent
     * @return string
     */
    public static function get_os_info($user_agent = null)
    {
        $agent = empty($user_agent) ? $_SERVER['HTTP_USER_AGENT'] : $user_agent;//获取用户代理字符串
        $ret   = self::parse_user_agent($agent);
        if(!empty($ret) && !empty($ret['platform']) && $ret['platform'] != 'Windows')
        {
            return $ret['platform'];
        }
        if(preg_match('/Mobile/i',$agent))
        {
            $os = '移动设备';
            if(preg_match('/Android/i',$agent))
            {
                $os = 'Android';
            }
            if(preg_match('/iPhone/i',$agent))
            {
                $os = 'iPhone';
            }
        }else if (preg_match('/win/i', $agent) && strpos($agent, '95'))
        {
            $os = 'Windows 95';
        }
        else if(preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
        {
            $os = 'Windows ME';
        }
        else if(preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
        {
            $os = 'Windows 98';
        }
        else if(preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
        {
            $os = 'Windows Vista';
        }
        else if(preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
        {
            $os = 'Windows 7';
        }
        else if(preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
        {
            $os = 'Windows 8';
        }else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
        {
            $os = 'Windows 10';
        }else if(preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
        {
            $os = 'Windows XP';
        }
        else if(preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
        {
            $os = 'Windows 2000';
        }
        else if(preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
        {
            $os = 'Windows NT';
        }
        else if(preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
        {
            $os = 'Windows 32';
        }
        else if(preg_match('/linux/i', $agent))
        {
            $os = 'Linux';
        }
        else if(preg_match('/unix/i', $agent))
        {
            $os = 'Unix';
        }
        else if(preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
        {
            $os = 'SunOS';
        }
        else if(preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
        {
            $os = 'IBM OS/2';
        }
        else if(preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
        {
            $os = 'iMac Pc';
        }
        else if(preg_match('/Mac/i', $agent) && preg_match('/OS\s+X/i', $agent))
        {
            $os = 'Mac';
        }
        else if(preg_match('/PowerPC/i', $agent))
        {
            $os = 'PowerPC';
        }
        else if(preg_match('/AIX/i', $agent))
        {
            $os = 'AIX';
        }
        else if(preg_match('/HPUX/i', $agent))
        {
            $os = 'HPUX';
        }
        else if(preg_match('/NetBSD/i', $agent))
        {
            $os = 'NetBSD';
        }
        else if(preg_match('/BSD/i', $agent))
        {
            $os = 'BSD';
        }
        else if(preg_match('/OSF1/i', $agent))
        {
            $os = 'OSF1';
        }
        else if(preg_match('/IRIX/i', $agent))
        {
            $os = 'IRIX';
        }
        else if(preg_match('/FreeBSD/i', $agent))
        {
            $os = 'FreeBSD';
        }
        else if(preg_match('/teleport/i', $agent))
        {
            $os = 'teleport';
        }
        else if(preg_match('/flashget/i', $agent))
        {
            $os = 'flashget';
        }
        else if(preg_match('/webzip/i', $agent))
        {
            $os = 'webzip';
        }
        else if(preg_match('/offline/i', $agent))
        {
            $os = 'offline';
        }
        else
        {
            Log::record('无法识别操作系统信息的UserAgent:'.$agent);
            $os = '未知操作系统';
        }
        return $os;
    }

    /**
     * useAgent获取客户端浏览器信息
     * @param string $user_agent 浏览器userAgent
     * @return string
     */
    public static function get_browser_info($user_agent = null)
    {
        $sys = empty($user_agent) ? $_SERVER['HTTP_USER_AGENT'] : $user_agent;//获取用户代理字符串
        $ret = self::parse_user_agent($sys);
        if(!empty($ret) && !empty($ret['browser']) && !empty($ret['version']))
        {
            return $ret['browser'].'('.$ret['version'].')';
        }
        if(preg_match('/Mobile/i',$sys))
        {
            $exp[0] = '移动浏览器';
            $exp[1] = '';
            if(preg_match('/Android/i',$sys))
            {
                if(preg_match('/MicroMessenger/i',$sys))
                {
                    $exp[0] = 'Android微信浏览器';
                }else if(preg_match('/MQQBrowser/i',$sys)) {
                    $exp[0] = '安卓QQ浏览器';
                }else if(preg_match('/UCBrowser/i',$sys)) {
                    $exp[0] = '安卓UC浏览器';
                }else if(stripos($sys, "Chrome") > 0){
                    $exp[0] = 'Chrome内核浏览器';
                }else {
                    $exp[0] = '安卓浏览器';
                }
            }
            if(preg_match('/iPhone/i',$sys) && preg_match('/MicroMessenger/i',$sys))
            {
                if(preg_match('/MicroMessenger/i',$sys))
                {
                    $exp[0] = 'iPhone微信浏览器';
                }else if(preg_match('/MQQBrowser/i',$sys)) {
                    $exp[0] = 'iPhoneQQ浏览器';
                }else if(preg_match('/UCBrowser/i',$sys)) {
                    $exp[0] = 'iPhoneUC浏览器';
                }else if(stripos($sys, "Chrome") > 0){
                    $exp[0] = 'Chrome内核浏览器';
                }else{
                    $exp[0] = 'iPhone浏览器';
                }
            }
        }else if (stripos($sys, "Firefox/") > 0) {
            preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
            $exp[0] = "Firefox";
            $exp[1] = $b[1];  //获取火狐浏览器的版本号
        } else if(stripos($sys, "Maxthon") > 0) {
            preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
            $exp[0] = "傲游";
            $exp[1] = $aoyou[1];
        } else if(stripos($sys, "MSIE") > 0) {
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp[0] = "IE";
            $exp[1] = $ie[1];  //获取IE的版本号
        } else if(stripos($sys, "OPR") > 0) {
            preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
            $exp[0] = "Opera";
            $exp[1] = $opera[1];  //获取opera浏览器版本号,今天下载一个opera浏览器做测试，发现opera竟然也换成谷歌的内核了，囧
        } else if(stripos($sys, "Edge") > 0) {
            //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
            preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
            $exp[0] = "Edge";
            $exp[1] = $Edge[1];
        } else if(preg_match("/Version\/(\d+\.\d+\.\d)\s+Safari/", $sys, $safari)) {
            // safari
            $exp[0] = "Safari";
            $exp[1] = $safari[1];
        } else if (stripos($sys, "Chrome") > 0) {
            preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
            $exp[0] = "Chrome";
            $exp[1] = $google[1];  //获取google chrome的版本号
        } else if(stripos($sys,'rv:')>0 && stripos($sys,'Gecko')>0){
            preg_match("/rv:([\d\.]+)/", $sys, $IE);
            $exp[0] = "IE";
            $exp[1] = $IE[1];
        }else {
            Log::record('无法识别浏览器版本的UserAgent:'.$sys);
            $exp[0] = "未知浏览器";
            $exp[1] = '';
        }
        return empty($exp[1]) ? $exp[0] : $exp[0].'('.$exp[1].')';
    }

    /**
     * 分析浏览器头信息
     * @param string $u_agent
     * @return array
     */
    public static function parse_user_agent( $u_agent = null ) {
        $u_agent  = empty($u_agent) ? $_SERVER['HTTP_USER_AGENT'] : $u_agent;
        $platform = null;
        $browser  = null;
        $version  = null;
        $empty    = ['platform' => $platform, 'browser' => $browser, 'version' => $version];
        if( !$u_agent )
        {
            return $empty;
        }
        if( preg_match('/\((.*?)\)/im', $u_agent, $parent_matches) ) {
            preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|(Open|Net|Free)BSD|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS|Switch)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);
            $priority = array( 'Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'FreeBSD', 'NetBSD', 'OpenBSD', 'CrOS', 'X11' );
            $result['platform'] = array_unique($result['platform']);
            if( count($result['platform']) > 1 ) {
                if( $keys = array_intersect($priority, $result['platform']) ) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif( isset($result['platform'][0]) ) {
                $platform = $result['platform'][0];
            }
        }
        if( $platform == 'linux-gnu' || $platform == 'X11' ) {
            $platform = 'Linux';
        } elseif( $platform == 'CrOS' ) {
            $platform = 'Chrome OS';
        }
        preg_match_all('%(?P<browser>MiuiBrowser|MicroMessenger|MQQBrowser|QQBrowser|Maxthon|Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
				TizenBrowser|Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|Puffin|SamsungBrowser|
				Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
				Valve\ Steam\ Tenfoot|
				NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
				(?:\)?;?)
				(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
            $u_agent, $result, PREG_PATTERN_ORDER);
        // If nothing matched, return null (to avoid undefined index errors)
        if( !isset($result['browser'][0]) || !isset($result['version'][0]) )
        {
            if( preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result) )
            {
                return array(
                    'platform' => $platform ?: null,
                    'browser'  => $result['browser'],
                    'version'  => isset($result['version']) ? $result['version'] ?: null : null
                );
            }
            return $empty;
        }
        if( preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $u_agent, $rv_result) )
        {
            $rv_result = $rv_result['version'];
        }
        // dump($result);
        $browser      = $result['browser'][0];
        $version      = $result['version'][0];
        $lowerBrowser = array_map('strtolower', $result['browser']);
        $find = function ( $search, &$key, &$value = null ) use ( $lowerBrowser ) {
            $search = (array)$search;
            foreach( $search as $val ) {
                $xkey = array_search(strtolower($val), $lowerBrowser);
                if( $xkey !== false ) {
                    $value = $val;
                    $key   = $xkey;
                    return true;
                }
            }
            return false;
        };
        $key = 0;
        $val = '';
        if( $browser == 'Iceweasel' || strtolower($browser) == 'icecat' ) {
            $browser = 'Firefox';
        } elseif( $find('Playstation Vita', $key) ) {
            $platform = 'PlayStation Vita';
            $browser  = 'Browser';
        } elseif( $find(array( 'Kindle Fire', 'Silk' ), $key, $val) ) {
            $browser  = $val == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if( !($version = $result['version'][$key]) || !is_numeric($version[0]) ) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif( $find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS' ) {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif( $find('Kindle', $key, $platform) ) {
            $browser = $result['browser'][$key];
            $version = $result['version'][$key];
        } elseif( $find('OPR', $key) ) {
            $browser = 'Opera';
            $version = $result['version'][$key];
        } elseif( $find('Opera', $key, $browser) ) {
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif( $find('Puffin', $key, $browser) ) {
            $version = $result['version'][$key];
            if( strlen($version) > 3 ) {
                $part = substr($version, -2);
                if( ctype_upper($part) ) {
                    $version = substr($version, 0, -2);
                    $flags   = [
                        'IP' => 'iPhone',
                        'IT' => 'iPad',
                        'AP' => 'Android',
                        'AT' => 'Android',
                        'WP' => 'Windows Phone',
                        'WT' => 'Windows'
                    ];
                    if( isset($flags[$part]) ) {
                        $platform = $flags[$part];
                    }
                }
            }
        }elseif ($find('MiuiBrowser',$key)) {
            $browser = 'XiaoMi';
            $version = $result['version'][$key];
        }elseif ($find('MicroMessenger',$key)) {
            $browser = '微信';
            $version = $result['version'][$key];
        }elseif ($find('Maxthon',$key)) {
            $browser = '遨游';
            $version = $result['version'][$key];
        }elseif ($find('MSIE',$key)) {
            $browser = 'IE';
            $version = $result['version'][$key];
        }elseif ($find(['MQQBrowser','QQBrowser'],$key)) {
            $browser = 'QQ浏览器';
            $version = $result['version'][$key];
        }elseif( $find(['IEMobile', 'Edge', 'Midori', 'Vivaldi', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome'], $key, $browser) ) {
            $version = $result['version'][$key];
        } elseif( $rv_result && $find('Trident', $key) ) {
            $browser = 'IE';
            $version = $rv_result;
        } elseif( $find('UCBrowser', $key) ) {
            $browser = 'UC浏览器';
            $version = $result['version'][$key];
        } elseif( $find('CriOS', $key) ) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif( $browser == 'AppleWebKit' ) {
            if( $platform == 'Android' && !($key = 0) ) {
                $browser = 'Android浏览器';
            } elseif( strpos($platform, 'BB') === 0 ) {
                $browser  = 'BlackBerry浏览器';
                $platform = 'BlackBerry';
            } elseif( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
                $browser = 'BlackBerry Browser';
            } else {
                $find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
            }
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif( $pKey = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser'])) ) {
            $pKey = reset($pKey);
            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $pKey);
            $browser  = 'NetFront';
        }
        return [
            'platform' => $platform ?: null,
            'browser'  => $browser ?: null,
            'version'  => $version ?: null
        ];
    }

}
