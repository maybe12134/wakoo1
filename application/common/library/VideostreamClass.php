<?php

namespace app\common\library;
use JamesHeinrich\GetID3\GetID3;


class VideostreamClass
{
    // private $stream = '';
    // private $buffer = 102400;
    // private $start  = -1;
    // private $end    = -1;
    // private $size   = 0;

    // function __construct($filePath)
    // {
    //     $this->path = $filePath;
    // }

    // //打开文件流
    // private function open()
    // {
    //     if (!($this->stream=new \SplFileObject($this->path,'rb'))) {
    //         return json(['code'=>-1,'msg'=>'无法解析视频流']);
    //     }
    // }

    // //设置header头
    // private function setHeader()
    // {
    //     ob_get_clean();
    //     header("Content-Type: video/mp4");
    //     header("Cache-Control: max-age=2592000, public");
    //     header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
    //     header("Last-Modified: ".gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT' );
    //     $this->start = 0;
    //     $file=get_headers($this->path,1);
    //     $this->size = $file['Content-Length'];
    //     $this->end   = $this->size - 1;
    //     header("Accept-Ranges: 0-".$this->end);

    //     if (isset($_SERVER['HTTP_RANGE'])) {
    //         $c_end = $this->end;
    //         list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    //         if (strpos($range, ',') !== false) {
    //             header('HTTP/1.1 416 Requested Range Not Satisfiable');
    //             header("Content-Range: bytes $this->start-$this->end/$this->size");
    //             exit;
    //         }
    //         if ($range == '-') {
    //             $c_start = $this->size - substr($range, 1);
    //         } else {
    //             $range = explode('-', $range);
    //             $c_start = $range[0];

    //             $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
    //         }
    //         $c_end = ($c_end > $this->end) ? $this->end : $c_end;
    //         if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
    //             header('HTTP/1.1 416 Requested Range Not Satisfiable');
    //             header("Content-Range: bytes $this->start-$this->end/$this->size");
    //             exit;
    //         }
    //         $this->start = $c_start;
    //         $this->end = $c_end;
    //         $length = $this->end - $this->start + 1;
    //         fseek($this->stream,$this->start);
    //         header('HTTP/1.1 206 Partial Content');
    //         header("Content-Length: ".$length);
    //         header("Content-Range: bytes $this->start-$this->end/".$this->size);
    //     } else {
    //         //header("Content-Length: ".$this->size);
    //     }
    // }

    // //关闭文件流
    // private function end()
    // {
    //     // fclose($this->stream);
    //     $this->stream=null;
    //     exit;
    // }

    // //执行计算范围的流式处理
    // private function stream()
    // {
    //     $i = $this->start;
    //     set_time_limit(0);
    //     while(!$this->stream->eof() && $i <= $this->end) {
    //         $bytesToRead = $this->buffer;
    //         if(($i+$bytesToRead) > $this->end) {
    //             $bytesToRead = $this->end - $i + 1;
    //         }
    //         $data = $this->stream->fread($bytesToRead);
    //         echo $data;
    //         $this->stream->next();
    //         $this->stream->fflush();
    //         // flush();
    //         $i += $bytesToRead;
    //     }
    // }
    // //开始处理
    // public function start()
    // {
    //     $this->open();
    //     $this->setHeader();
    //     $this->stream();
    //     $this->end();
    // }
//


    
    // function headerHandler($curl, $headerLine) {
    //     $len = strlen($headerLine);
    //     // HTTP响应头是以:分隔key和value的
    //     $split = explode(':', $headerLine, 2);
    //     if (count($split) > 1) {
    //         $key = trim($split[0]);
    //         $value = trim($split[1]);
    //         // 将响应头的key和value存放在全局变量里
    //         $GLOBALS['G_HEADER'][$key] = $value;
    //     }
    //     return $len;
    // }


    // public function bofang($filepath){
    //     set_time_limit(0);
    //     ini_set('max_execution_time', 0);//秒为单位，自己根据需要定义
    //     ini_set("memory_limit",-1);
    //     $ch = curl_init();  
    //     curl_setopt($ch, CURLOPT_URL, $filepath); 
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回 
    //     curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this,"headerHandler"]); // 设置header处理函数
    //     $file = curl_exec($ch); 
    //     curl_close($ch); 
    //     $size=$GLOBALS['G_HEADER']['content-length'];
    //     $length = $size;           // Content length
    //     $start  = 0;               // Start byte
    //     $end    = $size - 1;       // End byte
    //     header('Content-type: video/mp4');
    //     header("Accept-Ranges: 0-$length");
    //     //$_SERVER['HTTP_RANGE'] = 'bytes=0-';  // 会发生2次请求，会携带断点缓存需要加载的视频流 0- 表示从头加载文件尾，8G文件读取会卡
    //     // if (isset($_SERVER['HTTP_RANGE'])) {
    //     //     $c_start = $start;
    //     //     $c_end   = $end;
    //     //     list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    //     //     if (strpos($range, ',') !== false) {
    //     //         header('HTTP/1.1 416 Requested Range Not Satisfiable');
    //     //         header("Content-Range: bytes $start-$end/$size");
    //     //         exit;
    //     //     }
    //     //     if ($range == '-') {
    //     //         $c_start = $size - substr($range, 1);
    //     //     }else{
    //     //         $range  = explode('-', $range);
    //     //         $c_start = $range[0];
    //     //         $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
    //     //     }
    //     //     $c_end = ($c_end > $end) ? $end : $c_end;
    //     //     if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
    //     //         header('HTTP/1.1 416 Requested Range Not Satisfiable');
    //     //         header("Content-Range: bytes $start-$end/$size");
    //     //         exit;
    //     //     }
    //     //     $start  = $c_start;
    //     //     $end    = $c_end;
    //     //     $length = $end - $start + 1;
    //     //     fseek($file,$start);
    //     //     header('HTTP/1.1 206 Partial Content');
    //     // }
    //     //header("Content-Range: bytes $start-$end/$size");
    //     header("Content-Length: ".$length);
    //     $buffer = 1024 * 8;
    //     $strNum = 1024*10; // 如果8G 读取 8kb，估计会循环上百万次，就会陷入无限等待中...
    //     echo $file;
    //     //关闭文件对象
    //     $file = null;
    //     exit();
    // }


