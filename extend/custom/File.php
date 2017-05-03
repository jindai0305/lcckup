<?php

namespace custom;
/**
 * File
 */
class File {
    /**
     * 转换字节大小
     * @param number $size
     * @return number
     */
    public function transByte($size) {
        $arr = array ("B", "KB", "MB", "GB", "TB", "EB" );
        $i = 0;
        while ( $size >= 1024 ) {
            $size /= 1024;
            $i ++;
        }
        return round ( $size, 2 ) . $arr [$i];
    }

    /**
     * 创建文件
     * @param string $filename
     * @return string
     */
    public function createFile($path,$filename) {
        $result = array();
        if ($this->checkFilename($filename)) {
            if (! file_exists ($path.'/'.$filename )) {
                //通过touch($filename)来创建
                if (touch ($path.'/'.$filename )) {
                    $result['success'] = true;
                } else {
                    $result['success'] = false;
                    $result['msg'] = "文件新建失败,权限不足";
                }
            }else{
                $result['success'] = false;
                $result['msg'] = "文件已存在，请重命名后创建";
            }
        }else{
            $result['success'] = false;
            $result['msg'] = "非法文件名";
        }
        return $result;
    }

    /**
     * 重命名文件
     * @param string $oldname
     * @param string $newname
     * @return string
     */
    public function renameFile($oldname,$newname,$path){
        $result = array();
        //验证文件名是否合法
        if($this->checkFilename($newname)){
            //检测当前目录下是否存在同名文件
            $path=dirname($path);
            if(!file_exists($path."/".$newname)){
                //进行重命名
                if(rename($path.'/'.$oldname,$path."/".$newname)){
                    $result['success'] = true;
                }else{
                    $result['success'] = false;
                    $result['msg'] = "重命名失败";
                }
            }else{
                $result['success'] = false;
                $result['msg'] = "存在同名文件";
            }
        }else{
            $result['success'] = false;
            $result['msg'] = "非法文件名";
        }
        return $result;
    }

    /**
     * 删除文件
     * @param string $filename
     * @return string
     */
    public function delFile($filename){
        if (file_exists($filename)) {
            if(unlink($filename)){
                $mes = true;
            }else{
                $mes = false;
            }
        }else{
            $mes = true;
        }
        return $mes;
    }

    /**
     * 下载文件操作
     * @param string $filename
     */
    public function downFile($filename){
        header("content-disposition:attachment;filename=".basename($filename));
        header("content-length:".filesize($filename));
        readfile($filename);
    }

    /**
     * 复制文件
     * @param string $filename
     * @param string $dstname
     * @return string
     */
    public function copyFile($filename,$dstname){
        if(file_exists($dstname)){
            if(!file_exists($dstname."/".basename($filename))){
                if(copy($filename,$dstname."/".basename($filename))){
                    $mes="文件复制成功";
                }else{
                    $mes="文件复制失败";
                }
            }else{
                $mes="存在同名文件";
            }
        }else{
            $mes="目标目录不存在";
        }
        return $mes;
    }

    /**
     * 剪切文件
     * @param string $filename
     * @param string $dstname
     * @return string
     */
    public function cutFile($filename,$dstname){
        if(file_exists($dstname)){
            if(!file_exists($dstname."/".basename($filename))){
                if(rename($filename,$dstname."/".basename($filename))){
                    $mes="文件剪切成功";
                }else{
                    $mes="文件剪切失败";
                }
            }else{
                $mes="存在同名文件";
            }
        }else{
            $mes="目标目录不存在";
        }
        return $mes;
    }

    /**
     * 上传文件
     * @param array $fileInfo
     * @param string $path
     * @param array $allowExt
     * @param int $maxSize
     * @return string
     */
    public function uploadFile($fileInfo,$path,$allowExt=array("gif","jpeg","jpg","png","txt"),$maxSize=10485760){
        //判断错误号
        if($fileInfo['error']==UPLOAD_ERR_OK){
            //文件是否是通过HTTP POST方式上传上来的
            if(is_uploaded_file($fileInfo['tmp_name'])){
                //上传文件的文件名，只允许上传jpeg|jpg、png、gif、txt的文件
                //$allowExt=array("gif","jpeg","jpg","png","txt");
                $ext=getExt($fileInfo['name']);
                $uniqid=getUniqidName();
                $destination=$path."/".pathinfo($fileInfo['name'],PATHINFO_FILENAME)."_".$uniqid.".".$ext;
                if(in_array($ext,$allowExt)){
                    if($fileInfo['size']<=$maxSize){
                        if(move_uploaded_file($fileInfo['tmp_name'], $destination)){
                            $mes="文件上传成功";
                        }else{
                            $mes="文件移动失败";
                        }
                    }else{
                        $mes="文件过大";
                    }
                }else{
                    $mes="非法文件类型";
                }
            }else{
                $mes="文件不是通过HTTP POST方式上传上来的";
            }
        }else{
            switch($fileInfo['error']){
                case 1:
                    $mes="超过了配置文件的大小";
                    break;
                case 2:
                    $mes="超过了表单允许接收数据的大小";
                    break;
                case 3:
                    $mes="文件部分被上传";
                    break;
                case 4:
                    $mes="没有文件被上传";
                    break;
            }
        }
        
        return $mes;
        
    }

