<?php

namespace wumengshan;

class Func
{

    public function __construct()
    {

    }

    /**
     * 判断图片是否全路径
     * author: YJQ
     * @param $url
     * @return bool
     */
    public static function isFullImage($url)
    {
        $parse = parse_url($url);
        if (isset($parse['scheme'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取文件大小
     * Author @YJQ@
     * @param $filesize
     * @return string
     */
    public static function getSize($url) {
        $filesize = strlen(file_get_contents($url));
        if($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            $filesize = $filesize . ' 字节';
        }
        return $filesize;
    }

    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    public static function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = self::hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" alignment-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }

    public static function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }


    /**
     * 将图片下载到本地(微信头像保存)
     * author: YJQ
     * @param $url
     * @return array|false|string
     */
    public static function download($url){
        ob_start();
        readfile($url);
        $img=ob_get_contents();
        ob_end_clean();
        $save_dir = "./uploads/".date("Ymd",time())."/";
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        $new_file = $save_dir .Random::alnum(32).".png";
        $fp2=@fopen($new_file,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        return substr($new_file,1);
    }

    /**
     * 富文本版路径图片拼接全路径
     * author: YJQ
     * @param $content
     * @return mixed
     */
    public static function contentImageUrl($html_content, $host)
    {
        if (preg_match_all("/(<img[^>]+src=\"([^\"]+)\"[^>]*>)|(<a[^>]+href=\"([^\"]+)\"[^>]*>)|(<img[^>]+src='([^']+)'[^>]*>)|(<a[^>]+href='([^']+)'[^>]*>)/i", $html_content, $regs)) {
            foreach ($regs [0] as $num => $url) {
                $html_content = str_replace($url, self::lIIIIl($url, $host), $html_content);
            }
        }
        return $html_content;
    }

    /**
     * 正则匹配
     * author: YJQ
     * @param $l1
     * @param $l2
     * @return string|string[]
     */
    public static function lIIIIl($l1, $l2)
    {
        if (preg_match("/(.*)(href|src)\=(.+?)( |\/\>|\>).*/i", $l1, $regs)) {
            $I2 = $regs [3];
        }
        if (strlen($I2) > 0) {
            $I1 = str_replace(chr(34), "", $I2);
            $I1 = str_replace(chr(39), "", $I1);
        } else {
            return $l1;
        }
        $url_parsed = parse_url($l2);
        $scheme = isset($url_parsed['scheme']) ? $url_parsed ["scheme"] : '';
        if ($scheme != "") {
            $scheme = $scheme . "://";
        }
        $host = isset($url_parsed ["host"]) ? $url_parsed['host'] : '';
        $l3 = $scheme . $host;
        if (strlen($l3) == 0) {
            return $l1;
        }
        $path = isset($url_parsed ["path"]) ? dirname($url_parsed ["path"]) : '' ;
        if(!empty($path)){
            if ($path [0] == "\\") {
                $path = "";
            }
        }
        $pos = strpos($I1, "#");
        if ($pos > 0)
            $I1 = substr($I1, 0, $pos);

        //判断类型
        if (preg_match("/^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\\\.\=\?\+\-~`@\':!%#]|(&amp;)|&)+/i", $I1)) {
            return $l1;
        } //http开头的url类型要跳过
        elseif ($I1 [0] == "/") {
            $I1 = $l3 . $I1;
        } //绝对路径
        elseif (substr($I1, 0, 3) == "../") { //相对路径
            while (substr($I1, 0, 3) == "../") {
                $I1 = substr($I1, strlen($I1) - (strlen($I1) - 3), strlen($I1) - 3);
                if (strlen($path) > 0) {
                    $path = dirname($path);
                }
            }
            $I1 = $l3 . $path . "/" . $I1;
        } elseif (substr($I1, 0, 2) == "./") {
            $I1 = $l3 . $path . substr($I1, strlen($I1) - (strlen($I1) - 1), strlen($I1) - 1);
        } elseif (strtolower(substr($I1, 0, 7)) == "mailto:" || strtolower(substr($I1, 0, 11)) == "javascript:") {
            return $l1;
        } else {
            $I1 = $l3 . $path . "/" . $I1;
        }
        return str_replace($I2, "\"$I1\"", $l1);
    }

    /**
     * 富文本提取文字
     * author: YJQ
     * @param $string
     * @return string
     */
    public static function contentToText($string){
        if($string){
            //把一些预定义的 HTML 实体转换为字符
            $html_string = htmlspecialchars_decode($string);
            //将空格替换成空
            $content = str_replace(" ", "", $html_string);
            //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
            $contents = strip_tags($content);
            //返回字符串中的前$num字符串长度的字符
            return $contents;
        }else{
            return $string;
        }
    }
}