   //获取视频长度的php写法 待优化
   public function getid($params){
    $header_array = get_headers($params['video'], true);
    ini_set("memory_limit", '3014M');
    set_time_limit(0);
    $filename = tempnam('../runtime','getid3');
    file_put_contents('text.txt',"\n".date('Y-m-d h:i:s',time()),FILE_APPEND);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $params['video']);
    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, TRUE);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $fp=curl_exec ($ch);
    curl_close($ch);
    file_put_contents('text.txt',"\n".date('Y-m-d h:i:s',time()),FILE_APPEND);
    file_put_contents($filename, $fp);
    file_put_contents('text.txt',"\n".date('Y-m-d h:i:s',time()),FILE_APPEND);
    $a=new GetId3();
    $b=$a->analyze($filename);
    file_put_contents('text.txt',"\n".date('Y-m-d h:i:s',time()),FILE_APPEND);
    unlink($filename);
    dump($b);
    exit;
   }


   
 
//输出视频流
    public function outPutStream($videoUrl) {
        ini_set('memory_limit', '1024M'); //修改脚本的最大运行内存
        set_time_limit(600); //设置超时限制为 10分钟
        if(!$videoUrl){
            header('HTTP/1.1 500 Internal Server Error');
            echo "Error: Video cannot be played !";
            exit();
        }
        
        //获取视频大小
        $header_array = get_headers($videoUrl, true);
        $sizeTemp = $header_array['Content-Length'];
        if (is_array($sizeTemp)) {
            $size = $sizeTemp[count($sizeTemp) - 1];
        } else {
            $size = $sizeTemp;
        }
    
        //初始参数
        $start = 0;
        $end = $size - 1;
        $length = $size;
        $buffer = 1024 * 1024 * 5; // 输出的流大小 5m
        
        //计算 Range
        $ranges_arr = array();
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (!preg_match('/^bytes=\d*-\d*(,\d*-\d*)*$/i', $_SERVER['HTTP_RANGE'])) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
            }
            $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
            foreach ($ranges as $range) {
                $parts = explode('-', $range);
                $ranges_arr[] = array($parts[0], $parts[1]);
            }
            $ranges = $ranges_arr[0];
            $start = (int)$ranges[0];
            if ($ranges[1] != '') {
                $end = (int)$ranges[1];
            }
            $length = min($end - $start + 1, $buffer);
            $end = $start + $length - 1;
        }else{
            
            // php 文件第一次浏览器请求不会携带 RANGE 为了提升加载速度 默认请求 1 个字节的数据
            $start=0;
            $end=1;
            $length=2;
        }
    
        //添加 Range 分段请求
        $header = array("Range:bytes={$start}-{$end}");
        #发起请求
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $videoUrl);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, $header);
        //设置读取的缓存区大小
        curl_setopt($ch2, CURLOPT_BUFFERSIZE, $buffer);
        // 关闭安全认证
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
        //追踪返回302状态码，继续抓取
        curl_setopt($ch2, CURLOPT_HEADER, false);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch2, CURLOPT_NOBODY, false);
        curl_setopt($ch2, CURLOPT_REFERER, $videoUrl);
        //模拟来路
        curl_setopt($ch2, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.83 Safari/537.36 Edg/85.0.564.44");
        $content = curl_exec($ch2);
        curl_close($ch2);
        #设置响应头
        header('HTTP/1.1 206 PARTIAL CONTENT');
        header("Accept-Ranges: bytes");
        header("Connection: keep-alive");
        header("Content-Type: video/mp4");
        // header("Content-type: application/octet-stream");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Expose-Headers: Content-Range");
        //为了兼容 ios UC这类浏览器 这里加个判断 UC的 Content-Range 是 起始值-总大小减一
        if($end!=1){
            $end=$size-1;
        }
        header("Content-Range: bytes {$start}-{$end}/{$size}");
        //设置流的实际大小
        header("Content-Length: ".strlen($content));
        //清空缓存区
        ob_clean();
        //输出视频流
        echo $content;
        //销毁内存
        unset($content);
    }


    public function open(){
        //输出视频流 视频地址可能失效，您可以换成你的来测试
        $this->outPutStream("https://cdn.wakoohome.top/new.mp4");
        die();
    }


}