    /**
     *检测文件名是否合法
     * @param string $filename
     * @return boolean
     */
    public function checkFilename($filename){
        $pattern = "/[\/,\*,<>,\?\|]/";
        if (preg_match ( $pattern,  $filename )) {
            return false;
        }else{
            return true;
        }
    }


    /*-----------------------------文件夹操作----------------------------------*/
    /**
     * 遍历目录函数，只读取目录中的最外层的内容
     * @param string $path
     * @return array
     */
    public function readDirectory($path) {
        $handle = opendir ( $path );
        while ( ($item = readdir ( $handle )) !== false ) {
            //.和..这2个特殊目录
            if ($item != "." && $item != "..") {
                if (is_file ( $path . "/" . $item )) {
                    $arr ['file'] [] = $item;
                }
                if (is_dir ( $path . "/" . $item )) {
                    $arr ['dir'] [] = $item;
                }
            }
        }
        closedir ( $handle );
        if (!isset($arr)) {
            $arr = array();
        }
        return $arr;
    }

    /**
     * 得到文件夹大小
     * @param string $path
     * @return int 
     */
    public function dirSize($path){
        $sum=0;
        global $sum;
        $handle=opendir($path);
        while(($item=readdir($handle))!==false){
            if($item!="."&&$item!=".."){
                if(is_file($path."/".$item)){
                    $sum+=filesize($path."/".$item);
                }
                if(is_dir($path."/".$item)){
                    $func=__FUNCTION__;
                    $this->$func($path."/".$item);
                }
            }
            
        }
        closedir($handle);
        $size = $sum;
        unset($sum);
        return $size;
    }

    /**
     * 新建文件夹
     * @param string $path
     * @return int 
     */
    public function createFolder($path,$dirname){
        $result = array();
        //检测文件夹名称的合法性
        if($this->checkFilename($dirname)){
            //当前目录下是否存在同名文件夹名称
            if(!file_exists($path.'/'.$dirname)){
                if(mkdir($path.'/'.$dirname,0777,true)){
                    // $mes="文件夹创建成功";
                    $result['success'] = true;
                }else{
                    // $mes="文件夹创建失败";
                    $result['success'] = false;
                    $result['msg'] = "新建文件夹失败，权限不足";
                }
            }else{
                // $mes="存在相同文件夹名称";
                $result['success'] = false;
                $result['msg'] = "存在同名文件夹";
            }
        }else{
            $result['success'] = false;
            $result['msg'] = "非法文件夹名称";
        }
        return $result;
    }

    /**
     * 重命名文件夹
     * @param string $oldname
     * @param string $newname
     * @return string
     */
    public function renameFolder($oldname,$newname){
        //检测文件夹名称的合法性
        if($this->checkFilename(basename($newname))){
            //检测当前目录下是否存在同名文件夹名称
            if(!file_exists($newname)){
                if(rename($oldname,$newname)){
                    $mes="重命名成功";
                }else{
                    $mes="重命名失败";
                }
            }else{
                $mes="存在同名文件夹";
            }
        }else{
            $mes="非法文件夹名称";
        }
        return $mes;
    }
    /**
     * 复制文件夹
     * @param string $src
     * @param string $dst
     * @return string
     */
    public function copyFolder($src,$dst){
        if(!file_exists($dst)){
            mkdir($dst,0777,true);
        }
        $handle=opendir($src);
        while(($item=readdir($handle))!==false){
            if($item!="."&&$item!=".."){
                if(is_file($src."/".$item)){
                    copy($src."/".$item,$dst."/".$item);
                }
                if(is_dir($src."/".$item)){
                    $func=__FUNCTION__;
                    $this->$func($src."/".$item,$dst."/".$item);
                }
            }
        }
        closedir($handle);
        return "复制成功";
    }

    /**
     * 剪切文件夹
     * @param string $src
     * @param string $dst
     * @return string
     */
    public function cutFolder($src,$dst){
        //echo $src,"--",$dst;
        if(file_exists($dst)){
            if(is_dir($dst)){
                if(!file_exists($dst."/".basename($src))){
                    if(rename($src,$dst."/".basename($src))){
                        $mes="剪切成功";
                    }else{
                        $mes="剪切失败";
                    }
                }else{
                    $mes="存在同名文件夹";
                }
            }else{
                $mes="不是一个文件夹";
            }
        }else{
            $mes="目标文件夹不存在";
        }
        return $mes;
    }

    /**
     * 删除文件夹
     * @param string $path
     * @return string
     */
    public function delFolder($path){
        $handle=opendir($path);
        while(($item=readdir($handle))!==false){
            if($item!="."&&$item!=".."){
                if(is_file($path."/".$item)){
                    unlink($path."/".$item);
                }
                if(is_dir($path."/".$item)){
                    $func=__FUNCTION__;
                    $this->$func($path."/".$item);
                }
            }
        }
        closedir($handle);
        rmdir($path);
        return array('success'=>true);
    }

}

